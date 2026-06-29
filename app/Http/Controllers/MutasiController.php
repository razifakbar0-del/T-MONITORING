<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Cookie\CookieJar;
use Carbon\Carbon;
use App\Models\BankMutation;

class MutasiController extends Controller
{
    private string $baseUrl = 'https://mpn-gateway.samantara.com/mpnbjt';

    public function index()
    {
        return view('mutasi.index');
    }

    /**
     * Fetch dari API → simpan ke DB → return data dari DB
     */
    public function fetch(Request $request)
    {
        $startDate = $request->get('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->get('end',   Carbon::now()->format('Y-m-d'));

        try {
            $cookieJar = new CookieJar();

            // Step 1: Login
            $loginResponse = Http::timeout(30)
                ->withOptions(['cookies' => $cookieJar])
                ->get("{$this->baseUrl}/login");

            if (!$loginResponse->successful()) {
                return response()->json(['success' => false, 'error' => 'Login gagal: HTTP ' . $loginResponse->status()]);
            }

            $loginData = $loginResponse->json();

            if (!($loginData['status'] ?? false)) {
                return response()->json(['success' => false, 'error' => 'Login gagal: ' . ($loginData['message'] ?? 'Unknown')]);
            }

            $accounts    = $loginData['accounts'] ?? [];
            $accountInfo = null;
            if (!empty($accounts)) {
                $accountInfo = [
                    'accountNo' => $accounts[0]['norek'] ?? '-',
                    'nama'      => $accounts[0]['nama']  ?? '-',
                    'cabang'    => $loginData['user_name'] ?? '-',
                    'saldo'     => str_replace(['.', ','], '', $accounts[0]['saldo'] ?? '0'),
                ];
            }

            // Step 2: Ambil mutasi dari API
            $mutasiResponse = Http::timeout(30)
                ->withOptions(['cookies' => $cookieJar])
                ->get("{$this->baseUrl}/api/mutasi", [
                    'start' => $startDate,
                    'end'   => $endDate,
                ]);

            if (!$mutasiResponse->successful()) {
                return response()->json(['success' => false, 'error' => 'Gagal ambil mutasi: HTTP ' . $mutasiResponse->status()]);
            }

            $json = $mutasiResponse->json();

            if (($json['rc'] ?? '') !== '00') {
                return response()->json(['success' => false, 'error' => $json['msg'] ?? 'API error']);
            }

            $rawData = $json['data'] ?? [];

            if (empty($rawData)) {
                return response()->json(['success' => false, 'error' => 'Tidak ada data untuk periode ini']);
            }

            // Step 3: Simpan ke DB (upsert berdasarkan no_reference, abaikan duplikat)
            $saved   = 0;
            $skipped = 0;

            foreach ($rawData as $item) {
                $dk     = str_replace(['.', ' ', ','], '', $item['debet_kredit'] ?? '0');
                $saldo  = str_replace(['.', ' ', ','], '', $item['saldo']        ?? '0');
                $amount = (float) $dk;
                $noRef  = $item['no_reference'] ?? null;

                if (!$noRef) {
                    $skipped++;
                    continue;
                }

                $exists = BankMutation::where('no_reference', $noRef)->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                BankMutation::create([
                    'no_reference' => $noRef,
                    'tanggal'      => $item['tanggal'] ?? now()->toDateString(),
                    'keterangan'   => $item['keterangan']   ?? null,
                    'debet_kredit' => $amount,
                    'saldo'        => (float) $saldo,
                    'type'         => $amount >= 0 ? 'kredit' : 'debet',
                    'no_urut'      => $item['no'] ?? null,
                    'start_date'   => $startDate,
                    'end_date'     => $endDate,
                ]);

                $saved++;
            }

            // Step 4: Ambil data dari DB untuk periode ini
            $mutasiData = BankMutation::periode($startDate, $endDate)
                ->orderBy('tanggal')
                ->orderBy('id')
                ->get()
                ->map(fn($m) => [
                    'no'           => $m->no_urut ?? $m->id,
                    'tanggal'      => $m->tanggal->format('d/m/Y'),
                    'no_reference' => $m->no_reference,
                    'keterangan'   => $m->keterangan,
                    'debet_kredit' => $m->debet_kredit,
                    'saldo'        => $m->saldo,
                    'type'         => $m->type,
                ]);

            $totalKredit = $mutasiData->where('type', 'kredit')->sum('debet_kredit');
            $totalDebet  = $mutasiData->where('type', 'debet')->sum('debet_kredit');

            if ($accountInfo) {
                $lastSaldo = $mutasiData->last()['saldo'] ?? 0;
                $accountInfo['saldo'] = $lastSaldo;
            }

            return response()->json([
                'success'     => true,
                'accountInfo' => $accountInfo,
                'mutasiData'  => $mutasiData->values(),
                'summary'     => [
                    'total_transaksi' => $mutasiData->count(),
                    'total_kredit'    => $totalKredit,
                    'total_debet'     => abs($totalDebet),
                    'net'             => $totalKredit + $totalDebet,
                ],
                'sync_info' => [
                    'saved'   => $saved,
                    'skipped' => $skipped,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Ambil data mutasi dari DB saja (tanpa hit API)
     * Untuk filter ulang tanpa loading lama
     */
    public function fromDb(Request $request)
    {
        $startDate = $request->get('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->get('end',   Carbon::now()->format('Y-m-d'));

        $mutasiData = BankMutation::periode($startDate, $endDate)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get()
            ->map(fn($m) => [
                'no'           => $m->no_urut ?? $m->id,
                'tanggal'      => $m->tanggal->format('d/m/Y'),
                'no_reference' => $m->no_reference,
                'keterangan'   => $m->keterangan,
                'debet_kredit' => $m->debet_kredit,
                'saldo'        => $m->saldo,
                'type'         => $m->type,
            ]);

        if ($mutasiData->isEmpty()) {
            return response()->json([
                'success' => false,
                'error'   => 'Belum ada data di database untuk periode ini. Silakan klik Sinkron API.',
            ]);
        }

        $totalKredit = $mutasiData->where('type', 'kredit')->sum('debet_kredit');
        $totalDebet  = $mutasiData->where('type', 'debet')->sum('debet_kredit');

        return response()->json([
            'success'    => true,
            'mutasiData' => $mutasiData->values(),
            'summary'    => [
                'total_transaksi' => $mutasiData->count(),
                'total_kredit'    => $totalKredit,
                'total_debet'     => abs($totalDebet),
                'net'             => $totalKredit + $totalDebet,
            ],
        ]);
    }
}

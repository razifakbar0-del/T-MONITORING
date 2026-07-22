<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\BankMutation;

class MutasiController extends Controller
{
    public function index()
    {
        return view('mutasi.index');
    }

    /**
     * Fetch langsung dari DB eksternal 103.127.133.46 → simpan ke DB lokal → return data
     */
    public function fetch(Request $request)
    {
        $startDate = $request->get('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->get('end',   Carbon::now()->format('Y-m-d'));

        try {
            // Ambil data langsung dari DB eksternal (koneksi 'mutasi' di config/database.php)
            $rawData = DB::connection('mutasi')
                ->table('mutasi_transactions')
                ->whereBetween('trxdate', [$startDate, $endDate])
                ->orderBy('trxdate')
                ->orderBy('trxtime')
                ->get();

            if ($rawData->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Tidak ada data untuk periode ' . $startDate . ' s/d ' . $endDate,
                ]);
            }

            // Ambil info akun dari row pertama
            $firstRow    = $rawData->first();
            $accountInfo = [
                'accountNo' => $firstRow->account   ?? '-',
                'nama'      => $firstRow->bank_name ?? '-',
                'cabang'    => $firstRow->bank_type ?? '-',
                'saldo'     => $rawData->last()->balance ?? 0,
            ];

            // Simpan ke DB lokal (skip duplikat berdasarkan no_reference = refid)
            $saved   = 0;
            $skipped = 0;

            foreach ($rawData as $item) {
                $noRef = $item->refid ?? null;
                if (!$noRef) { $skipped++; continue; }

                $exists = BankMutation::where('no_reference', $noRef)->exists();
                if ($exists) { $skipped++; continue; }

                $amount = (float) ($item->credit > 0 ? $item->credit : -$item->debit);

                BankMutation::create([
                    'no_reference' => $noRef,
                    'tanggal'      => $item->trxdate,
                    'keterangan'   => $item->remark ?? null,
                    'debet_kredit' => $amount,
                    'saldo'        => (float) ($item->balance ?? 0),
                    'type'         => $item->credit > 0 ? 'kredit' : 'debet',
                    'no_urut'      => null,
                    'start_date'   => $startDate,
                    'end_date'     => $endDate,
                ]);

                $saved++;
            }

            // Format data untuk response
            $mutasiData = $rawData->map(function ($item) {
                $amount = (float) ($item->credit > 0 ? $item->credit : -$item->debit);
                return [
                    'no'           => $item->id,
                    'tanggal'      => Carbon::parse($item->trxdate)->format('d/m/Y') . ' ' . ($item->trxtime ?? ''),
                    'no_reference' => $item->refid ?? '-',
                    'keterangan'   => $item->remark ?? '-',
                    'debet_kredit' => $amount,
                    'saldo'        => (float) ($item->balance ?? 0),
                    'type'         => $item->credit > 0 ? 'kredit' : 'debet',
                ];
            });

            $totalKredit = $rawData->sum('credit');
            $totalDebet  = $rawData->sum('debit');

            return response()->json([
                'success'     => true,
                'accountInfo' => $accountInfo,
                'mutasiData'  => $mutasiData->values(),
                'summary'     => [
                    'total_transaksi' => $rawData->count(),
                    'total_kredit'    => (float) $totalKredit,
                    'total_debet'     => (float) $totalDebet,
                    'net'             => (float) ($totalKredit - $totalDebet),
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
     * Ambil data dari DB lokal saja (tanpa hit DB eksternal)
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
                'error'   => 'Belum ada data di database lokal untuk periode ini. Silakan klik Sinkron.',
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
                'net'             => $totalKredit - abs($totalDebet),
            ],
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class MutasiController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->get('end',   Carbon::now()->format('Y-m-d'));

        $mutasiData  = null;
        $accountInfo = null;
        $error       = null;
        $summary     = null;

        try {
            $url = "https://mpn-gateway.samantara.com/mpnbjt/api/mutasi?start={$startDate}&end={$endDate}";
            $response = Http::timeout(30)->get($url);

            if ($response->successful()) {
                $json = $response->json();

                if (($json['rc'] ?? '') === '00') {
                    $rawData     = $json['data']    ?? [];
                    $accountInfo = $json['account'] ?? null;

                    // Parsing & normalisasi tiap baris mutasi
                    $mutasiData = collect($rawData)->map(function ($item) {
                        $dk    = str_replace(['.', ' '], '', $item['debet_kredit'] ?? '0');
                        $saldo = str_replace(['.', ' '], '', $item['saldo']        ?? '0');
                        $amount = (float) $dk;

                        return [
                            'no'           => $item['no']           ?? '-',
                            'tanggal'      => $item['tanggal']       ?? '-',
                            'no_reference' => $item['no_reference']  ?? '-',
                            'keterangan'   => $item['keterangan']    ?? '-',
                            'debet_kredit' => $amount,
                            'saldo'        => (float) $saldo,
                            'type'         => $amount >= 0 ? 'kredit' : 'debet',
                        ];
                    });

                    // Summary
                    $totalKredit = $mutasiData->where('type', 'kredit')->sum('debet_kredit');
                    $totalDebet  = $mutasiData->where('type', 'debet')->sum('debet_kredit');
                    $summary = [
                        'total_transaksi' => $mutasiData->count(),
                        'total_kredit'    => $totalKredit,
                        'total_debet'     => abs($totalDebet),
                        'net'             => $totalKredit + $totalDebet,
                    ];
                } else {
                    $error = 'API merespon: ' . ($json['msg'] ?? 'Gagal');
                }
            } else {
                $error = 'HTTP Error: ' . $response->status();
            }
        } catch (\Exception $e) {
            $error = 'Koneksi gagal: ' . $e->getMessage();
        }

        return view('mutasi.index', compact(
            'mutasiData', 'accountInfo', 'error', 'summary', 'startDate', 'endDate'
        ));
    }
}
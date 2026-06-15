<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use App\Models\ApiSource;
use Carbon\Carbon;

class TransactionService
{
    public function fetchAndSync($startDate, $endDate)
    {
        // 1. Ambil semua sumber API yang aktif
        $sources = ApiSource::where('is_active', true)->get();

        // Jika belum ada di DB, gunakan default
        if ($sources->isEmpty()) {
            $sources = [
                (object)[
                    'id' => null,
                    'url' => "https://mpn-gateway.samantara.com/mpnbjt/api/mutasi?start={$startDate}&end={$endDate}"
                ]
            ];
        }

        foreach ($sources as $source) {
            // Mengganti placeholder tanggal jika url disimpan dinamis
            $url = str_replace(['{YYYY-MM-DD}', '{start}', '{end}'], [$startDate, $startDate, $endDate], $source->url);
            
            try {
                $response = Http::timeout(30)->get($url);

                if ($response->successful()) {
                    $transactions = $response->json()['data'] ?? []; // Sesuaikan dengan struktur JSON asli

                    foreach ($transactions as $trx) {
                        // Simpan atau update ke database (Upsert) untuk menghindari duplikasi
                        Transaction::updateOrCreate(
                            ['trx_id' => $trx['id']], // Key unik
                            [
                                'api_source_id' => $source->id,
                                'trx_date' => Carbon::parse($trx['date']),
                                'amount' => $trx['amount'],
                                'status' => $trx['status'],
                                'customer_name' => $trx['customer_name'] ?? null,
                            ]
                        );
                    }
                }
            } catch (\Exception $e) {
                // Log error jika salah satu API down
                logger("Gagal hit API: " . $e->getMessage());
            }
        }
    }
}
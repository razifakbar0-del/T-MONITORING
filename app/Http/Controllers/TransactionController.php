<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function syncApi()
    {
        // Menentukan rentang tanggal sinkronisasi
        $startDate = '2026-06-01'; 
        $endDate = Carbon::today()->format('Y-m-d');
        $endpointUrl = "https://mpn-gateway.samantara.com/mpnbjt/api/mutasi?start={$startDate}&end={$endDate}";

        try {
            // Ambil data dari API dengan timeout 15 detik agar tidak membuat aplikasi freeze
            $response = Http::timeout(15)->get($endpointUrl);

            if ($response->successful()) {
                $rootData = $response->json();
                
                // Response API Samantara membungkus data mutasi di dalam key 'data'
                $apiData = $rootData['data'] ?? [];
                $insertedCount = 0;

                // Mapping nama bulan Indonesia ke angka untuk keperluan standar format SQLite/MySQL
                $months = [
                    'Januari'   => '01', 'Februari'  => '02', 'Maret'     => '03', 
                    'April'     => '04', 'Mei'       => '05', 'Juni'      => '06', 
                    'Juli'      => '07', 'Agustus'   => '08', 'September' => '09', 
                    'Oktober'   => '10', 'November'  => '11', 'Desember'  => '12'
                ];

                foreach ($apiData as $item) {
                    // Ambil nomor referensi unik sebagai id transaksi API
                    $apiTrxId = $item['no_reference'] ?? null;
                    
                    if ($apiTrxId) {
                        // BUG FIX 1: Ubah 'debet_credit' (huruf c) menjadi 'debet_kredit' (huruf k) sesuai JSON asli API
                        $rawAmount = $item['debet_kredit'] ?? '0';
                        
                        // Membersihkan tanda titik (.) dan minus (-) lalu ubah ke angka mutlak (absolut)
                        $cleanAmount = abs((float) str_replace(['.', '-'], '', $rawAmount));

                        // BUG FIX 2: Standardisasi penanganan konversi tanggal lokal "02 Juni 2026"
                        $rawDate = $item['tanggal'] ?? ''; 
                        $finalDate = Carbon::now()->format('Y-m-d H:i:s'); // fallback jika parse gagal
                        
                        if (!empty($rawDate)) {
                            $dateParts = explode(' ', trim($rawDate)); // pecah jadi array [02, Juni, 2026]
                            if (count($dateParts) === 3) {
                                $day = str_pad($dateParts[0], 2, '0', STR_PAD_LEFT);
                                $monthName = $dateParts[1];
                                $year = $dateParts[2];
                                
                                // Jika bulan tidak terdaftar, default ke bulan berjalan
                                $monthNumber = $months[$monthName] ?? Carbon::now()->format('m');
                                $finalDate = "{$year}-{$monthNumber}-{$day} 12:00:00";
                            }
                        }

                        // BUG FIX 3: Mencegah duplikasi data sebelum insert ke database local
                        $exists = Transaction::where('trx_id', $apiTrxId)->exists();
                        
                        if (!$exists) {
                            Transaction::create([
                                'api_source_id' => 1, // Pastikan id 1 terdaftar di tabel api_sources
                                'trx_id'        => $apiTrxId,
                                'trx_date'      => $finalDate,
                                'amount'        => $cleanAmount,
                                'status'        => 'sukses',
                                'customer_name' => $item['keterangan'] ?? 'Pelanggan Umum',
                            ]);
                            $insertedCount++;
                        }
                    }
                }
                
                return redirect()->route('dashboard')->with('success', "Sinkronisasi Berhasil! Berhasil menambahkan {$insertedCount} data mutasi baru.");
            }
            
            return redirect()->route('dashboard')->with('error', "Gagal Sinkronisasi! Gateway merespon status: " . $response->status());

        } catch (\Exception $e) {
            // Mencatat error ke file log local (storage/logs/laravel.log) untuk memudahkan debugging mandiri
            Log::error('API Sync Failure: ' . $e->getMessage());
            
            return redirect()->route('dashboard')->with('error', "Gagal terhubung ke server API: " . $e->getMessage());
        }
    }

    public function exportCsv()
    {
        // Logika export aman
    }
}
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
        $startDate = '2026-06-01'; 
        $endDate = Carbon::today()->format('Y-m-d');
        $endpointUrl = "https://mpn-gateway.samantara.com/mpnbjt/api/mutasi?start={$startDate}&end={$endDate}";

        try {
            $response = Http::timeout(15)->get($endpointUrl);

            if ($response->successful()) {
                $rootData = $response->json();
                $apiData = $rootData['data'] ?? [];
                $insertedCount = 0;

                $months = [
                    'Januari'   => '01', 'Februari'  => '02', 'Maret'     => '03', 
                    'April'     => '04', 'Mei'       => '05', 'Juni'      => '06', 
                    'Juli'      => '07', 'Agustus'   => '08', 'September' => '09', 
                    'Oktober'   => '10', 'November'  => '11', 'Desember'  => '12'
                ];

                foreach ($apiData as $item) {
                    $apiTrxId = $item['no_reference'] ?? null;
                    
                    if ($apiTrxId) {
                        $rawAmount = $item['debet_kredit'] ?? '0';
                        $cleanAmount = abs((float) str_replace(['.', '-'], '', $rawAmount));

                        $rawDate = $item['tanggal'] ?? ''; 
                        $finalDate = Carbon::now()->format('Y-m-d H:i:s');
                        
                        if (!empty($rawDate)) {
                            $dateParts = explode(' ', trim($rawDate));
                            if (count($dateParts) === 3) {
                                $day = str_pad($dateParts[0], 2, '0', STR_PAD_LEFT);
                                $monthName = $dateParts[1];
                                $year = $dateParts[2];
                                $monthNumber = $months[$monthName] ?? Carbon::now()->format('m');
                                $finalDate = "{$year}-{$monthNumber}-{$day} 12:00:00";
                            }
                        }

                        $exists = Transaction::where('trx_id', $apiTrxId)->exists();
                        
                        if (!$exists) {
                            Transaction::create([
                                'api_source_id' => 1,
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
            Log::error('API Sync Failure: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', "Gagal terhubung ke server API: " . $e->getMessage());
        }
    }

    public function uploadCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');

        // Skip baris header
        fgetcsv($handle, 0, ';');

        $inserted = 0;
        $skipped  = 0;
        $errors   = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            // Pastikan jumlah kolom cukup
            if (count($row) < 17) {
                $errors++;
                continue;
            }

            $trxId = trim($row[0]);
            if (empty($trxId)) { $errors++; continue; }

            // Cek duplikat berdasarkan trx_id
            if (Transaction::where('trx_id', $trxId)->exists()) {
                $skipped++;
                continue;
            }

            try {
                // Parse tanggal format "19/06/2026 00:36:52"
                $trxDate = Carbon::createFromFormat('d/m/Y H:i:s', trim($row[5]));

                $rc     = trim($row[10]);
                $status = ($rc === '00') ? 'sukses' : 'gagal';

                Transaction::create([
                    'trx_id'        => $trxId,
                    'api_source_id' => null,
                    'reseller_name' => trim($row[1]),
                    'supplier'      => trim($row[4]),
                    'trx_date'      => $trxDate,
                    'msisdn'        => trim($row[6]),
                    'amount'        => abs((float) trim($row[9])),
                    'status'        => $status,
                    'product_code'  => trim($row[14]),
                    'customer_name' => trim($row[13]),
                    'sn'            => trim($row[11]),
                    'request_id'    => trim($row[12]),
                    'debit'         => (int) trim($row[15]),
                    'credit'        => (int) trim($row[16]),
                    'balance'       => isset($row[17]) ? (int) trim($row[17]) : 0,
                    'profit'        => isset($row[8])  ? (int) trim($row[8])  : 0,
                ]);

                $inserted++;
            } catch (\Exception $e) {
                Log::error("CSV import error row {$trxId}: " . $e->getMessage());
                $errors++;
            }
        }

        fclose($handle);

        $message = "Import selesai: {$inserted} data berhasil diimport, {$skipped} data sudah ada (skip)";
        if ($errors > 0) $message .= ", {$errors} baris error";

        return back()->with('success', $message);
    }

    public function exportCsv()
    {
        $transactions = Transaction::orderBy('trx_date', 'desc')->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            // Header kolom
            fputcsv($handle, [
                'ID', 'TRX ID', 'Reseller', 'Supplier', 'Tanggal', 
                'MSISDN', 'Produk', 'Amount', 'Profit', 
                'Debit', 'Credit', 'Balance', 'Status'
            ]);
            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->id,
                    $t->trx_id,
                    $t->reseller_name,
                    $t->supplier,
                    $t->trx_date,
                    $t->msisdn,
                    $t->customer_name,
                    $t->amount,
                    $t->profit,
                    $t->debit,
                    $t->credit,
                    $t->balance,
                    $t->status,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
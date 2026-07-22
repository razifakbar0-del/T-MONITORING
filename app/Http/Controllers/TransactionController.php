<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query();

        if ($request->filled('start'))        $query->whereDate('trx_date', '>=', $request->start);
        if ($request->filled('end'))          $query->whereDate('trx_date', '<=', $request->end);
        if ($request->filled('msisdn'))       $query->where('msisdn', 'like', '%' . $request->msisdn . '%');
        if ($request->filled('reseller'))     $query->where(function ($q) use ($request) {
            $q->where('reseller_name', 'like', '%' . $request->reseller . '%')
              ->orWhere('request_id',  'like', '%' . $request->reseller . '%');
        });
        if ($request->filled('product_code')) $query->where('product_code', 'like', '%' . $request->product_code . '%');
        if ($request->filled('status')) {
            $statusMap = ['sukses' => 'success', 'gagal' => 'failed'];
            $query->where('status', $statusMap[$request->status] ?? $request->status);
        }

        $perPage      = in_array((int) $request->get('per_page'), [10, 15, 25, 50]) ? (int) $request->get('per_page') : 15;
        $transactions = $query->orderBy('trx_date', 'desc')->paginate($perPage)->withQueryString();

        $summary = [
            'total'        => $transactions->total(),
            'total_sukses' => (clone $query)->where('status', 'success')->count(),
            'total_gagal'  => (clone $query)->where('status', 'failed')->count(),
            'total_amount' => (clone $query)->sum('credit'),
        ];

        if ($request->ajax()) {
            return response()->json([
                'data' => $transactions->getCollection()->map(fn($t) => [
                    'id'            => $t->id,
                    'trx_id'        => $t->trx_id,
                    'msisdn'        => $t->msisdn,
                    'trx_date'      => Carbon::parse($t->trx_date)->format('d M Y'),
                    'trx_time'      => Carbon::parse($t->trx_date)->format('H:i:s'),
                    'product_code'  => $t->product_code,
                    'reseller_name' => $t->reseller_name,
                    'request_id'    => $t->request_id,
                    'supplier'      => $t->supplier,
                    'customer_name' => $t->customer_name,
                    'debit'         => $t->debit  ?? 0,
                    'credit'        => $t->credit ?? 0,
                    'amount'        => $t->amount ?? 0,
                    'profit'        => $t->profit ?? 0,
                    'status'        => $t->status,
                ]),
                'total'        => $transactions->total(),
                'from'         => $transactions->firstItem(),
                'to'           => $transactions->lastItem(),
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'summary'      => $summary,
            ]);
        }

        return view('transactions.index', compact('transactions', 'summary'));
    }

    public function show($id)
    {
        $transaction = Transaction::findOrFail($id);
        return view('transactions.show', compact('transaction'));
    }

    public function syncApi()
    {
        $startDate   = '2026-06-01';
        $endDate     = Carbon::today()->format('Y-m-d');
        $endpointUrl = "https://mpn-gateway.samantara.com/mpnbjt/api/mutasi?start={$startDate}&end={$endDate}";
        try {
            $response = Http::timeout(15)->get($endpointUrl);
            if ($response->successful()) {
                $apiData = $response->json()['data'] ?? [];
                $insertedCount = 0;
                $months = ['Januari'=>'01','Februari'=>'02','Maret'=>'03','April'=>'04','Mei'=>'05','Juni'=>'06','Juli'=>'07','Agustus'=>'08','September'=>'09','Oktober'=>'10','November'=>'11','Desember'=>'12'];
                foreach ($apiData as $item) {
                    $apiTrxId = $item['no_reference'] ?? null;
                    if (!$apiTrxId) continue;
                    $cleanAmount = abs((float) str_replace(['.', '-'], '', $item['debet_kredit'] ?? '0'));
                    $rawDate = $item['tanggal'] ?? '';
                    $finalDate = Carbon::now()->format('Y-m-d H:i:s');
                    if (!empty($rawDate)) {
                        $dateParts = explode(' ', trim($rawDate));
                        if (count($dateParts) === 3) {
                            $day = str_pad($dateParts[0], 2, '0', STR_PAD_LEFT);
                            $monthNumber = $months[$dateParts[1]] ?? Carbon::now()->format('m');
                            $finalDate = "{$dateParts[2]}-{$monthNumber}-{$day} 12:00:00";
                        }
                    }
                    if (!Transaction::where('trx_id', $apiTrxId)->exists()) {
                        Transaction::create(['api_source_id'=>1,'trx_id'=>$apiTrxId,'trx_date'=>$finalDate,'amount'=>$cleanAmount,'status'=>'success','customer_name'=>$item['keterangan']??'Pelanggan Umum']);
                        $insertedCount++;
                    }
                }
                AuditLog::create(['user_id'=>auth()->id(),'activity'=>'SYNC','model_type'=>'Transaction','details'=>"Sync berhasil, {$insertedCount} data baru."]);
                return redirect()->route('dashboard')->with('success', "Sinkronisasi Berhasil! {$insertedCount} data baru.");
            }
            return redirect()->route('dashboard')->with('error', 'Gagal Sinkronisasi! Status: '.$response->status());
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Gagal: '.$e->getMessage());
        }
    }

    public function uploadCsv(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:10240']);
        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        fgetcsv($handle, 0, ';');
        $inserted = $skipped = $errors = 0;
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) < 17) { $errors++; continue; }
            $trxId = trim($row[0]);
            if (empty($trxId)) { $errors++; continue; }
            if (Transaction::where('trx_id', $trxId)->exists()) { $skipped++; continue; }
            try {
                Transaction::create([
                    'trx_id'        => $trxId,
                    'api_source_id' => null,
                    'reseller_name' => trim($row[1]),
                    'supplier'      => trim($row[4]),
                    'trx_date'      => Carbon::createFromFormat('d/m/Y H:i:s', trim($row[5])),
                    'msisdn'        => trim($row[6]),
                    'amount'        => abs((float) trim($row[9])),
                    'status'        => trim($row[10]) === '00' ? 'success' : 'failed',
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
                Log::error("CSV row {$trxId}: ".$e->getMessage());
                $errors++;
            }
        }
        fclose($handle);
        AuditLog::create(['user_id'=>auth()->id(),'activity'=>'IMPORT','model_type'=>'Transaction','details'=>"CSV: {$inserted} ok, {$skipped} skip, {$errors} error."]);
        $msg = "Import: {$inserted} berhasil, {$skipped} duplikat";
        if ($errors) $msg .= ", {$errors} error";
        return back()->with('success', $msg);
    }

    public function exportCsv(Request $request)
    {
        $query = Transaction::query();
        if ($request->filled('start'))        $query->whereDate('trx_date', '>=', $request->start);
        if ($request->filled('end'))          $query->whereDate('trx_date', '<=', $request->end);
        if ($request->filled('msisdn'))       $query->where('msisdn', 'like', '%'.$request->msisdn.'%');
        if ($request->filled('reseller'))     $query->where('reseller_name', 'like', '%'.$request->reseller.'%');
        if ($request->filled('product_code')) $query->where('product_code', 'like', '%'.$request->product_code.'%');
        if ($request->filled('status')) {
            $statusMap = ['sukses'=>'success','gagal'=>'failed'];
            $query->where('status', $statusMap[$request->status] ?? $request->status);
        }
        $transactions = $query->orderBy('trx_date', 'desc')->get();
        $headers = ['Content-Type'=>'text/csv','Content-Disposition'=>'attachment; filename="transactions_'.date('Ymd_His').'.csv"'];
        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID','TRX ID','MSISDN','Tanggal','Jam','Code','Reseller','Request ID','Supplier','Keterangan','Debit','Kredit','Total','Profit','Status']);
            foreach ($transactions as $t) {
                $dt = Carbon::parse($t->trx_date);
                fputcsv($handle, [$t->id,$t->trx_id,$t->msisdn,$dt->format('d/m/Y'),$dt->format('H:i:s'),$t->product_code,$t->reseller_name,$t->request_id,$t->supplier,$t->customer_name,$t->debit,$t->credit,$t->amount,$t->profit,$t->status==='success'?'sukses':'gagal']);
            }
            fclose($handle);
        };
        return response()->stream($callback, 200, $headers);
    }
}
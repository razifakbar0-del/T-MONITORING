<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Supplier;
use App\Models\SupplierMutation;
use App\Models\RekonResult;
use App\Models\Transaction;
use App\Models\AuditLog;

class RekonController extends Controller
{
    // -------------------------------------------------------
    // Halaman daftar supplier
    // -------------------------------------------------------
    public function index()
    {
        $suppliers = Supplier::withCount('mutations')->latest()->get();
        return view('rekon.index', compact('suppliers'));
    }

    // -------------------------------------------------------
    // Form tambah supplier
    // -------------------------------------------------------
    public function create()
    {
        return view('rekon.create');
    }

    // -------------------------------------------------------
    // Simpan supplier baru
    // -------------------------------------------------------
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'api_url'       => 'required|url',
            'method'        => 'required|in:GET,POST,PUT,DELETE,PATCH',
            'param_start'   => 'required|string',
            'param_end'     => 'required|string',
            'response_path' => 'required|string',
        ]);

        // Validasi JSON headers & body
        $headers  = null;
        $body     = null;
        $fieldMap = null;

        if ($request->filled('headers')) {
            $headers = json_decode($request->headers_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['headers_json' => 'Format Headers JSON tidak valid.'])->withInput();
            }
        }

        if ($request->filled('body_json')) {
            $body = json_decode($request->body_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['body_json' => 'Format Body JSON tidak valid.'])->withInput();
            }
        }

        if ($request->filled('field_map_json')) {
            $fieldMap = json_decode($request->field_map_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['field_map_json' => 'Format Field Map JSON tidak valid.'])->withInput();
            }
        }

        Supplier::create([
            'name'          => $request->name,
            'api_url'       => $request->api_url,
            'method'        => $request->method,
            'headers'       => $headers,
            'body'          => $body,
            'param_start'   => $request->param_start,
            'param_end'     => $request->param_end,
            'response_path' => $request->response_path,
            'field_map'     => $fieldMap,
            'is_active'     => $request->boolean('is_active', true),
            'notes'         => $request->notes,
        ]);

        return redirect()->route('rekon.index')->with('success', "Supplier {$request->name} berhasil ditambahkan.");
    }

    // -------------------------------------------------------
    // Form edit supplier
    // -------------------------------------------------------
    public function edit(Supplier $supplier)
    {
        return view('rekon.edit', compact('supplier'));
    }

    // -------------------------------------------------------
    // Update supplier
    // -------------------------------------------------------
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'api_url'       => 'required|url',
            'method'        => 'required|in:GET,POST,PUT,DELETE,PATCH',
            'param_start'   => 'required|string',
            'param_end'     => 'required|string',
            'response_path' => 'required|string',
        ]);

        $headers  = $request->filled('headers_json')   ? json_decode($request->headers_json, true)   : null;
        $body     = $request->filled('body_json')      ? json_decode($request->body_json, true)      : null;
        $fieldMap = $request->filled('field_map_json') ? json_decode($request->field_map_json, true) : null;

        $supplier->update([
            'name'          => $request->name,
            'api_url'       => $request->api_url,
            'method'        => $request->method,
            'headers'       => $headers,
            'body'          => $body,
            'param_start'   => $request->param_start,
            'param_end'     => $request->param_end,
            'response_path' => $request->response_path,
            'field_map'     => $fieldMap,
            'is_active'     => $request->boolean('is_active', true),
            'notes'         => $request->notes,
        ]);

        return redirect()->route('rekon.index')->with('success', "Supplier {$supplier->name} berhasil diupdate.");
    }

    // -------------------------------------------------------
    // Hapus supplier
    // -------------------------------------------------------
    public function destroy(Supplier $supplier)
    {
        $name = $supplier->name;
        $supplier->delete();
        return redirect()->route('rekon.index')->with('success', "Supplier {$name} berhasil dihapus.");
    }

    // -------------------------------------------------------
    // Sync data dari API supplier → simpan ke supplier_mutations
    // -------------------------------------------------------
    public function sync(Request $request, Supplier $supplier)
    {
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
        ]);

        $start = $request->start;
        $end   = $request->end;

        try {
            // Build request
            $http = Http::timeout(30);

            // Tambah headers jika ada
            if (!empty($supplier->headers)) {
                $http = $http->withHeaders($supplier->headers);
            }

            // Kirim request sesuai method
            $method = strtolower($supplier->method);
            $params = [
                $supplier->param_start => $start,
                $supplier->param_end   => $end,
            ];

            if ($method === 'get') {
                $response = $http->get($supplier->api_url, $params);
            } else {
                $bodyData = array_merge($supplier->body ?? [], $params);
                $response = $http->$method($supplier->api_url, $bodyData);
            }

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'error'   => "HTTP {$response->status()}: {$response->body()}",
                ]);
            }

            $json    = $response->json();
            $rawData = $supplier->extractFromPath($json);

            if (empty($rawData)) {
                return response()->json([
                    'success' => false,
                    'error'   => "Tidak ada data di path '{$supplier->response_path}'. Response: " . substr($response->body(), 0, 200),
                ]);
            }

            $saved   = 0;
            $skipped = 0;

            foreach ($rawData as $item) {
                $mapped = $supplier->mapItem($item);
                $noRef  = $mapped['no_reference'] ?? null;

                if (!$noRef) { $skipped++; continue; }

                $exists = SupplierMutation::where('supplier_id', $supplier->id)
                    ->where('no_reference', $noRef)
                    ->exists();

                if ($exists) { $skipped++; continue; }

                $dk     = str_replace(['.', ' ', ','], '', $mapped['debet_kredit'] ?? '0');
                $saldo  = str_replace(['.', ' ', ','], '', $mapped['saldo']        ?? '0');
                $amount = (float) $dk;

                // Parse tanggal (format: "02 Juni 2026" atau "2026-06-02")
                $tanggal = $this->parseDate($mapped['tanggal'] ?? '');

                SupplierMutation::create([
                    'supplier_id'  => $supplier->id,
                    'no_reference' => $noRef,
                    'tanggal'      => $tanggal,
                    'keterangan'   => $mapped['keterangan'] ?? null,
                    'debet_kredit' => $amount,
                    'saldo'        => (float) $saldo,
                    'type'         => $amount >= 0 ? 'kredit' : 'debet',
                    'raw_data'     => $item,
                    'sync_start'   => $start,
                    'sync_end'     => $end,
                ]);

                $saved++;
            }

            AuditLog::create([
                'user_id'    => auth()->id(),
                'activity'   => 'SYNC',
                'model_type' => 'SupplierMutation',
                'details'    => "Sync supplier {$supplier->name}: {$saved} data baru, {$skipped} duplikat.",
            ]);

            return response()->json([
                'success' => true,
                'message' => "Sync berhasil: {$saved} data baru, {$skipped} duplikat.",
                'saved'   => $saved,
                'skipped' => $skipped,
            ]);

        } catch (\Exception $e) {
            Log::error("Supplier sync error [{$supplier->name}]: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // -------------------------------------------------------
    // Test koneksi API supplier (tanpa simpan)
    // -------------------------------------------------------
    public function testApi(Request $request, Supplier $supplier)
    {
        try {
            $http   = Http::timeout(10);
            if (!empty($supplier->headers)) $http = $http->withHeaders($supplier->headers);

            $method = strtolower($supplier->method);
            $params = [
                $supplier->param_start => now()->startOfMonth()->format('Y-m-d'),
                $supplier->param_end   => now()->format('Y-m-d'),
            ];

            $response = $method === 'get'
                ? $http->get($supplier->api_url, $params)
                : $http->$method($supplier->api_url, array_merge($supplier->body ?? [], $params));

            $json    = $response->json();
            $rawData = $supplier->extractFromPath($json ?? []);
            $sample  = !empty($rawData) ? $rawData[0] : null;

            return response()->json([
                'success'     => $response->successful(),
                'http_status' => $response->status(),
                'data_count'  => count($rawData),
                'sample'      => $sample,
                'raw_preview' => substr($response->body(), 0, 500),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // -------------------------------------------------------
    // Jalankan rekonsiliasi
    // -------------------------------------------------------
    public function rekon(Request $request, Supplier $supplier)
    {
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
        ]);

        $start = $request->start;
        $end   = $request->end;

        // Hapus hasil rekon lama untuk periode ini
        RekonResult::where('supplier_id', $supplier->id)
            ->where('periode_start', $start)
            ->where('periode_end', $end)
            ->delete();

        // Data lokal (transaksi) dalam periode
        $localTrx = Transaction::whereBetween('trx_date', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->get()
            ->keyBy('trx_id');

        // Data supplier mutations dalam periode
        $supplierMut = SupplierMutation::where('supplier_id', $supplier->id)
            ->periode($start, $end)
            ->get()
            ->keyBy('no_reference');

        $results  = [];
        $matched  = 0;
        $onlyLocal    = 0;
        $onlySupplier = 0;
        $selisih  = 0;

        // Cek semua transaksi lokal
        foreach ($localTrx as $trxId => $trx) {
            if ($supplierMut->has($trxId)) {
                $supMut       = $supplierMut[$trxId];
                $amountSup    = abs($supMut->debet_kredit);
                $amountLocal  = $trx->amount;
                $diff         = $amountLocal - $amountSup;
                $status       = abs($diff) < 1 ? 'match' : 'selisih';

                if ($status === 'match') $matched++;
                else $selisih++;

                $results[] = [
                    'supplier_id'           => $supplier->id,
                    'periode_start'         => $start,
                    'periode_end'           => $end,
                    'transaction_trx_id'    => $trxId,
                    'supplier_no_reference' => $trxId,
                    'amount_local'          => $amountLocal,
                    'amount_supplier'       => $amountSup,
                    'selisih'               => $diff,
                    'status'                => $status,
                    'detail'                => json_encode([
                        'trx_date'    => $trx->trx_date,
                        'keterangan'  => $supMut->keterangan,
                        'local_status'=> $trx->status,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                $onlyLocal++;
                $results[] = [
                    'supplier_id'           => $supplier->id,
                    'periode_start'         => $start,
                    'periode_end'           => $end,
                    'transaction_trx_id'    => $trxId,
                    'supplier_no_reference' => null,
                    'amount_local'          => $trx->amount,
                    'amount_supplier'       => 0,
                    'selisih'               => $trx->amount,
                    'status'                => 'only_local',
                    'detail'                => json_encode(['trx_date' => $trx->trx_date, 'local_status' => $trx->status]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Cek supplier mutations yang tidak ada di lokal
        foreach ($supplierMut as $noRef => $mut) {
            if (!$localTrx->has($noRef)) {
                $onlySupplier++;
                $results[] = [
                    'supplier_id'           => $supplier->id,
                    'periode_start'         => $start,
                    'periode_end'           => $end,
                    'transaction_trx_id'    => null,
                    'supplier_no_reference' => $noRef,
                    'amount_local'          => 0,
                    'amount_supplier'       => abs($mut->debet_kredit),
                    'selisih'               => -abs($mut->debet_kredit),
                    'status'                => 'only_supplier',
                    'detail'                => json_encode(['tanggal' => $mut->tanggal, 'keterangan' => $mut->keterangan]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Bulk insert
        foreach (array_chunk($results, 100) as $chunk) {
            RekonResult::insert($chunk);
        }

        return response()->json([
            'success'       => true,
            'summary' => [
                'match'         => $matched,
                'only_local'    => $onlyLocal,
                'only_supplier' => $onlySupplier,
                'selisih'       => $selisih,
                'total'         => count($results),
            ],
        ]);
    }

    // -------------------------------------------------------
    // Lihat hasil rekon
    // -------------------------------------------------------
    public function rekonResult(Request $request, Supplier $supplier)
    {
        $start  = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
        $end    = $request->get('end',   now()->format('Y-m-d'));
        $status = $request->get('status');

        $query = RekonResult::where('supplier_id', $supplier->id)
            ->where('periode_start', $start)
            ->where('periode_end', $end);

        if ($status) $query->where('status', $status);

        $results = $query->orderBy('status')->paginate(50)->withQueryString();

        $summary = RekonResult::where('supplier_id', $supplier->id)
            ->where('periode_start', $start)
            ->where('periode_end', $end)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('rekon.result', compact('supplier', 'results', 'summary', 'start', 'end'));
    }

    // -------------------------------------------------------
    // Helper: parse tanggal berbagai format
    // -------------------------------------------------------
    private function parseDate(string $raw): string
    {
        $raw = trim($raw);
        if (empty($raw)) return now()->format('Y-m-d');

        // Format: "2026-06-02"
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $raw)) {
            return substr($raw, 0, 10);
        }

        // Format: "02/06/2026"
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}/', $raw)) {
            return Carbon::createFromFormat('d/m/Y', substr($raw, 0, 10))->format('Y-m-d');
        }

        // Format: "02 Juni 2026"
        $months = [
            'Januari'=>'01','Februari'=>'02','Maret'=>'03','April'=>'04',
            'Mei'=>'05','Juni'=>'06','Juli'=>'07','Agustus'=>'08',
            'September'=>'09','Oktober'=>'10','November'=>'11','Desember'=>'12',
        ];
        $parts = explode(' ', $raw);
        if (count($parts) >= 3) {
            $day   = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $month = $months[$parts[1]] ?? '01';
            $year  = $parts[2];
            return "{$year}-{$month}-{$day}";
        }

        return now()->format('Y-m-d');
    }
}
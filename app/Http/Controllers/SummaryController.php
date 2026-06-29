<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\SupplierMutation;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Cookie\CookieJar;
use Carbon\Carbon;

class SummaryController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        $startOfDay = $selectedDate->copy()->startOfDay();
        $endOfDay   = $selectedDate->copy()->endOfDay();

        // ── Transaksi dari DB lokal ──
        $trxQuery = Transaction::whereDate('trx_date', $date);

        $totalTrx  = (clone $trxQuery)->count();

        // Status di DB adalah 'sukses' dan 'gagal' (dari TransactionController upload CSV)
        $trxSukses = (clone $trxQuery)->where('status', 'sukses')->count();
        $trxGagal  = (clone $trxQuery)->where('status', 'gagal')->count();

        $successRate = $totalTrx > 0 ? round(($trxSukses / $totalTrx) * 100, 1) : 0;

        // Penjualan = credit > 0, bukan DEPOSIT/REFUND, status sukses
        $totalPenjualan = (clone $trxQuery)
            ->where('status', 'sukses')
            ->whereNotIn('product_code', ['DEPOSIT', 'REFUND', 'REFFUND'])
            ->where('credit', '>', 0)
            ->sum('credit');

        // Pembelian = debit > 0
        $totalPembelian = (clone $trxQuery)
            ->where('debit', '>', 0)
            ->sum('debit');

        $profit = $totalPenjualan - $totalPembelian;

        // ── Grafik per jam ──
        $grafikJam = Transaction::selectRaw("DATE_FORMAT(trx_date, '%H:00') as jam, COUNT(*) as jumlah, SUM(amount) as nominal")
            ->whereDate('trx_date', $date)
            ->groupBy('jam')
            ->orderBy('jam')
            ->get();

        // ── Saldo Rekening (dari API Samantara) ──
        $saldoRekening = null;
        try {
            $cookieJar = new CookieJar();
            $loginRes  = Http::timeout(15)->withOptions(['cookies' => $cookieJar])
                ->get('https://mpn-gateway.samantara.com/mpnbjt/login');
            if ($loginRes->successful()) {
                $json     = $loginRes->json();
                $accounts = $json['accounts'] ?? [];
                if (!empty($accounts)) {
                    $raw = $accounts[0]['saldo'] ?? '0';
                    $saldoRekening = (float) str_replace(['.', ',', ' '], ['', '.', ''], $raw);
                }
            }
        } catch (\Exception $e) {
            // Gagal ambil saldo rekening, tidak apa-apa
        }

        // ── Saldo Supplier (dari supplier_mutations terbaru) ──
        $saldoSupplier = SupplierMutation::orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->value('saldo');

        // ── 7 hari terakhir untuk mini chart ──
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $day   = Carbon::today()->subDays($i);
            $count = Transaction::whereDate('trx_date', $day)->count();
            $nom   = Transaction::whereDate('trx_date', $day)->sum('amount');
            $last7Days->push([
                'label'   => $day->format('d/m'),
                'jumlah'  => $count,
                'nominal' => $nom,
            ]);
        }

        return view('summary.index', compact(
            'date', 'selectedDate',
            'totalTrx', 'trxSukses', 'trxGagal', 'successRate',
            'totalPenjualan', 'totalPembelian', 'profit',
            'saldoRekening', 'saldoSupplier',
            'grafikJam', 'last7Days'
        ));
    }
}
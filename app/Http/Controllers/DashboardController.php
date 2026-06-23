<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Models\AuditLog; // <-- Memastikan model AuditLog sudah di-import

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ── Filter periode ──────────────────────────────
        $filter = $request->input('filter', 'harian');
        
        switch ($filter) {
            case 'mingguan':
                $startDate = Carbon::now()->subDays(6)->startOfDay()->toDateTimeString();
                $endDate   = Carbon::now()->endOfDay()->toDateTimeString();
                $groupFormat = "DATE(trx_date)";
                $labelFormat = 'd M';
                break;
            case 'bulanan':
                $startDate = Carbon::now()->subDays(29)->startOfDay()->toDateTimeString();
                $endDate   = Carbon::now()->endOfDay()->toDateTimeString();
                $groupFormat = "DATE(trx_date)";
                $labelFormat = 'd M';
                break;
            default: // harian
                $startDate = Carbon::now()->startOfDay()->toDateTimeString();
                $endDate   = Carbon::now()->endOfDay()->toDateTimeString();
                $groupFormat = "strftime('%H:00', trx_date)";
                $labelFormat = null;
                break;
        }

        // ── Data grafik ──────────────────────────────────
        if ($filter === 'harian') {
            $chartData = Transaction::selectRaw(
                "DATE_FORMAT(trx_date, '%H:00') as period"                )
                ->whereBetween('trx_date', [$startDate, $endDate])
                ->groupBy('period')
                ->orderBy('period', 'asc')
                ->get();
        } else {
            $chartData = Transaction::selectRaw(
                    "DATE(trx_date) as period, SUM(amount) as total, COUNT(*) as jumlah"
                )
                ->whereBetween('trx_date', [$startDate, $endDate])
                ->groupBy('period')
                ->orderBy('period', 'asc')
                ->get();
        }

        $labels = [];
        $values = [];
        $counts = [];

        foreach ($chartData as $data) {
            if ($filter !== 'harian' && $labelFormat && $data->period) {
                try {
                    $labels[] = Carbon::parse($data->period)->format($labelFormat);
                } catch (\Exception $e) {
                    $labels[] = $data->period;
                }
            } else {
                $labels[] = $data->period ?? '00:00';
            }
            $values[] = (float) $data->total;
            $counts[] = (int)  $data->jumlah;
        }

        // ── 10 transaksi terbaru ──────────────────────────
        $transactions = Transaction::orderBy('trx_date', 'desc')
                                   ->take(10)
                                   ->get();

        // ── Statistik kartu (Dioptimasi dengan format string untuk SQLite) ──────────────────────────────
        $todayStart = Carbon::now()->startOfDay()->toDateTimeString();
        $todayEnd   = Carbon::now()->endOfDay()->toDateTimeString();
        
        $statHariIni = Transaction::whereBetween('trx_date', [$todayStart, $todayEnd])->sum('amount') ?? 0;
        $statMinggu  = Transaction::where('trx_date', '>=', Carbon::now()->subDays(6)->startOfDay()->toDateTimeString())->sum('amount') ?? 0;
        $statBulan   = Transaction::where('trx_date', '>=', Carbon::now()->subDays(29)->startOfDay()->toDateTimeString())->sum('amount') ?? 0;
        $statTotal   = Transaction::count();

        $countHariIni = Transaction::whereBetween('trx_date', [$todayStart, $todayEnd])->count();
        $countMinggu  = Transaction::where('trx_date', '>=', Carbon::now()->subDays(6)->startOfDay()->toDateTimeString())->count();
        $countBulan   = Transaction::where('trx_date', '>=', Carbon::now()->subDays(29)->startOfDay()->toDateTimeString())->count();

        // ── Audit Log Baru (Menarik 10 log aktivitas sistem terbaru beserta data user terkait) ──
        $auditLogs = AuditLog::with('user')->orderBy('created_at', 'desc')->take(10)->get();

        // Mengirimkan semua data termasuk auditLogs ke dalam file View dashboard
        return view('dashboard', compact(
            'transactions',
            'labels', 'values', 'counts',
            'filter',
            'statHariIni', 'statMinggu', 'statBulan', 'statTotal',
            'countHariIni', 'countMinggu', 'countBulan',
            'auditLogs' // <-- SEKARANG VARIABEL INI SUDAH IKUT TERKIRIM
        ));
    }
}
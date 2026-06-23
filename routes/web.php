<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/sync-api', [TransactionController::class, 'syncApi'])->name('api.sync');
    Route::get('/export-transactions', [TransactionController::class, 'exportCsv'])->name('transactions.export');
    
    // Taruh di sini supaya aman di dalam middleware auth!
    Route::post('/transactions/upload-csv', [TransactionController::class, 'uploadCsv'])->name('transactions.upload-csv');

    Route::get('/users',            [UserController::class, 'index'])  ->name('users.index');
    Route::post('/users',           [UserController::class, 'store'])  ->name('users.store');
    Route::delete('/users/{user}',  [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/profile',    [ProfileController::class, 'edit'])   ->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update']) ->name('profile.update');
       Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/mutasi', [App\Http\Controllers\MutasiController::class, 'index'])->name('mutasi.index');
});

// ← DEBUG API (Sudah dibersihkan dari sisa kode yang nyangkut)
Route::get('/debug-api', function () {
    $url = "https://mpn-gateway.samantara.com/mpnbjt/api/mutasi?start=2026-01-01&end=2026-06-12"; // Menggunakan range dari Januari sesuai catatan kamu
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(30)
            ->withHeaders(['Accept' => 'application/json'])
            ->get($url);
        $raw  = $response->json();
        $body = $response->body();
        $sample = null;
        if (is_array($raw)) {
            foreach (['data','mutasi','payload','result','records','list','items'] as $key) {
                if (isset($raw[$key]) && is_array($raw[$key]) && count($raw[$key]) > 0) {
                    $sample = $raw[$key][0];
                    break;
                }
            }
            if (!$sample && isset($raw[0])) $sample = $raw[0];
        }
        return response()->json([
            'status_http'    => $response->status(),
            'top_level_keys' => is_array($raw) ? array_keys($raw) : 'BUKAN ARRAY',
            'sample_item'    => $sample,
            'raw_preview'    => substr($body, 0, 800),
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

require __DIR__.'/auth.php';
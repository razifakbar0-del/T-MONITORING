<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\MutasiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RekonController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Transaksi
    Route::get('/transactions',             [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/export',      [TransactionController::class, 'exportCsv'])->name('transactions.export');
    Route::get('/transactions/{id}',        [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/sync-api', function () {
        return redirect()->route('dashboard')->with('error', 'Fitur Sinkronisasi API sudah tidak digunakan. Gunakan Upload CSV atau halaman Mutasi Rekening.');
    })->name('api.sync');
    Route::post('/transactions/upload-csv', [TransactionController::class, 'uploadCsv'])->name('transactions.upload-csv');

    // Mutasi Rekening
    Route::get('/mutasi',         [MutasiController::class, 'index'])->name('mutasi.index');
    Route::get('/mutasi/fetch',   [MutasiController::class, 'fetch'])->name('mutasi.fetch');
    Route::get('/mutasi/from-db', [MutasiController::class, 'fromDb'])->name('mutasi.fromDb');

    // Rekon Supplier
    Route::prefix('rekon')->name('rekon.')->group(function () {
        Route::get('/',                    [RekonController::class, 'index'])->name('index');
        Route::get('/create',              [RekonController::class, 'create'])->name('create');
        Route::post('/',                   [RekonController::class, 'store'])->name('store');
        Route::get('/{supplier}/edit',     [RekonController::class, 'edit'])->name('edit');
        Route::put('/{supplier}',          [RekonController::class, 'update'])->name('update');
        Route::delete('/{supplier}',       [RekonController::class, 'destroy'])->name('destroy');
        Route::post('/{supplier}/sync',    [RekonController::class, 'sync'])->name('sync');
        Route::post('/{supplier}/rekon',   [RekonController::class, 'rekon'])->name('rekon');
        Route::get('/{supplier}/result',   [RekonController::class, 'rekonResult'])->name('result');
        Route::get('/{supplier}/test-api', [RekonController::class, 'testApi'])->name('test-api');
        
     }); 
    //summary 
    Route::get('/summary', [App\Http\Controllers\SummaryController::class, 'index'])->name('summary.index');
    

    // User Management
    Route::get('/users',           [UserController::class, 'index'])  ->name('users.index');
    Route::post('/users',          [UserController::class, 'store'])  ->name('users.store');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Profile
    Route::get('/profile',    [ProfileController::class, 'edit'])   ->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update']) ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
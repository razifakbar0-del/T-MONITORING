<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        AuditLog::create([
            'user_id'    => Auth::id(), // Mengambil ID user yang sedang login (jika via sistem)
            'activity'   => 'CREATE',
            'model_type' => 'Transaction',
            'model_id'   => $transaction->id,
            'details'    => "Transaksi baru berhasil ditambahkan/disinkronisasi dengan jumlah Rp " . number_format($transaction->amount, 0, ',', '.'),
        ]);
    }

    public function deleted(Transaction $transaction): void
    {
        AuditLog::create([
            'user_id'    => Auth::id(),
            'activity'   => 'DELETE',
            'model_type' => 'Transaction',
            'model_id'   => $transaction->id,
            'details'    => "Transaksi ID {$transaction->id} dihapus dari sistem.",
        ]);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    // Menentukan kolom tabel transaksi yang boleh diisi otomatis saat sync API
    protected $fillable = [
        'api_source_id',
        'trx_id',
        'trx_date',
        'amount',
        'status',
        'customer_name'
    ];

    /**
     * Relasi balik ke model ApiSource
     */
    public function apiSource()
    {
        return $this->belongsTo(ApiSource::class, 'api_source_id');
    }
}
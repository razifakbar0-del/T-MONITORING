<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'api_source_id',
        'reseller_name',
        'msisdn',
        'supplier',
        'product_code',
        'sn',
        'request_id',
        'trx_id',
        'trx_date',
        'amount',
        'status',
        'customer_name',
        'debit',
        'credit',
        'balance',
        'profit',
    ];

    protected $casts = [
        'trx_date' => 'datetime',
        'amount'   => 'float',
        'debit'    => 'integer',
        'credit'   => 'integer',
        'balance'  => 'integer',
        'profit'   => 'integer',
    ];

    public function apiSource()
    {
        return $this->belongsTo(ApiSource::class, 'api_source_id');
    }

    // Scope: hanya transaksi sukses (RC = 00 → status = 'sukses')
    public function scopeSukses($query)
    {
        return $query->where('status', 'sukses');
    }

    // Scope: filter by tanggal
    public function scopePeriode($query, $start, $end)
    {
        return $query->whereBetween('trx_date', [$start . ' 00:00:00', $end . ' 23:59:59']);
    }
}
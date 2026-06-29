<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierMutation extends Model
{
    protected $fillable = [
        'supplier_id', 'no_reference', 'tanggal', 'keterangan',
        'debet_kredit', 'saldo', 'type', 'raw_data',
        'sync_start', 'sync_end',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'sync_start'   => 'date',
        'sync_end'     => 'date',
        'debet_kredit' => 'float',
        'saldo'        => 'float',
        'raw_data'     => 'array',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function scopePeriode($query, $start, $end)
    {
        return $query->whereBetween('tanggal', [$start, $end]);
    }
}
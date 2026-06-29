<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankMutation extends Model
{
    protected $fillable = [
        'no_reference',
        'tanggal',
        'keterangan',
        'debet_kredit',
        'saldo',
        'type',
        'no_urut',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'start_date'   => 'date',
        'end_date'     => 'date',
        'debet_kredit' => 'float',
        'saldo'        => 'float',
    ];

    // Scope filter by date range
    public function scopePeriode($query, $start, $end)
    {
        return $query->whereBetween('tanggal', [$start, $end]);
    }

    // Scope kredit saja
    public function scopeKredit($query)
    {
        return $query->where('type', 'kredit');
    }

    // Scope debet saja
    public function scopeDebet($query)
    {
        return $query->where('type', 'debet');
    }
}
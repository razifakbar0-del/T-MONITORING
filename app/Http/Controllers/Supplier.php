<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'url',
        'method',
        'headers',
        'body',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'headers' => 'array',
        'body'    => 'array',
    ];

    public function rekon()
    {
        return $this->hasMany(SupplierRekon::class);
    }

    public function rekonTerakhir()
    {
        return $this->hasOne(SupplierRekon::class)->latestOfMany();
    }

    // Relasi ke transaksi berdasarkan kolom supplier (nama/kode)
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'supplier', 'kode');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
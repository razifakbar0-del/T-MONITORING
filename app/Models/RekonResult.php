<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekonResult extends Model
{
    protected $fillable = [
        'supplier_id', 'periode_start', 'periode_end',
        'transaction_trx_id', 'supplier_no_reference',
        'amount_local', 'amount_supplier', 'selisih',
        'status', 'detail',
    ];

    protected $casts = [
        'periode_start'    => 'date',
        'periode_end'      => 'date',
        'amount_local'     => 'float',
        'amount_supplier'  => 'float',
        'selisih'          => 'float',
        'detail'           => 'array',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
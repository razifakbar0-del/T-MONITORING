<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiTransaction extends Model
{
    // Paksa model ini menggunakan koneksi mutasi eksternal
    protected $connection = 'mysql_mutasi';

    // Ganti dengan nama tabel asli yang ada di dalam database mutasi_transactions
    protected $table = 'nama_tabel_di_sana'; 
}
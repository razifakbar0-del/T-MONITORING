<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiSource extends Model
{
    // Menentukan kolom mana saja yang boleh diisi datanya
    protected $fillable = ['name', 'url', 'is_active'];

    /**
     * Relasi ke model Transaction (Satu ApiSource memiliki banyak Transaction)
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'api_source_id');
    }
}
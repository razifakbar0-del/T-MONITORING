<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'api_url', 'method', 'headers', 'body',
        'param_start', 'param_end', 'response_path',
        'field_map', 'is_active', 'notes',
    ];

    protected $casts = [
        'headers'   => 'array',
        'body'      => 'array',
        'field_map' => 'array',
        'is_active' => 'boolean',
    ];

    public function mutations()
    {
        return $this->hasMany(SupplierMutation::class);
    }

    public function rekonResults()
    {
        return $this->hasMany(RekonResult::class);
    }

    // Ambil data dari nested response path ("data" atau "result.data")
    public function extractFromPath(array $response): array
    {
        $keys = explode('.', $this->response_path ?? 'data');
        $data = $response;
        foreach ($keys as $key) {
            $data = $data[$key] ?? [];
            if (!is_array($data)) return [];
        }
        return $data;
    }

    // Map field response supplier ke standar kita berdasarkan field_map
    public function mapItem(array $item): array
    {
        $map = $this->field_map ?? [
            'no_reference' => 'no_reference',
            'tanggal'      => 'tanggal',
            'keterangan'   => 'keterangan',
            'debet_kredit' => 'debet_kredit',
            'saldo'        => 'saldo',
        ];
        $result = [];
        foreach ($map as $ourField => $theirField) {
            $result[$ourField] = $item[$theirField] ?? null;
        }
        return $result;
    }
}
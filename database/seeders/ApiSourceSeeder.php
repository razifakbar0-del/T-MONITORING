<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApiSource; // Pastikan model ini sudah ada

class ApiSourceSeeder extends Seeder
{
    public function run(): void
    {
        ApiSource::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'API Samantara',
                'endpoint' => 'https://mpn-gateway.samantara.com/mpnbjt/api/mutasi',
                'status' => 'active'
            ]
        );
    }
}
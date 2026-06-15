<?php

namespace Database\Seeders; // <-- PASTIKAN BARIS INI ADA DAN BENAR!

use Illuminate\Database\Seeder;
// use App\Models\User; // Jika ingin memanggil model User langsung

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Di sini kamu harus memanggil UserSeeder agar akun admin terbuat
        $this->call([
            UserSeeder::class,
            ApiSourceSeeder::class,
        ]);
    }
}
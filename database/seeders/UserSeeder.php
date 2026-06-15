<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Admin
        User::create([
            'name'     => 'Admin Monitoring',
            'email'    => 'admin@mail.com',
            'password' => Hash::make('password123'), // Password kamu nanti: password123
            'role'     => 'admin', // WAJIB HURUF KECIL SEMUA
        ]);

        // 2. Buat Akun Operator
        User::create([
            'name'     => 'Operator Lapangan',
            'email'    => 'operator@mail.com',
            'password' => Hash::make('password123'),
            'role'     => 'operator', // WAJIB HURUF KECIL SEMUA
        ]);

        // 3. Buat Akun Viewer
        User::create([
            'name'     => 'Viewer',
            'email'    => 'viewer@mail.com',
            'password' => Hash::make('password123'),
            'role'     => 'viewer', // WAJIB HURUF KECIL SEMUA
        ]);
    }
}
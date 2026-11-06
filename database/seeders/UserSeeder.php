<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin/Waka
        User::updateOrCreate(
            ['email' => 'admin@sekolah.com'],
            [
                'name' => 'Waka Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'admin',
                'phone' => '6281234567890',
            ]
        );

        // PIC Ruang 2
        User::updateOrCreate(
            ['email' => 'pic2@sekolah.com'],
            [
                'name' => 'Bu Sari (PIC)',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'pic',
                'phone' => '6281122334455',
            ]
        );

        // Guru 1
        User::updateOrCreate(
            ['email' => 'guru1@sekolah.com'],
            [
                'name' => 'Pak Guru Satu',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'guru',
            ]
        );

        // Guru/Organisasi 2
        User::updateOrCreate(
            ['email' => 'osis@sekolah.com'],
            [
                'name' => 'OSIS Sekolah',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'guru',
            ]
        );
    }
}

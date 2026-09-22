<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Buat 5 akun pengguna default untuk masing-masing peran sistem.
     * Semua akun dapat digunakan login langsung dengan kata sandi: "password"
     */
    public function run(): void
    {
        $defaultUsers = [
            [
                'email' => 'superadmin@perikanan.go.id',
                'name' => 'Super Administrator',
                'role' => 'super-admin',
            ],
            [
                'email' => 'admin@perikanan.go.id',
                'name' => 'Administrator Data',
                'role' => 'admin',
            ],
            [
                'email' => 'verifikator@perikanan.go.id',
                'name' => 'Petugas Verifikator',
                'role' => 'verifikator',
            ],
            [
                'email' => 'petugas@perikanan.go.id',
                'name' => 'Petugas Lapangan',
                'role' => 'petugas-lapangan',
            ],
            [
                'email' => 'viewer@perikanan.go.id',
                'name' => 'Pengguna Viewer',
                'role' => 'viewer',
            ],
        ];

        foreach ($defaultUsers as $item) {
            $user = User::firstOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$item['role']]);
        }
    }
}

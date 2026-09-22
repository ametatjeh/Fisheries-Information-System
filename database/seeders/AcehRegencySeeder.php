<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Models\Regency;
use Illuminate\Database\Seeder;

class AcehRegencySeeder extends Seeder
{
    /**
     * Seed seluruh 23 Kabupaten & Kota resmi di Provinsi Aceh.
     * Kode resmi Kemendagri: 11.01 s/d 11.18 (18 Kabupaten) dan 11.71 s/d 11.75 (5 Kota).
     *
     * Idempoten: menggunakan updateOrCreate agar aman dijalankan ulang tanpa duplikasi.
     */
    public function run(): void
    {
        $aceh = Province::where('code', '11')->firstOrFail();

        $regencies = [
            // 18 Kabupaten
            ['code' => '11.01', 'name' => 'Aceh Selatan', 'type' => 'kabupaten'],
            ['code' => '11.02', 'name' => 'Aceh Tenggara', 'type' => 'kabupaten'],
            ['code' => '11.03', 'name' => 'Aceh Timur', 'type' => 'kabupaten'],
            ['code' => '11.04', 'name' => 'Aceh Tengah', 'type' => 'kabupaten'],
            ['code' => '11.05', 'name' => 'Aceh Barat', 'type' => 'kabupaten'],
            ['code' => '11.06', 'name' => 'Aceh Besar', 'type' => 'kabupaten'],
            ['code' => '11.07', 'name' => 'Pidie', 'type' => 'kabupaten'],
            ['code' => '11.08', 'name' => 'Bireuen', 'type' => 'kabupaten'],
            ['code' => '11.09', 'name' => 'Aceh Utara', 'type' => 'kabupaten'],
            ['code' => '11.10', 'name' => 'Aceh Barat Daya', 'type' => 'kabupaten'],
            ['code' => '11.11', 'name' => 'Gayo Lues', 'type' => 'kabupaten'],
            ['code' => '11.12', 'name' => 'Aceh Tamiang', 'type' => 'kabupaten'],
            ['code' => '11.13', 'name' => 'Nagan Raya', 'type' => 'kabupaten'],
            ['code' => '11.14', 'name' => 'Aceh Jaya', 'type' => 'kabupaten'],
            ['code' => '11.15', 'name' => 'Bener Meriah', 'type' => 'kabupaten'],
            ['code' => '11.16', 'name' => 'Pidie Jaya', 'type' => 'kabupaten'],
            ['code' => '11.17', 'name' => 'Simeulue', 'type' => 'kabupaten'],
            ['code' => '11.18', 'name' => 'Aceh Singkil', 'type' => 'kabupaten'],

            // 5 Kota
            ['code' => '11.71', 'name' => 'Banda Aceh', 'type' => 'kota'],
            ['code' => '11.72', 'name' => 'Sabang', 'type' => 'kota'],
            ['code' => '11.73', 'name' => 'Lhokseumawe', 'type' => 'kota'],
            ['code' => '11.74', 'name' => 'Langsa', 'type' => 'kota'],
            ['code' => '11.75', 'name' => 'Subulussalam', 'type' => 'kota'],
        ];

        foreach ($regencies as $r) {
            Regency::updateOrCreate(
                ['code' => $r['code']],
                [
                    'province_id' => $aceh->id,
                    'name' => $r['name'],
                    'type' => $r['type'],
                ]
            );
        }

        // Hapus kabupaten/kota di luar provinsi Aceh jika ada
        Regency::where('province_id', '!=', $aceh->id)->delete();
    }
}

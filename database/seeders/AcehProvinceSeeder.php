<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Database\Seeder;

class AcehProvinceSeeder extends Seeder
{
    /**
     * Seed data provinsi khusus Aceh (Kode Resmi Kemendagri: 11).
     * Memastikan tabel provinces HANYA berisi 1 baris (Provinsi Aceh).
     *
     * Idempoten: aman dijalankan berulang kali.
     */
    public function run(): void
    {
        // 1. Buat atau perbarui Provinsi Aceh
        Province::updateOrCreate(
            ['code' => '11'],
            ['name' => 'Aceh']
        );

        // 2. Bersihkan data wilayah non-Aceh secara aman dari hierarki terbawah
        $nonAcehProvinces = Province::where('code', '!=', '11')->get();

        if ($nonAcehProvinces->isNotEmpty()) {
            $nonAcehProvinceIds = $nonAcehProvinces->pluck('id');
            $nonAcehRegencies = Regency::whereIn('province_id', $nonAcehProvinceIds)->get();
            $nonAcehRegencyIds = $nonAcehRegencies->pluck('id');
            $nonAcehDistricts = District::whereIn('regency_id', $nonAcehRegencyIds)->get();
            $nonAcehDistrictIds = $nonAcehDistricts->pluck('id');

            Village::whereIn('district_id', $nonAcehDistrictIds)->delete();
            District::whereIn('id', $nonAcehDistrictIds)->delete();
            Regency::whereIn('id', $nonAcehRegencyIds)->delete();
            Province::whereIn('id', $nonAcehProvinceIds)->delete();
        }
    }
}

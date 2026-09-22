<?php

namespace Database\Seeders;

use App\Models\VesselType;
use Illuminate\Database\Seeder;

class VesselTypeSeeder extends Seeder
{
    /**
     * Seed master data tipe armada kapal penangkap ikan:
     * - Perahu Tanpa Motor (Jukung / Sampan Tradisional)
     * - Perahu Motor Tempel (Mesin Longtail / Outboard Engine)
     * - Kapal Motor (KM) dengan klasifikasi Gross Tonnage (GT) standar KKP/BPS
     *
     * Idempoten: aman dijalankan ulang.
     */
    public function run(): void
    {
        $types = [
            [
                'code' => 'PTM-01',
                'name' => 'Perahu Tanpa Motor (Jukung / Sampan)',
                'category' => 'tanpa_motor',
                'tonnage_range' => '0 GT',
                'description' => 'Perahu kayu tradisional bertenaga dayung/layar untuk operasional perikanan subsisten di perairan pantai/estuari.',
                'is_active' => true,
            ],
            [
                'code' => 'PMT-01',
                'name' => 'Perahu Motor Tempel Kecil (< 3 GT)',
                'category' => 'motor_tempel',
                'tonnage_range' => '< 3 GT',
                'description' => 'Perahu kayu/fiber bermesin tempel (Robin/Yamaha 15-40 PK) untuk nelayan pancing ulur dan jaring insang harian (one-day fishing).',
                'is_active' => true,
            ],
            [
                'code' => 'PMT-02',
                'name' => 'Perahu Motor Tempel Sedang (3 - 5 GT)',
                'category' => 'motor_tempel',
                'tonnage_range' => '3 - 5 GT',
                'description' => 'Perahu motor tempel bermesin ganda dengan jangkauan melaut hingga 12 mil laut dari garis pantai.',
                'is_active' => true,
            ],
            [
                'code' => 'KM-01',
                'name' => 'Kapal Motor Kecil (< 5 GT)',
                'category' => 'kapal_motor',
                'tonnage_range' => '< 5 GT',
                'description' => 'Kapal motor bermesin dalam (inboard engine) ukuran kecil untuk penangkapan ikan pesisir.',
                'is_active' => true,
            ],
            [
                'code' => 'KM-02',
                'name' => 'Kapal Motor Sedang (5 - 10 GT / Boat Tep-Tep)',
                'category' => 'kapal_motor',
                'tonnage_range' => '5 - 10 GT',
                'description' => 'Armada khas nelayan Aceh (Boat Tep-tep kayu) berbobot 5-10 GT untuk alat tangkap pancing tonda, rawai, dan mini purse seine.',
                'is_active' => true,
            ],
            [
                'code' => 'KM-03',
                'name' => 'Kapal Motor Pukat Cincin (10 - 20 GT)',
                'category' => 'kapal_motor',
                'tonnage_range' => '10 - 20 GT',
                'description' => 'Kapal motor kayu/baja untuk operasional pukat langgar / purse seine berawak 15-25 orang nelayan (ABK).',
                'is_active' => true,
            ],
            [
                'code' => 'KM-04',
                'name' => 'Kapal Motor (20 - 30 GT)',
                'category' => 'kapal_motor',
                'tonnage_range' => '20 - 30 GT',
                'description' => 'Kapal motor berizin provinsi (DKP) dengan kemampuan melaut 3-7 hari ke perairan ZEEI dan perairan lepas pantai.',
                'is_active' => true,
            ],
            [
                'code' => 'KM-05',
                'name' => 'Kapal Motor Samudera (30 - 60 GT)',
                'category' => 'kapal_motor',
                'tonnage_range' => '30 - 60 GT',
                'description' => 'Kapal motor berizin pusat (Kementerian Kelautan dan Perikanan) untuk perikanan tuna longline dan purse seine samudera di WPPNRI 572.',
                'is_active' => true,
            ],
            [
                'code' => 'KM-06',
                'name' => 'Kapal Motor Industri (> 60 GT)',
                'category' => 'kapal_motor',
                'tonnage_range' => '> 60 GT',
                'description' => 'Kapal penangkap ikan skala industri berfasilitas pendingin palka beku (freezer) untuk melaut berminggu-minggu di laut lepas.',
                'is_active' => true,
            ],
        ];

        foreach ($types as $t) {
            VesselType::updateOrCreate(
                ['code' => $t['code']],
                $t
            );
        }
    }
}

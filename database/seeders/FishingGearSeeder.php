<?php

namespace Database\Seeders;

use App\Models\FishingGear;
use Illuminate\Database\Seeder;

class FishingGearSeeder extends Seeder
{
    /**
     * Seed master data alat penangkapan ikan (API):
     * - Jaring Lingkar (Purse Seine / Pukat Cincin / Pukat Langgar Aceh)
     * - Pancing (Handline / Pancing Ulur, Tuna Longline, Troll Line)
     * - Jaring Insang (Drift Gillnet, Trammel Net)
     * - Jaring Tarik & Angkat (Beach Seine / Pukat Pantai, Lift Net)
     * - Perangkap (Bubu Dasar, Bubu Laut Dalam)
     *
     * Klasifikasi mengacu pada Permen KP / Standar Statistik FAO.
     * Idempoten: aman dijalankan ulang.
     */
    public function run(): void
    {
        $gears = [
            [
                'code' => 'PS-01',
                'name' => 'Pukat Cincin Satu Kapal (Purse Seine)',
                'category' => 'jaring_lingkar',
                'description' => 'Alat tangkap utama kapal motor di Aceh (Pukat Langgar) untuk menangkap pelagis besar dan kecil (cakalang, tongkol, layang).',
                'is_active' => true,
            ],
            [
                'code' => 'PS-02',
                'name' => 'Pukat Cincin Mini (Mini Purse Seine)',
                'category' => 'jaring_lingkar',
                'description' => 'Pukat cincin ukuran sedang untuk kapal 5-15 GT menyasar kembung, selar, dan tongkol di perairan pesisir.',
                'is_active' => true,
            ],
            [
                'code' => 'LL-01',
                'name' => 'Rawai Tuna (Tuna Longline)',
                'category' => 'pancing',
                'description' => 'Pancing rawai hanyut samudera dengan ratusan mata pancing untuk target madidihang dan tuna mata besar di WPPNRI 572.',
                'is_active' => true,
            ],
            [
                'code' => 'LL-02',
                'name' => 'Rawai Dasar (Bottom Longline)',
                'category' => 'pancing',
                'description' => 'Rawai yang dipasang di dasar perairan karang/demersal untuk menangkap kerapu, kakap merah, dan pari.',
                'is_active' => true,
            ],
            [
                'code' => 'HL-01',
                'name' => 'Pancing Ulur (Handline)',
                'category' => 'pancing',
                'description' => 'Alat pancing sederhana tradisonal nelayan kecil (motor tempel/tanpa motor) untuk target tuna, cakalang, dan kuwe.',
                'is_active' => true,
            ],
            [
                'code' => 'TL-01',
                'name' => 'Pancing Tonda (Troll Line)',
                'category' => 'pancing',
                'description' => 'Pancing yang ditarik perahu bergerak menggunakan umpan tiruan untuk menyasar tenggiri, lemadang, dan cakalang.',
                'is_active' => true,
            ],
            [
                'code' => 'GN-01',
                'name' => 'Jaring Insang Hanyut (Drift Gillnet)',
                'category' => 'jaring_insang',
                'description' => 'Jaring insang permukaan yang hanyut terbawa arus untuk menangkap kembung, tongkol, dan tenggiri.',
                'is_active' => true,
            ],
            [
                'code' => 'GN-02',
                'name' => 'Jaring Insang Dasar (Bottom Set Gillnet)',
                'category' => 'jaring_insang',
                'description' => 'Jaring insang yang menetap di dasar perairan untuk menangkap ikan demersal, bawal, dan rajungan.',
                'is_active' => true,
            ],
            [
                'code' => 'TN-01',
                'name' => 'Jaring Tiga Lapis (Trammel Net)',
                'category' => 'jaring_insang',
                'description' => 'Jaring klitik berlapis tiga yang sangat selektif dan efektif untuk menangkap udang windu dan lobster di perairan pantai.',
                'is_active' => true,
            ],
            [
                'code' => 'BS-01',
                'name' => 'Pukat Pantai (Beach Seine / Pukat Darat)',
                'category' => 'jaring_tarik',
                'description' => 'Pukat tradisional (Pukat Darat Aceh) yang ditarik beramai-ramai oleh nelayan dari bibir pantai.',
                'is_active' => true,
            ],
            [
                'code' => 'TR-01',
                'name' => 'Bubu Dasar (Fish Trap)',
                'category' => 'perangkap',
                'description' => 'Perangkap kawat / bambu yang diletakkan di dasar terumbu karang untuk menangkap kerapu, kakap, dan lobster.',
                'is_active' => true,
            ],
            [
                'code' => 'LN-01',
                'name' => 'Bagan Perahu (Boat Lift Net)',
                'category' => 'jaring_angkat',
                'description' => 'Jaring angkat yang dipasang pada perahu berlampu untuk menangkap teri nasi dan cumi-cumi pada malam hari.',
                'is_active' => true,
            ],
        ];

        foreach ($gears as $g) {
            FishingGear::updateOrCreate(
                ['code' => $g['code']],
                $g
            );
        }
    }
}

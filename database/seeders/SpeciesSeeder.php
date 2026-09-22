<?php

namespace Database\Seeders;

use App\Models\Species;
use Illuminate\Database\Seeder;

class SpeciesSeeder extends Seeder
{
    /**
     * Seed master data jenis ikan / komoditas perikanan:
     * - Ikan Pelagis Besar (Tuna, Cakalang, Tongkol, Tenggiri)
     * - Ikan Pelagis Kecil (Kembung, Layang, Selar, Teri)
     * - Ikan Demersal & Karang (Kerapu, Kakap Merah, Kuwe, Manyung)
     * - Krustasea & Moluska (Lobster, Udang Windu, Cumi-cumi, Gurita)
     *
     * Dilengkapi nama lokal Aceh, nama FAO, nama ilmiah, dan famili taksonomi.
     * Idempoten: aman dijalankan ulang.
     */
    public function run(): void
    {
        $species = [
            // =================================================================
            // 1. PELAGIS BESAR (Komoditas Utama Samudera Hindia & Selat Malaka)
            // =================================================================
            [
                'code' => 'SKJ',
                'local_name_id' => 'Cakalang',
                'local_name' => 'Sisik / Eungkot Jeurubok',
                'scientific_name' => 'Katsuwonus pelamis',
                'english_name' => 'Skipjack tuna',
                'family' => 'Scombridae',
                'fish_group' => 'pelagis_besar',
                'conservation_status' => 'Least Concern (LC)',
                'is_active' => true,
            ],
            [
                'code' => 'YFT',
                'local_name_id' => 'Madidihang / Tuna Sirip Kuning',
                'local_name' => 'Eungkot Asu / Tuna Kuning',
                'scientific_name' => 'Thunnus albacares',
                'english_name' => 'Yellowfin tuna',
                'family' => 'Scombridae',
                'fish_group' => 'pelagis_besar',
                'conservation_status' => 'Near Threatened (NT)',
                'is_active' => true,
            ],
            [
                'code' => 'BET',
                'local_name_id' => 'Tuna Mata Besar',
                'local_name' => 'Tuna Mata Gede',
                'scientific_name' => 'Thunnus obesus',
                'english_name' => 'Bigeye tuna',
                'family' => 'Scombridae',
                'fish_group' => 'pelagis_besar',
                'conservation_status' => 'Vulnerable (VU)',
                'is_active' => true,
            ],
            [
                'code' => 'KAW',
                'local_name_id' => 'Tongkol Komo',
                'local_name' => 'Suree Batu',
                'scientific_name' => 'Euthynnus affinis',
                'english_name' => 'Kawakawa / Eastern little tuna',
                'family' => 'Scombridae',
                'fish_group' => 'pelagis_besar',
                'conservation_status' => 'Least Concern (LC)',
                'is_active' => true,
            ],
            [
                'code' => 'FRI',
                'local_name_id' => 'Tongkol Krai',
                'local_name' => 'Suree Eungkot',
                'scientific_name' => 'Auxis thazard',
                'english_name' => 'Frigate tuna',
                'family' => 'Scombridae',
                'fish_group' => 'pelagis_besar',
                'conservation_status' => 'Least Concern (LC)',
                'is_active' => true,
            ],
            [
                'code' => 'COM',
                'local_name_id' => 'Tenggiri',
                'local_name' => 'Tanggiri',
                'scientific_name' => 'Scomberomorus commerson',
                'english_name' => 'Narrow-barred Spanish mackerel',
                'family' => 'Scombridae',
                'fish_group' => 'pelagis_besar',
                'conservation_status' => 'Near Threatened (NT)',
                'is_active' => true,
            ],
            [
                'code' => 'SFA',
                'local_name_id' => 'Layaran / Ikan Pedang',
                'local_name' => 'Eungkot Todak',
                'scientific_name' => 'Istiophorus platypterus',
                'english_name' => 'Indo-Pacific sailfish',
                'family' => 'Istiophoridae',
                'fish_group' => 'pelagis_besar',
                'conservation_status' => 'Vulnerable (VU)',
                'is_active' => true,
            ],
            [
                'code' => 'DOL',
                'local_name_id' => 'Madidihang / Lemadang',
                'local_name' => 'Eungkot Golok / Dolphin Fish',
                'scientific_name' => 'Coryphaena hippurus',
                'english_name' => 'Common dolphinfish / Mahi-mahi',
                'family' => 'Coryphaenidae',
                'fish_group' => 'pelagis_besar',
                'conservation_status' => 'Least Concern (LC)',
                'is_active' => true,
            ],

            // =================================================================
            // 2. PELAGIS KECIL (Perikanan Rakyat & Pesisir)
            // =================================================================
            [
                'code' => 'RAG',
                'local_name_id' => 'Kembung Lelaki',
                'local_name' => 'Kembong Banjar',
                'scientific_name' => 'Rastrelliger kanagurta',
                'english_name' => 'Indian mackerel',
                'family' => 'Scombridae',
                'fish_group' => 'pelagis_kecil',
                'conservation_status' => 'Least Concern (LC)',
                'is_active' => true,
            ],
            [
                'code' => 'MSD',
                'local_name_id' => 'Layang Deles',
                'local_name' => 'Dapeu',
                'scientific_name' => 'Decapterus macrosoma',
                'english_name' => 'Shortfin scad',
                'family' => 'Carangidae',
                'fish_group' => 'pelagis_kecil',
                'conservation_status' => 'Least Concern (LC)',
                'is_active' => true,
            ],
            [
                'code' => 'YTC',
                'local_name_id' => 'Selar Kuning',
                'local_name' => 'Lolong Kuning',
                'scientific_name' => 'Selaroides leptolepis',
                'english_name' => 'Yellowstripe scad',
                'family' => 'Carangidae',
                'fish_group' => 'pelagis_kecil',
                'conservation_status' => 'Least Concern (LC)',
                'is_active' => true,
            ],
            [
                'code' => 'STO',
                'local_name_id' => 'Teri Nasi',
                'local_name' => 'Eungkot Bili Laut',
                'scientific_name' => 'Stolephorus commersonnii',
                'english_name' => 'Commerson\'s anchovy',
                'family' => 'Engraulidae',
                'fish_group' => 'pelagis_kecil',
                'conservation_status' => 'Least Concern (LC)',
                'is_active' => true,
            ],

            // =================================================================
            // 3. DEMERSAL & IKAN KARANG
            // =================================================================
            [
                'code' => 'MLR',
                'local_name_id' => 'Kakap Merah',
                'local_name' => 'Kakap Mirah',
                'scientific_name' => 'Lutjanus malabaricus',
                'english_name' => 'Malabar blood snapper',
                'family' => 'Lutjanidae',
                'fish_group' => 'demersal',
                'conservation_status' => 'Least Concern (LC)',
                'is_active' => true,
            ],
            [
                'code' => 'GAT',
                'local_name_id' => 'Kuwe / Gerong',
                'local_name' => 'Rambeu',
                'scientific_name' => 'Caranx ignobilis',
                'english_name' => 'Giant trevally',
                'family' => 'Carangidae',
                'fish_group' => 'karang',
                'conservation_status' => 'Least Concern (LC)',
                'is_active' => true,
            ],
            [
                'code' => 'HBF',
                'local_name_id' => 'Kerapu Bebek / Tikus',
                'local_name' => 'Geurupa Bebek',
                'scientific_name' => 'Cromileptes altivelis',
                'english_name' => 'Humpback grouper',
                'family' => 'Serranidae',
                'fish_group' => 'karang',
                'conservation_status' => 'Vulnerable (VU)',
                'is_active' => true,
            ],
            [
                'code' => 'ENU',
                'local_name_id' => 'Kerapu Lumpur',
                'local_name' => 'Geurupa Lumpur / Karang',
                'scientific_name' => 'Epinephelus coioides',
                'english_name' => 'Orange-spotted grouper',
                'family' => 'Serranidae',
                'fish_group' => 'karang',
                'conservation_status' => 'Least Concern (LC)',
                'is_active' => true,
            ],
            [
                'code' => 'BAR',
                'local_name_id' => 'Kakap Putih',
                'local_name' => 'Kakap Puteh / Siakap',
                'scientific_name' => 'Lates calcarifer',
                'english_name' => 'Barramundi',
                'family' => 'Latidae',
                'fish_group' => 'demersal',
                'conservation_status' => 'Least Concern (LC)',
                'is_active' => true,
            ],

            // =================================================================
            // 4. KRUSTASEA & MOLUSKA (Nilai Ekonomis Tinggi di Aceh)
            // =================================================================
            [
                'code' => 'LOB',
                'local_name_id' => 'Lobster Pasir',
                'local_name' => 'Lobster Simeulue',
                'scientific_name' => 'Panulirus homarus',
                'english_name' => 'Scalloped spiny lobster',
                'family' => 'Palinuridae',
                'fish_group' => 'udang_krustasea',
                'conservation_status' => 'Least Concern (LC)',
                'is_active' => true,
            ],
            [
                'code' => 'GIT',
                'local_name_id' => 'Udang Windu',
                'local_name' => 'Udang Pancet Windu',
                'scientific_name' => 'Penaeus monodon',
                'english_name' => 'Giant tiger prawn',
                'family' => 'Penaeidae',
                'fish_group' => 'udang_krustasea',
                'conservation_status' => 'Not Evaluated (NE)',
                'is_active' => true,
            ],
            [
                'code' => 'SQC',
                'local_name_id' => 'Cumi-cumi',
                'local_name' => 'Eungkot Sotong',
                'scientific_name' => 'Loligo duvaucelii',
                'english_name' => 'Indian squid',
                'family' => 'Loliginidae',
                'fish_group' => 'moluska',
                'conservation_status' => 'Least Concern (LC)',
                'is_active' => true,
            ],
            [
                'code' => 'OCT',
                'local_name_id' => 'Gurita Karang',
                'local_name' => 'Gurita Sabang',
                'scientific_name' => 'Octopus cyanea',
                'english_name' => 'Big blue octopus',
                'family' => 'Octopodidae',
                'fish_group' => 'moluska',
                'conservation_status' => 'Least Concern (LC)',
                'is_active' => true,
            ],
        ];

        foreach ($species as $s) {
            Species::updateOrCreate(
                ['code' => $s['fao_code']],
                $s
            );
        }
    }
}

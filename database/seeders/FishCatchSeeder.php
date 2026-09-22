<?php

namespace Database\Seeders;

use App\Models\FishCatch;
use App\Models\FishingEffort;
use App\Models\FishingTrip;
use App\Models\Species;
use Illuminate\Database\Seeder;

class FishCatchSeeder extends Seeder
{
    /**
     * Seed master data hasil tangkapan ikan (Fish Catches) di Aceh.
     * Mencakup target species, bycatch, dan discarded.
     * Idempoten: aman dijalankan berulang kali.
     */
    public function run(): void
    {
        $catches = [
            // --- TRIP-202609-0001: KM Inka Mina 704 (Purse Seine Samudera Hindia) ---
            [
                'trip_number' => 'TRIP-202609-0001',
                'setting_number' => 1,
                'species_code' => 'SKJ', // Cakalang
                'weight_kg' => 3200.00,
                'fish_count' => 1450,
                'catch_status' => 'target',
                'notes' => 'Tangkapan setting 1 di sekitar rumpon laut dalam nomor 04. Ukuran ikan seragam 2.0 - 2.5 kg.',
            ],
            [
                'trip_number' => 'TRIP-202609-0001',
                'setting_number' => 1,
                'species_code' => 'YFT', // Madidihang / Tuna Sirip Kuning
                'weight_kg' => 1450.00,
                'fish_count' => 220,
                'catch_status' => 'target',
                'notes' => 'Madidihang ukuran sedang (grade B) tersimpan di palka es nomor 1.',
            ],
            [
                'trip_number' => 'TRIP-202609-0001',
                'setting_number' => 1,
                'species_code' => 'DOL', // Lemadang / Mahi-mahi
                'weight_kg' => 320.00,
                'fish_count' => 45,
                'catch_status' => 'bycatch',
                'notes' => 'Tangkapan sampingan bernilai ekonomis tinggi dekat pelampung rumpon.',
            ],
            [
                'trip_number' => 'TRIP-202609-0001',
                'setting_number' => 2,
                'species_code' => 'SKJ', // Cakalang
                'weight_kg' => 2800.00,
                'fish_count' => 1280,
                'catch_status' => 'target',
                'notes' => 'Tangkapan setting ke-2. Kondisi ikan segar dipadatkan dengan es curah.',
            ],
            [
                'trip_number' => 'TRIP-202609-0001',
                'setting_number' => 2,
                'species_code' => 'KAW', // Tongkol Komo
                'weight_kg' => 650.00,
                'fish_count' => 520,
                'catch_status' => 'bycatch',
                'notes' => 'Tangkapan sampingan kawanan tongkol komo.',
            ],

            // --- TRIP-202609-0002: KM Lampulo Jaya Mandiri (Purse Seine Pelagis Kecil Pulo Aceh) ---
            [
                'trip_number' => 'TRIP-202609-0002',
                'setting_number' => 1,
                'species_code' => 'FRI', // Tongkol Krai
                'weight_kg' => 1250.00,
                'fish_count' => 1600,
                'catch_status' => 'target',
                'notes' => 'Tongkol krai segar hasil operasi malam hari menggunakan atraksi lampu.',
            ],
            [
                'trip_number' => 'TRIP-202609-0002',
                'setting_number' => 1,
                'species_code' => 'RAB', // Kembung Lelaki
                'weight_kg' => 850.00,
                'fish_count' => 4200,
                'catch_status' => 'target',
                'notes' => 'Kembung lelaki kualitas ekspor konsumsi lokal.',
            ],
            [
                'trip_number' => 'TRIP-202609-0002',
                'setting_number' => 1,
                'species_code' => 'YTL', // Selar Kuning
                'weight_kg' => 450.00,
                'fish_count' => 3100,
                'catch_status' => 'target',
                'notes' => 'Ikan selar segar untuk pasokan pasar pagi Lampulo.',
            ],
            [
                'trip_number' => 'TRIP-202609-0002',
                'setting_number' => 1,
                'species_code' => 'SQL', // Cumi-cumi
                'weight_kg' => 120.00,
                'fish_count' => 600,
                'catch_status' => 'bycatch',
                'notes' => 'Cumi-cumi tertarik lampu atraksi pukat cincin.',
            ],

            // --- TRIP-202609-0003: KM Malaka Rayeuk (Drift Gillnet Selat Malaka) ---
            [
                'trip_number' => 'TRIP-202609-0003',
                'setting_number' => 1,
                'species_code' => 'COM', // Tenggiri
                'weight_kg' => 680.00,
                'fish_count' => 140,
                'catch_status' => 'target',
                'notes' => 'Tenggiri batang ukuran 4-6 kg tertangkap jaring insang hanyut.',
            ],
            [
                'trip_number' => 'TRIP-202609-0003',
                'setting_number' => 1,
                'species_code' => 'KAW', // Tongkol Komo
                'weight_kg' => 340.00,
                'fish_count' => 270,
                'catch_status' => 'bycatch',
                'notes' => 'Tangkapan sampingan gillnet perairan Lhokseumawe.',
            ],

            // --- TRIP-202609-0004: KM Selat Malaka 02 (Purse Seine Teri & Layang Idi) ---
            [
                'trip_number' => 'TRIP-202609-0004',
                'setting_number' => 1,
                'species_code' => 'STE', // Teri
                'weight_kg' => 950.00,
                'fish_count' => null,
                'catch_status' => 'target',
                'notes' => 'Teri nasi segar langsung direbus sebagian di palka olah kapal.',
            ],
            [
                'trip_number' => 'TRIP-202609-0004',
                'setting_number' => 1,
                'species_code' => 'RUS', // Layang
                'weight_kg' => 620.00,
                'fish_count' => 2800,
                'catch_status' => 'target',
                'notes' => 'Ikan layang kualitas super untuk pengasinan dan pasar lokal Idi.',
            ],

            // --- TRIP-202609-0005: KM Meulaboh Bahari (Handline Demersal Pantai Barat) ---
            [
                'trip_number' => 'TRIP-202609-0005',
                'setting_number' => 1,
                'species_code' => 'LUC', // Kakap Merah
                'weight_kg' => 420.00,
                'fish_count' => 95,
                'catch_status' => 'target',
                'notes' => 'Kakap merah karang kedalaman 60 meter. Ukuran 3.5 - 5.0 kg/ekor.',
            ],
            [
                'trip_number' => 'TRIP-202609-0005',
                'setting_number' => 1,
                'species_code' => 'EPI', // Kerapu Lumpur
                'weight_kg' => 280.00,
                'fish_count' => 65,
                'catch_status' => 'target',
                'notes' => 'Kerapu batu/lumpur bernilai ekonomis tinggi untuk restoran.',
            ],
            [
                'trip_number' => 'TRIP-202609-0005',
                'setting_number' => 1,
                'species_code' => 'TRE', // Kuwe / Bubara
                'weight_kg' => 160.00,
                'fish_count' => 30,
                'catch_status' => 'bycatch',
                'notes' => 'Kuwe ukuran besar terpancing handline.',
            ],

            // --- TRIP-202609-0006: KM Pulo Breueh Mandiri (Pancing Tonda & Ulur) ---
            [
                'trip_number' => 'TRIP-202609-0006',
                'setting_number' => 1,
                'species_code' => 'SKJ', // Cakalang
                'weight_kg' => 380.00,
                'fish_count' => 190,
                'catch_status' => 'target',
                'notes' => 'Cakalang pancing tonda perairan Selat Benggala.',
            ],
            [
                'trip_number' => 'TRIP-202609-0006',
                'species_code' => 'KAW', // Tongkol Komo
                'weight_kg' => 290.00,
                'fish_count' => 220,
                'catch_status' => 'target',
                'notes' => 'Tongkol komo hasil pancing ulur sekitar Pulo Nasi.',
            ],

            // --- TRIP-202609-0007: PMT Ulee Lheue Samudera (Handline Pesisir) ---
            [
                'trip_number' => 'TRIP-202609-0007',
                'setting_number' => 1,
                'species_code' => 'TRE', // Kuwe
                'weight_kg' => 85.00,
                'fish_count' => 22,
                'catch_status' => 'target',
                'notes' => 'Hasil melaut harian satu hari nelayan perahu motor tempel.',
            ],
            [
                'trip_number' => 'TRIP-202609-0007',
                'species_code' => 'LUC', // Kakap Merah
                'weight_kg' => 60.00,
                'fish_count' => 16,
                'catch_status' => 'bycatch',
                'notes' => 'Kakap merah dekat terumbu karang Ulee Lheue.',
            ],

            // --- TRIP-202609-0008: KM Sabang Bahari Indah (Tuna Longline Sabang) ---
            [
                'trip_number' => 'TRIP-202609-0008',
                'setting_number' => 1,
                'species_code' => 'YFT', // Madidihang
                'weight_kg' => 850.00,
                'fish_count' => 32,
                'catch_status' => 'target',
                'notes' => 'Tuna sirip kuning kualitas ekspor (grade A sashimi), suhu palka es -2 derajat Celcius.',
            ],
            [
                'trip_number' => 'TRIP-202609-0008',
                'setting_number' => 1,
                'species_code' => 'BET', // Tuna Mata Besar
                'weight_kg' => 620.00,
                'fish_count' => 18,
                'catch_status' => 'target',
                'notes' => 'Tuna mata besar tertangkap rawai hanyut di kedalaman 100 meter.',
            ],
            [
                'trip_number' => 'TRIP-202609-0008',
                'setting_number' => 1,
                'species_code' => 'SWO', // Ikan Pedang
                'weight_kg' => 140.00,
                'fish_count' => 2,
                'catch_status' => 'bycatch',
                'notes' => 'Tangkapan sampingan rawai tuna samudera lepas.',
            ],

            // --- TRIP-202609-0009: KM Inka Mina 704 (Trip Berjalan) ---
            [
                'trip_number' => 'TRIP-202609-0009',
                'setting_number' => 1,
                'species_code' => 'SKJ', // Cakalang
                'weight_kg' => 1850.00,
                'fish_count' => 820,
                'catch_status' => 'target',
                'notes' => 'Hasil setting 1 trip berjalan di ZEEI Samudera Hindia. Palka 1 mulai terisi.',
            ],
            [
                'trip_number' => 'TRIP-202609-0009',
                'setting_number' => 1,
                'species_code' => 'YFT', // Madidihang
                'weight_kg' => 750.00,
                'fish_count' => 110,
                'catch_status' => 'target',
                'notes' => 'Madidihang tuna muda (baby tuna) tertangkap bersama cakalang.',
            ],
        ];

        foreach ($catches as $item) {
            $trip = FishingTrip::where('trip_number', $item['trip_number'])->first();
            $species = Species::where('fao_code', $item['species_code'])->first();

            if (! $trip || ! $species) {
                continue;
            }

            $effortId = null;
            if (isset($item['setting_number'])) {
                $effort = FishingEffort::where('fishing_trip_id', $trip->id)
                    ->where('setting_number', $item['setting_number'])
                    ->first();
                if ($effort) {
                    $effortId = $effort->id;
                }
            }

            FishCatch::firstOrCreate(
                [
                    'fishing_trip_id' => $trip->id,
                    'fish_species_id' => $species->id,
                    'fishing_effort_id' => $effortId,
                    'catch_status' => $item['catch_status'],
                ],
                [
                    'weight_kg' => $item['weight_kg'],
                    'fish_count' => $item['fish_count'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]
            );
        }
    }
}

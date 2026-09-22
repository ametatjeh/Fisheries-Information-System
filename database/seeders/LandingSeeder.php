<?php

namespace Database\Seeders;

use App\Models\FishingTrip;
use App\Models\Landing;
use App\Models\LandingItem;
use App\Models\LandingSite;
use App\Models\Species;
use App\Models\User;
use Illuminate\Database\Seeder;

class LandingSeeder extends Seeder
{
    /**
     * Seed master data transaksi pendaratan dan pembongkaran ikan (Fish Landings).
     * Idempoten: aman dijalankan berulang kali.
     */
    public function run(): void
    {
        $admin = User::first();

        $landingsData = [
            // 1. Trip 1 - KM Inka Mina 704 di PPS Lampulo
            [
                'landing_number' => 'LND-202608-0001',
                'trip_number' => 'TRIP-202609-0001',
                'landing_site_code' => 'PPS-LMP',
                'landing_date' => '2026-08-30 16:30:00',
                'buyer_count' => 14,
                'notes' => 'Pembongkaran palka 1 dan 2 selesai pukul 18.00 WIB. Mutu ikan cakalang dan tuna sangat baik (Grade A & B). Seluruh ikan diserap pedagang lokal dan pabrik pengolahan Lampulo.',
                'items' => [
                    ['species_code' => 'SKJ', 'weight_kg' => 3200.00, 'fish_count' => 1450, 'price_per_kg' => 22000.00, 'quality_grade' => 'A'],
                    ['species_code' => 'YFT', 'weight_kg' => 1450.00, 'fish_count' => 220,  'price_per_kg' => 55000.00, 'quality_grade' => 'B'],
                    ['species_code' => 'DOL', 'weight_kg' => 320.00,  'fish_count' => 45,   'price_per_kg' => 30000.00, 'quality_grade' => 'A'],
                ],
            ],

            // 2. Trip 2 - KM Lampulo Jaya Mandiri di PPS Lampulo
            [
                'landing_number' => 'LND-202609-0002',
                'trip_number' => 'TRIP-202609-0002',
                'landing_site_code' => 'PPS-LMP',
                'landing_date' => '2026-09-03 18:00:00',
                'buyer_count' => 18,
                'notes' => 'Pelelangan ikan pelagis kecil sore hari di dermaga TPI Lampulo. Pembeli pedagang pasar Peunayong dan Ulee Kareng.',
                'items' => [
                    ['species_code' => 'FRI', 'weight_kg' => 1250.00, 'fish_count' => 1600, 'price_per_kg' => 24000.00, 'quality_grade' => 'A'],
                    ['species_code' => 'RAB', 'weight_kg' => 850.00,  'fish_count' => 4200, 'price_per_kg' => 32000.00, 'quality_grade' => 'A'],
                    ['species_code' => 'YTL', 'weight_kg' => 450.00,  'fish_count' => 3100, 'price_per_kg' => 28000.00, 'quality_grade' => 'B'],
                    ['species_code' => 'SQL', 'weight_kg' => 120.00,  'fish_count' => 600,  'price_per_kg' => 55000.00, 'quality_grade' => 'A'],
                ],
            ],

            // 3. Trip 3 - KM Malaka Rayeuk di PPI Pusong Lhokseumawe
            [
                'landing_number' => 'LND-202609-0003',
                'trip_number' => 'TRIP-202609-0003',
                'landing_site_code' => 'PPI-PSG',
                'landing_date' => '2026-09-04 16:00:00',
                'buyer_count' => 8,
                'notes' => 'Pendaratan tenggiri batang kualitas prima langsung dilelang ke pedagang ikan antar kota Lhokseumawe dan Medan.',
                'items' => [
                    ['species_code' => 'COM', 'weight_kg' => 680.00, 'fish_count' => 140, 'price_per_kg' => 68000.00, 'quality_grade' => 'A'],
                    ['species_code' => 'KAW', 'weight_kg' => 340.00, 'fish_count' => 270, 'price_per_kg' => 24000.00, 'quality_grade' => 'B'],
                ],
            ],

            // 4. Trip 4 - KM Selat Malaka 02 di PPN Idi Aceh Timur
            [
                'landing_number' => 'LND-202609-0004',
                'trip_number' => 'TRIP-202609-0004',
                'landing_site_code' => 'PPN-IDI',
                'landing_date' => '2026-09-04 17:30:00',
                'buyer_count' => 12,
                'notes' => 'Pendaratan hasil tangkapan teri dan layang. Teri rebus langsung dikirim ke sentra pengeringan ikan asin Kuala Idi.',
                'items' => [
                    ['species_code' => 'STE', 'weight_kg' => 950.00, 'fish_count' => null, 'price_per_kg' => 35000.00, 'quality_grade' => 'A'],
                    ['species_code' => 'RUS', 'weight_kg' => 620.00, 'fish_count' => 2800, 'price_per_kg' => 20000.00, 'quality_grade' => 'B'],
                ],
            ],

            // 5. Trip 5 - KM Meulaboh Bahari di PPI Ujong Baroh Meulaboh
            [
                'landing_number' => 'LND-202609-0005',
                'trip_number' => 'TRIP-202609-0005',
                'landing_site_code' => 'PPI-MBO',
                'landing_date' => '2026-09-06 16:00:00',
                'buyer_count' => 6,
                'notes' => 'Ikan karang dan demersal segar (kakap merah & kerapu batu). Diborong pengusaha restoran seafood Meulaboh dan Banda Aceh.',
                'items' => [
                    ['species_code' => 'LUC', 'weight_kg' => 420.00, 'fish_count' => 95, 'price_per_kg' => 75000.00, 'quality_grade' => 'A'],
                    ['species_code' => 'EPI', 'weight_kg' => 280.00, 'fish_count' => 65, 'price_per_kg' => 85000.00, 'quality_grade' => 'A'],
                    ['species_code' => 'TRE', 'weight_kg' => 160.00, 'fish_count' => 30, 'price_per_kg' => 37000.00, 'quality_grade' => 'B'],
                ],
            ],

            // 6. Trip 6 - KM Pulo Breueh Mandiri di PPI Ulee Lheue
            [
                'landing_number' => 'LND-202609-0006',
                'trip_number' => 'TRIP-202609-0006',
                'landing_site_code' => 'PPI-ULH',
                'landing_date' => '2026-09-05 17:00:00',
                'buyer_count' => 5,
                'notes' => 'Pendaratan sore di PPI Ulee Lheue dari nelayan kepulauan Pulo Aceh.',
                'items' => [
                    ['species_code' => 'SKJ', 'weight_kg' => 380.00, 'fish_count' => 190, 'price_per_kg' => 25000.00, 'quality_grade' => 'B'],
                    ['species_code' => 'KAW', 'weight_kg' => 290.00, 'fish_count' => 220, 'price_per_kg' => 24000.00, 'quality_grade' => 'B'],
                ],
            ],

            // 7. Trip 7 - PMT Ulee Lheue Samudera di PPI Ulee Lheue (One-Day Trip)
            [
                'landing_number' => 'LND-202609-0007',
                'trip_number' => 'TRIP-202609-0007',
                'landing_site_code' => 'PPI-ULH',
                'landing_date' => '2026-09-06 17:15:00',
                'buyer_count' => 3,
                'notes' => 'Hasil one-day trip pancing ulur langsung dibeli konsumen lokal pantai Ulee Lheue.',
                'items' => [
                    ['species_code' => 'TRE', 'weight_kg' => 85.00, 'fish_count' => 22, 'price_per_kg' => 45000.00, 'quality_grade' => 'A'],
                    ['species_code' => 'LUC', 'weight_kg' => 60.00, 'fish_count' => 16, 'price_per_kg' => 80000.00, 'quality_grade' => 'A'],
                ],
            ],

            // 8. Trip 8 - KM Sabang Bahari Indah di PPI Ie Meulee Sabang
            [
                'landing_number' => 'LND-202609-0008',
                'trip_number' => 'TRIP-202609-0008',
                'landing_site_code' => 'PPI-SBG',
                'landing_date' => '2026-09-06 19:30:00',
                'buyer_count' => 4,
                'notes' => 'Pendaratan tuna samudera kualitas ekspor (sashimi grade). Ikan langsung dimasukkan ke cold storage untuk penerbangan kargo transit Kualanamu.',
                'items' => [
                    ['species_code' => 'YFT', 'weight_kg' => 850.00, 'fish_count' => 32, 'price_per_kg' => 85000.00, 'quality_grade' => 'A'],
                    ['species_code' => 'BET', 'weight_kg' => 620.00, 'fish_count' => 18, 'price_per_kg' => 65000.00, 'quality_grade' => 'A'],
                    ['species_code' => 'SWO', 'weight_kg' => 140.00, 'fish_count' => 2,  'price_per_kg' => 45000.00, 'quality_grade' => 'B'],
                ],
            ],
        ];

        foreach ($landingsData as $data) {
            $trip = FishingTrip::where('trip_number', $data['trip_number'])->first();
            $site = LandingSite::where('fao_code', $data['landing_site_code'])->first();

            if (! $site) {
                continue;
            }

            // Hitung akumulasi berat dan nilai dari items
            $totalWeight = 0;
            $totalValue = 0;

            foreach ($data['items'] as $it) {
                $totalWeight += $it['weight_kg'];
                $totalValue += ($it['weight_kg'] * $it['price_per_kg']);
            }

            $landing = Landing::firstOrCreate(
                ['landing_number' => $data['landing_number']],
                [
                    'fishing_trip_id' => $trip ? $trip->id : null,
                    'landing_site_id' => $site->id,
                    'landing_date' => $data['landing_date'],
                    'recorded_by' => $admin ? $admin->id : null,
                    'total_weight_kg' => $totalWeight,
                    'total_value_rp' => $totalValue,
                    'buyer_count' => $data['buyer_count'],
                    'notes' => $data['notes'],
                ]
            );

            // Simpan rincian komoditas ikan
            foreach ($data['items'] as $itemData) {
                $species = Species::where('fao_code', $itemData['species_code'])->first();
                if (! $species) {
                    continue;
                }

                $totalPrice = $itemData['weight_kg'] * $itemData['price_per_kg'];

                LandingItem::firstOrCreate(
                    [
                        'landing_id' => $landing->id,
                        'fish_species_id' => $species->id,
                    ],
                    [
                        'weight_kg' => $itemData['weight_kg'],
                        'fish_count' => $itemData['fish_count'],
                        'price_per_kg' => $itemData['price_per_kg'],
                        'total_price' => $totalPrice,
                        'quality_grade' => $itemData['quality_grade'],
                    ]
                );
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\CatchEstimation;
use App\Models\FishingGear;
use App\Models\LandingSite;
use App\Models\MonthlyProductionStatistic;
use App\Models\Regency;
use App\Models\Species;
use Illuminate\Database\Seeder;

class ProductionStatisticSeeder extends Seeder
{
    /**
     * Seed monthly production statistics and catch estimations.
     */
    public function run(): void
    {
        // Ambil wilayah
        $bandaAceh = Regency::where('name', 'like', '%Banda Aceh%')->first();
        $acehTimur = Regency::where('name', 'like', '%Aceh Timur%')->first();
        $lhokseumawe = Regency::where('name', 'like', '%Lhokseumawe%')->first();
        $acehBarat = Regency::where('name', 'like', '%Aceh Barat%')->first();
        $sabang = Regency::where('name', 'like', '%Sabang%')->first();

        $defaultRegencyId = $bandaAceh ? $bandaAceh->id : 1;

        // Ambil lokasi pendaratan
        $lampulo = LandingSite::where('fao_code', 'LND-ACH-001')->orWhere('name', 'like', '%Lampulo%')->first();
        $pusong = LandingSite::where('fao_code', 'LND-ACH-002')->orWhere('name', 'like', '%Pusong%')->first();
        $idi = LandingSite::where('fao_code', 'LND-ACH-003')->orWhere('name', 'like', '%Idi%')->first();
        $meulaboh = LandingSite::where('fao_code', 'LND-ACH-004')->orWhere('name', 'like', '%Meulaboh%')->orWhere('name', 'like', '%Ujong Baroh%')->first();
        $sabangSite = LandingSite::where('fao_code', 'LND-ACH-007')->orWhere('name', 'like', '%Sabang%')->orWhere('name', 'like', '%Ie Meulee%')->first();

        // Ambil alat tangkap
        $gearPS = FishingGear::where('fao_code', 'like', '%PS%')->orWhere('name', 'like', '%Pukat Cincin%')->first();
        $gearGN = FishingGear::where('fao_code', 'like', '%GN%')->orWhere('name', 'like', '%Insang%')->first();
        $gearLL = FishingGear::where('fao_code', 'like', '%LL%')->orWhere('name', 'like', '%Rawai%')->first();
        $gearHL = FishingGear::where('fao_code', 'like', '%HL%')->orWhere('name', 'like', '%Pancing Ulur%')->first();

        // Ambil spesies ikan
        $skj = Species::where('fao_code', 'SKJ')->orWhere('local_name_id', 'like', '%Cakalang%')->first();
        $yft = Species::where('fao_code', 'YFT')->orWhere('local_name_id', 'like', '%Madidihang%')->first();
        $kaw = Species::where('fao_code', 'KAW')->orWhere('local_name_id', 'like', '%Tongkol%')->first();
        $com = Species::where('fao_code', 'COM')->orWhere('local_name_id', 'like', '%Tenggiri%')->first();
        $lja = Species::where('fao_code', 'LJA')->orWhere('local_name_id', 'like', '%Kakap Merah%')->first();

        $year = 2026;

        // 1. Data Statistik Produksi Bulanan (Monthly Production Statistics)
        // Data dari bulan Januari - September 2026
        $monthlyStatsData = [
            // PPS Lampulo (Banda Aceh)
            [
                'regency_id' => $bandaAceh ? $bandaAceh->id : $defaultRegencyId,
                'landing_site_id' => $lampulo ? $lampulo->id : 1,
                'fish_species_id' => $skj ? $skj->id : 1,
                'fishing_gear_id' => $gearPS ? $gearPS->id : 1,
                'year' => $year,
                'month' => 8,
                'total_volume_kg' => 48500.00,
                'total_value_rp' => 1212500000.00,
                'average_price_per_kg' => 25000.00,
                'total_active_vessels' => 24,
                'total_trips' => 36,
            ],
            [
                'regency_id' => $bandaAceh ? $bandaAceh->id : $defaultRegencyId,
                'landing_site_id' => $lampulo ? $lampulo->id : 1,
                'fish_species_id' => $yft ? $yft->id : 2,
                'fishing_gear_id' => $gearPS ? $gearPS->id : 1,
                'year' => $year,
                'month' => 8,
                'total_volume_kg' => 32400.00,
                'total_value_rp' => 1620000000.00,
                'average_price_per_kg' => 50000.00,
                'total_active_vessels' => 20,
                'total_trips' => 30,
            ],
            [
                'regency_id' => $bandaAceh ? $bandaAceh->id : $defaultRegencyId,
                'landing_site_id' => $lampulo ? $lampulo->id : 1,
                'fish_species_id' => $skj ? $skj->id : 1,
                'fishing_gear_id' => $gearPS ? $gearPS->id : 1,
                'year' => $year,
                'month' => 9,
                'total_volume_kg' => 54200.00,
                'total_value_rp' => 1463400000.00,
                'average_price_per_kg' => 27000.00,
                'total_active_vessels' => 26,
                'total_trips' => 41,
            ],
            [
                'regency_id' => $bandaAceh ? $bandaAceh->id : $defaultRegencyId,
                'landing_site_id' => $lampulo ? $lampulo->id : 1,
                'fish_species_id' => $kaw ? $kaw->id : 3,
                'fishing_gear_id' => $gearGN ? $gearGN->id : 2,
                'year' => $year,
                'month' => 9,
                'total_volume_kg' => 28700.00,
                'total_value_rp' => 574000000.00,
                'average_price_per_kg' => 20000.00,
                'total_active_vessels' => 18,
                'total_trips' => 34,
            ],

            // PPN Idi (Aceh Timur)
            [
                'regency_id' => $acehTimur ? $acehTimur->id : $defaultRegencyId,
                'landing_site_id' => $idi ? $idi->id : 1,
                'fish_species_id' => $kaw ? $kaw->id : 3,
                'fishing_gear_id' => $gearPS ? $gearPS->id : 1,
                'year' => $year,
                'month' => 8,
                'total_volume_kg' => 41200.00,
                'total_value_rp' => 865200000.00,
                'average_price_per_kg' => 21000.00,
                'total_active_vessels' => 22,
                'total_trips' => 38,
            ],
            [
                'regency_id' => $acehTimur ? $acehTimur->id : $defaultRegencyId,
                'landing_site_id' => $idi ? $idi->id : 1,
                'fish_species_id' => $com ? $com->id : 4,
                'fishing_gear_id' => $gearGN ? $gearGN->id : 2,
                'year' => $year,
                'month' => 9,
                'total_volume_kg' => 19800.00,
                'total_value_rp' => 1089000000.00,
                'average_price_per_kg' => 55000.00,
                'total_active_vessels' => 15,
                'total_trips' => 28,
            ],

            // PPI Pusong (Kota Lhokseumawe)
            [
                'regency_id' => $lhokseumawe ? $lhokseumawe->id : $defaultRegencyId,
                'landing_site_id' => $pusong ? $pusong->id : 1,
                'fish_species_id' => $com ? $com->id : 4,
                'fishing_gear_id' => $gearGN ? $gearGN->id : 2,
                'year' => $year,
                'month' => 8,
                'total_volume_kg' => 16500.00,
                'total_value_rp' => 874500000.00,
                'average_price_per_kg' => 53000.00,
                'total_active_vessels' => 14,
                'total_trips' => 25,
            ],
            [
                'regency_id' => $lhokseumawe ? $lhokseumawe->id : $defaultRegencyId,
                'landing_site_id' => $pusong ? $pusong->id : 1,
                'fish_species_id' => $kaw ? $kaw->id : 3,
                'fishing_gear_id' => $gearPS ? $gearPS->id : 1,
                'year' => $year,
                'month' => 9,
                'total_volume_kg' => 22400.00,
                'total_value_rp' => 470400000.00,
                'average_price_per_kg' => 21000.00,
                'total_active_vessels' => 16,
                'total_trips' => 29,
            ],

            // PPI Ujong Baroh (Aceh Barat / Meulaboh)
            [
                'regency_id' => $acehBarat ? $acehBarat->id : $defaultRegencyId,
                'landing_site_id' => $meulaboh ? $meulaboh->id : 1,
                'fish_species_id' => $lja ? $lja->id : 5,
                'fishing_gear_id' => $gearHL ? $gearHL->id : 3,
                'year' => $year,
                'month' => 8,
                'total_volume_kg' => 14200.00,
                'total_value_rp' => 994000000.00,
                'average_price_per_kg' => 70000.00,
                'total_active_vessels' => 19,
                'total_trips' => 32,
            ],
            [
                'regency_id' => $acehBarat ? $acehBarat->id : $defaultRegencyId,
                'landing_site_id' => $meulaboh ? $meulaboh->id : 1,
                'fish_species_id' => $lja ? $lja->id : 5,
                'fishing_gear_id' => $gearHL ? $gearHL->id : 3,
                'year' => $year,
                'month' => 9,
                'total_volume_kg' => 15800.00,
                'total_value_rp' => 1137600000.00,
                'average_price_per_kg' => 72000.00,
                'total_active_vessels' => 21,
                'total_trips' => 35,
            ],

            // Pelabuhan Ie Meulee (Kota Sabang)
            [
                'regency_id' => $sabang ? $sabang->id : $defaultRegencyId,
                'landing_site_id' => $sabangSite ? $sabangSite->id : 1,
                'fish_species_id' => $yft ? $yft->id : 2,
                'fishing_gear_id' => $gearLL ? $gearLL->id : 3,
                'year' => $year,
                'month' => 8,
                'total_volume_kg' => 21500.00,
                'total_value_rp' => 1612500000.00,
                'average_price_per_kg' => 75000.00,
                'total_active_vessels' => 12,
                'total_trips' => 18,
            ],
            [
                'regency_id' => $sabang ? $sabang->id : $defaultRegencyId,
                'landing_site_id' => $sabangSite ? $sabangSite->id : 1,
                'fish_species_id' => $yft ? $yft->id : 2,
                'fishing_gear_id' => $gearLL ? $gearLL->id : 3,
                'year' => $year,
                'month' => 9,
                'total_volume_kg' => 25800.00,
                'total_value_rp' => 1986600000.00,
                'average_price_per_kg' => 77000.00,
                'total_active_vessels' => 14,
                'total_trips' => 22,
            ],
        ];

        foreach ($monthlyStatsData as $stat) {
            MonthlyProductionStatistic::firstOrCreate(
                [
                    'regency_id' => $stat['regency_id'],
                    'landing_site_id' => $stat['landing_site_id'],
                    'fish_species_id' => $stat['fish_species_id'],
                    'fishing_gear_id' => $stat['fishing_gear_id'],
                    'year' => $stat['year'],
                    'month' => $stat['month'],
                ],
                $stat
            );
        }

        // 2. Data Estimasi Tangkapan & CPUE (Catch Estimations)
        $estimationsData = [
            [
                'regency_id' => $bandaAceh ? $bandaAceh->id : $defaultRegencyId,
                'landing_site_id' => $lampulo ? $lampulo->id : 1,
                'fish_species_id' => $skj ? $skj->id : 1,
                'fishing_gear_id' => $gearPS ? $gearPS->id : 1,
                'year' => $year,
                'month' => 8,
                'sampled_catch_kg' => 28500.00,
                'raising_factor' => 1.7018,
                'estimated_catch_kg' => 48500.00,
                'estimated_effort_trips' => 36,
                'cpue' => 1347.2222, // kg/trip
                'variance' => 12450.25,
                'notes' => 'Estimasi perikanan pukat cincin pelagis besar PPS Lampulo berbasis sampel survei harian.',
            ],
            [
                'regency_id' => $bandaAceh ? $bandaAceh->id : $defaultRegencyId,
                'landing_site_id' => $lampulo ? $lampulo->id : 1,
                'fish_species_id' => $yft ? $yft->id : 2,
                'fishing_gear_id' => $gearPS ? $gearPS->id : 1,
                'year' => $year,
                'month' => 8,
                'sampled_catch_kg' => 19800.00,
                'raising_factor' => 1.6364,
                'estimated_catch_kg' => 32400.00,
                'estimated_effort_trips' => 30,
                'cpue' => 1080.0000,
                'variance' => 9820.50,
                'notes' => 'Ekstrapolasi hasil tangkapan madidihang pukat cincin WPPNRI 572.',
            ],
            [
                'regency_id' => $bandaAceh ? $bandaAceh->id : $defaultRegencyId,
                'landing_site_id' => $lampulo ? $lampulo->id : 1,
                'fish_species_id' => $skj ? $skj->id : 1,
                'fishing_gear_id' => $gearPS ? $gearPS->id : 1,
                'year' => $year,
                'month' => 9,
                'sampled_catch_kg' => 33200.00,
                'raising_factor' => 1.6325,
                'estimated_catch_kg' => 54200.00,
                'estimated_effort_trips' => 41,
                'cpue' => 1321.9512,
                'variance' => 14200.00,
                'notes' => 'Peningkatan kelimpahan cakalang seiring musim tangkap barat daya.',
            ],
            [
                'regency_id' => $acehTimur ? $acehTimur->id : $defaultRegencyId,
                'landing_site_id' => $idi ? $idi->id : 1,
                'fish_species_id' => $kaw ? $kaw->id : 3,
                'fishing_gear_id' => $gearPS ? $gearPS->id : 1,
                'year' => $year,
                'month' => 9,
                'sampled_catch_kg' => 24500.00,
                'raising_factor' => 1.6816,
                'estimated_catch_kg' => 41200.00,
                'estimated_effort_trips' => 38,
                'cpue' => 1084.2105,
                'variance' => 11300.00,
                'notes' => 'Perikanan pelagis kecil Selat Malaka WPPNRI 571.',
            ],
            [
                'regency_id' => $sabang ? $sabang->id : $defaultRegencyId,
                'landing_site_id' => $sabangSite ? $sabangSite->id : 1,
                'fish_species_id' => $yft ? $yft->id : 2,
                'fishing_gear_id' => $gearLL ? $gearLL->id : 3,
                'year' => $year,
                'month' => 9,
                'sampled_catch_kg' => 16800.00,
                'raising_factor' => 1.5357,
                'estimated_catch_kg' => 25800.00,
                'estimated_effort_trips' => 22,
                'cpue' => 1172.7273,
                'variance' => 8500.00,
                'notes' => 'Rawai tuna Samudera Hindia barat Pulau Weh.',
            ],
            [
                'regency_id' => $acehBarat ? $acehBarat->id : $defaultRegencyId,
                'landing_site_id' => $meulaboh ? $meulaboh->id : 1,
                'fish_species_id' => $lja ? $lja->id : 5,
                'fishing_gear_id' => $gearHL ? $gearHL->id : 3,
                'year' => $year,
                'month' => 9,
                'sampled_catch_kg' => 9800.00,
                'raising_factor' => 1.6122,
                'estimated_catch_kg' => 15800.00,
                'estimated_effort_trips' => 35,
                'cpue' => 451.4286,
                'variance' => 3200.00,
                'notes' => 'Perikanan demersal karang pancing ulur Pantai Barat Aceh.',
            ],
        ];

        foreach ($estimationsData as $est) {
            CatchEstimation::firstOrCreate(
                [
                    'regency_id' => $est['regency_id'],
                    'landing_site_id' => $est['landing_site_id'],
                    'fish_species_id' => $est['fish_species_id'],
                    'fishing_gear_id' => $est['fishing_gear_id'],
                    'year' => $est['year'],
                    'month' => $est['month'],
                ],
                $est
            );
        }
    }
}

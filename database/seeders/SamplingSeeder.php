<?php

namespace Database\Seeders;

use App\Models\BiologicalMeasurement;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\Sample;
use App\Models\SamplingPlan;
use App\Models\Species;
use App\Models\User;
use Illuminate\Database\Seeder;

class SamplingSeeder extends Seeder
{
    /**
     * Seed biological sampling plans, sample batches, and morphometric measurements.
     */
    public function run(): void
    {
        $admin = User::first();
        $adminId = $admin ? $admin->id : 1;

        // Ambil ID landing sites
        $lampuloSite = LandingSite::where('fao_code', 'LND-ACH-001')->orWhere('name', 'like', '%Lampulo%')->first();
        $pusongSite = LandingSite::where('fao_code', 'LND-ACH-002')->orWhere('name', 'like', '%Pusong%')->first();
        $idiSite = LandingSite::where('fao_code', 'LND-ACH-003')->orWhere('name', 'like', '%Idi%')->first();
        $meulabohSite = LandingSite::where('fao_code', 'LND-ACH-004')->orWhere('name', 'like', '%Meulaboh%')->orWhere('name', 'like', '%Ujong Baroh%')->first();
        $sabangSite = LandingSite::where('fao_code', 'LND-ACH-007')->orWhere('name', 'like', '%Sabang%')->orWhere('name', 'like', '%Ie Meulee%')->first();

        // Ambil ID spesies ikan
        $skjSpecies = Species::where('fao_code', 'SKJ')->orWhere('local_name_id', 'like', '%Cakalang%')->first();
        $yftSpecies = Species::where('fao_code', 'YFT')->orWhere('local_name_id', 'like', '%Madidihang%')->first();
        $kawSpecies = Species::where('fao_code', 'KAW')->orWhere('local_name_id', 'like', '%Tongkol%')->first();
        $ljaSpecies = Species::where('fao_code', 'LJA')->orWhere('local_name_id', 'like', '%Kakap Merah%')->first();
        $cotSpecies = Species::where('fao_code', 'COT')->orWhere('local_name_id', 'like', '%Kerapu%')->first();
        $comSpecies = Species::where('fao_code', 'COM')->orWhere('local_name_id', 'like', '%Tenggiri%')->first();

        // Ambil trip penangkapan
        $trip1 = FishingTrip::where('trip_number', 'TRIP-202608-0001')->first();
        $trip2 = FishingTrip::where('trip_number', 'TRIP-202609-0002')->first();
        $trip3 = FishingTrip::where('trip_number', 'TRIP-202609-0003')->first();
        $trip4 = FishingTrip::where('trip_number', 'TRIP-202609-0004')->first();
        $trip5 = FishingTrip::where('trip_number', 'TRIP-202609-0005')->first();
        $trip8 = FishingTrip::where('trip_number', 'TRIP-202609-0008')->first();

        // 1. Rencana Program Sampling (Sampling Plans)
        $plansData = [
            [
                'code' => 'SMP-PLAN-2026-001',
                'title' => 'Program Pemantauan Biologi Perikanan Pelagis Besar PPS Lampulo',
                'landing_site_id' => $lampuloSite ? $lampuloSite->id : 1,
                'target_species_id' => $skjSpecies ? $skjSpecies->id : null,
                'start_date' => '2026-08-01',
                'end_date' => '2026-10-31',
                'target_sample_size' => 300,
                'sampling_method' => 'stratified',
                'status' => 'active',
                'notes' => 'Program pendugaan struktur ukuran panjang dan tingkat kematangan gonad ikan cakalang & madidihang di WPPNRI 572.',
            ],
            [
                'code' => 'SMP-PLAN-2026-002',
                'title' => 'Kajian Struktur Ukuran & Kematangan Madidihang (YFT) Sabang',
                'landing_site_id' => $sabangSite ? $sabangSite->id : ($lampuloSite ? $lampuloSite->id : 1),
                'target_species_id' => $yftSpecies ? $yftSpecies->id : null,
                'start_date' => '2026-08-15',
                'end_date' => '2026-11-30',
                'target_sample_size' => 150,
                'sampling_method' => 'systematic',
                'status' => 'active',
                'notes' => 'Monitoring proporsi tuna madidihang layak tangkap (Lm50) perikanan pancing ulur dan rawai tuna Samudera Hindia.',
            ],
            [
                'code' => 'SMP-PLAN-2026-003',
                'title' => 'Pemantauan Biologi Ikan Demersal (Kakap & Kerapu) Pantai Barat Aceh',
                'landing_site_id' => $meulabohSite ? $meulabohSite->id : ($lampuloSite ? $lampuloSite->id : 1),
                'target_species_id' => $ljaSpecies ? $ljaSpecies->id : null,
                'start_date' => '2026-07-01',
                'end_date' => '2026-09-30',
                'target_sample_size' => 200,
                'sampling_method' => 'stratified',
                'status' => 'completed',
                'notes' => 'Evaluasi potensi stok ikan karang dan demersal di perairan Aceh Barat dan Nagan Raya pasca musim barat.',
            ],
            [
                'code' => 'SMP-PLAN-2026-004',
                'title' => 'Survei Biometri Ikan Pelagis Kecil (Tongkol & Kembung) PPN Idi',
                'landing_site_id' => $idiSite ? $idiSite->id : ($lampuloSite ? $lampuloSite->id : 1),
                'target_species_id' => $kawSpecies ? $kawSpecies->id : null,
                'start_date' => '2026-09-01',
                'end_date' => '2026-12-31',
                'target_sample_size' => 250,
                'sampling_method' => 'random',
                'status' => 'active',
                'notes' => 'Pendataan biologi perikanan pukat cincin pelagis kecil di Selat Malaka (WPPNRI 571).',
            ],
        ];

        $createdPlans = [];
        foreach ($plansData as $plan) {
            $createdPlans[$plan['fao_code']] = SamplingPlan::firstOrCreate(
                ['code' => $plan['fao_code']],
                $plan
            );
        }

        // 2. Batch Pengambilan Sampel (Samples)
        $samplesData = [
            [
                'sample_code' => 'SMP-202608-0001',
                'sampling_plan_id' => $createdPlans['SMP-PLAN-2026-001']->id,
                'fishing_trip_id' => $trip1 ? $trip1->id : null,
                'landing_site_id' => $lampuloSite ? $lampuloSite->id : 1,
                'enumerator_id' => $adminId,
                'sample_date' => '2026-08-15',
                'notes' => 'Sub-sampel palka 1 KM Inka Mina 704. Ikan dalam kondisi segar es curah grade A.',
            ],
            [
                'sample_code' => 'SMP-202609-0002',
                'sampling_plan_id' => $createdPlans['SMP-PLAN-2026-001']->id,
                'fishing_trip_id' => $trip2 ? $trip2->id : null,
                'landing_site_id' => $lampuloSite ? $lampuloSite->id : 1,
                'enumerator_id' => $adminId,
                'sample_date' => '2026-09-03',
                'notes' => 'Sampel keranjang kedua hasil tangkapan pukat cincin KM Lampulo Jaya Mandiri.',
            ],
            [
                'sample_code' => 'SMP-202609-0003',
                'sampling_plan_id' => $createdPlans['SMP-PLAN-2026-002']->id,
                'fishing_trip_id' => $trip8 ? $trip8->id : null,
                'landing_site_id' => $sabangSite ? $sabangSite->id : 1,
                'enumerator_id' => $adminId,
                'sample_date' => '2026-09-06',
                'notes' => 'Sampel tuna madidihang hasil pancing rawai KM Sabang Bahari Indah di perairan barat Weh.',
            ],
            [
                'sample_code' => 'SMP-202609-0004',
                'sampling_plan_id' => $createdPlans['SMP-PLAN-2026-003']->id,
                'fishing_trip_id' => $trip5 ? $trip5->id : null,
                'landing_site_id' => $meulabohSite ? $meulabohSite->id : 1,
                'enumerator_id' => $adminId,
                'sample_date' => '2026-09-05',
                'notes' => 'Sampel kakap merah dan kerapu dari trip rawai dasar KM Meulaboh Bahari.',
            ],
            [
                'sample_code' => 'SMP-202609-0005',
                'sampling_plan_id' => $createdPlans['SMP-PLAN-2026-004']->id,
                'fishing_trip_id' => $trip4 ? $trip4->id : null,
                'landing_site_id' => $idiSite ? $idiSite->id : 1,
                'enumerator_id' => $adminId,
                'sample_date' => '2026-09-04',
                'notes' => 'Sampel tongkol krai dan komo hasil purse seine KM Selat Malaka 02.',
            ],
            [
                'sample_code' => 'SMP-202609-0006',
                'sampling_plan_id' => $createdPlans['SMP-PLAN-2026-001']->id,
                'fishing_trip_id' => $trip1 ? $trip1->id : null,
                'landing_site_id' => $lampuloSite ? $lampuloSite->id : 1,
                'enumerator_id' => $adminId,
                'sample_date' => '2026-09-07',
                'notes' => 'Sampel ulangan morfometri cakalang pendaratan dermaga 2 PPS Lampulo.',
            ],
        ];

        // 3. Data Pengukuran Biologis Spesimen Morfometrik
        // Morfometrik spesimen individu untuk tiap batch sampel
        $measurementsSpec = [
            'SMP-202608-0001' => [
                // Spesimen Cakalang (SKJ) & Madidihang (YFT)
                ['species' => $skjSpecies, 'fl' => 45.5, 'tl' => 48.0, 'sl' => 43.0, 'weight' => 1950, 'sex' => 'female', 'tkg' => 3, 'stomach' => 3, 'notes' => 'Gonad membesar, butir telur mulai terlihat halus.'],
                ['species' => $skjSpecies, 'fl' => 48.2, 'tl' => 51.0, 'sl' => 45.8, 'weight' => 2300, 'sex' => 'female', 'tkg' => 4, 'stomach' => 2, 'notes' => 'Gonad matang penuh, pembuluh darah tampak jelas.'],
                ['species' => $skjSpecies, 'fl' => 42.0, 'tl' => 44.5, 'sl' => 39.5, 'weight' => 1550, 'sex' => 'male',   'tkg' => 2, 'stomach' => 4, 'notes' => 'Testis berbentuk pita keputihan.'],
                ['species' => $skjSpecies, 'fl' => 52.0, 'tl' => 55.2, 'sl' => 49.0, 'weight' => 2900, 'sex' => 'male',   'tkg' => 4, 'stomach' => 3, 'notes' => 'Sperma keluar jika ditekan perlahan.'],
                ['species' => $skjSpecies, 'fl' => 46.8, 'tl' => 49.4, 'sl' => 44.2, 'weight' => 2100, 'sex' => 'female', 'tkg' => 3, 'stomach' => 2, 'notes' => 'Kondisi fisik utuh tanpa parasit.'],
                ['species' => $skjSpecies, 'fl' => 40.5, 'tl' => 43.0, 'sl' => 38.0, 'weight' => 1400, 'sex' => 'undetermined', 'tkg' => 1, 'stomach' => 1, 'notes' => 'Gonad sangat kecil bening, belum terdiferensiasi.'],
                ['species' => $yftSpecies, 'fl' => 62.0, 'tl' => 66.5, 'sl' => 59.0, 'weight' => 5400, 'sex' => 'female', 'tkg' => 3, 'stomach' => 4, 'notes' => 'Isi lambung didominasi teri dan cumi kecil.'],
                ['species' => $yftSpecies, 'fl' => 68.5, 'tl' => 73.0, 'sl' => 65.0, 'weight' => 7100, 'sex' => 'male',   'tkg' => 3, 'stomach' => 3, 'notes' => 'Sirip dada memanjang normal, warna cerah.'],
            ],
            'SMP-202609-0002' => [
                // Spesimen Tongkol Komo (KAW)
                ['species' => $kawSpecies, 'fl' => 34.0, 'tl' => 36.5, 'sl' => 32.0, 'weight' => 650, 'sex' => 'female', 'tkg' => 2, 'stomach' => 3, 'notes' => 'Bercak hitam khas di bawah sirip dada terlihat jelas.'],
                ['species' => $kawSpecies, 'fl' => 36.5, 'tl' => 39.0, 'sl' => 34.5, 'weight' => 820, 'sex' => 'female', 'tkg' => 3, 'stomach' => 2, 'notes' => 'Ovarium mengisi 1/2 rongga perut.'],
                ['species' => $kawSpecies, 'fl' => 38.0, 'tl' => 40.5, 'sl' => 36.0, 'weight' => 950, 'sex' => 'male',   'tkg' => 3, 'stomach' => 4, 'notes' => 'Lambung berisi rebon dan jeli laut.'],
                ['species' => $kawSpecies, 'fl' => 31.5, 'tl' => 33.8, 'sl' => 29.5, 'weight' => 520, 'sex' => 'male',   'tkg' => 2, 'stomach' => 2, 'notes' => 'Spesimen muda.'],
                ['species' => $kawSpecies, 'fl' => 41.0, 'tl' => 44.0, 'sl' => 39.0, 'weight' => 1200, 'sex' => 'female', 'tkg' => 4, 'stomach' => 1, 'notes' => 'Induk matang gonad mendekati pemijahan.'],
                ['species' => $kawSpecies, 'fl' => 35.2, 'tl' => 37.8, 'sl' => 33.0, 'weight' => 740, 'sex' => 'undetermined', 'tkg' => 1, 'stomach' => 3, 'notes' => 'Fase perkembangan.'],
            ],
            'SMP-202609-0003' => [
                // Madidihang (YFT) Ukuran Besar Sabang
                ['species' => $yftSpecies, 'fl' => 78.0, 'tl' => 84.0, 'sl' => 74.0, 'weight' => 11200, 'sex' => 'male',   'tkg' => 4, 'stomach' => 3, 'notes' => 'Tuna madidihang dewasa, daging kenyal kemerahan.'],
                ['species' => $yftSpecies, 'fl' => 85.0, 'tl' => 91.5, 'sl' => 81.0, 'weight' => 14800, 'sex' => 'female', 'tkg' => 4, 'stomach' => 4, 'notes' => 'Ovarium sangat besar, butir telur matang.'],
                ['species' => $yftSpecies, 'fl' => 72.5, 'tl' => 78.0, 'sl' => 69.0, 'weight' => 8900,  'sex' => 'female', 'tkg' => 3, 'stomach' => 2, 'notes' => 'Ukuran di atas Lm50 (>60 cm).'],
                ['species' => $yftSpecies, 'fl' => 64.0, 'tl' => 69.0, 'sl' => 61.0, 'weight' => 6200,  'sex' => 'male',   'tkg' => 3, 'stomach' => 3, 'notes' => 'Kondisi sirip punggung kedua mulai memanjang.'],
                ['species' => $yftSpecies, 'fl' => 92.0, 'tl' => 99.0, 'sl' => 88.0, 'weight' => 18500, 'sex' => 'male',   'tkg' => 5, 'stomach' => 1, 'notes' => 'Fase salin (spent) setelah memijah.'],
            ],
            'SMP-202609-0004' => [
                // Kakap Merah (LJA) & Kerapu (COT) Meulaboh
                ['species' => $ljaSpecies, 'fl' => 42.0, 'tl' => 46.0, 'sl' => 38.5, 'weight' => 1800, 'sex' => 'female', 'tkg' => 3, 'stomach' => 3, 'notes' => 'Warna sisik merah menyala, sirip dorsal utuh.'],
                ['species' => $ljaSpecies, 'fl' => 48.5, 'tl' => 53.0, 'sl' => 45.0, 'weight' => 2600, 'sex' => 'male',   'tkg' => 4, 'stomach' => 2, 'notes' => 'Kematangan gonad tahap lanjut.'],
                ['species' => $ljaSpecies, 'fl' => 38.0, 'tl' => 41.5, 'sl' => 35.0, 'weight' => 1350, 'sex' => 'female', 'tkg' => 2, 'stomach' => 4, 'notes' => 'Lambung berisi kepiting karang dan udang.'],
                ['species' => $cotSpecies, 'fl' => 44.0, 'tl' => 47.5, 'sl' => 40.0, 'weight' => 2100, 'sex' => 'female', 'tkg' => 3, 'stomach' => 3, 'notes' => 'Totol oranye kecokelatan rapat merata.'],
                ['species' => $cotSpecies, 'fl' => 51.0, 'tl' => 55.0, 'sl' => 47.0, 'weight' => 3400, 'sex' => 'female', 'tkg' => 4, 'stomach' => 1, 'notes' => 'Kerapu lumpur matang gonad.'],
            ],
            'SMP-202609-0005' => [
                // Tongkol Komo (KAW) & Tenggiri (COM) PPN Idi
                ['species' => $kawSpecies, 'fl' => 32.0, 'tl' => 34.5, 'sl' => 30.0, 'weight' => 580, 'sex' => 'male',   'tkg' => 2, 'stomach' => 3, 'notes' => 'Hasil tangkapan pukat cincin malam hari.'],
                ['species' => $kawSpecies, 'fl' => 35.0, 'tl' => 37.5, 'sl' => 33.0, 'weight' => 760, 'sex' => 'female', 'tkg' => 3, 'stomach' => 2, 'notes' => 'Kondisi ikan segar berlendir bening.'],
                ['species' => $kawSpecies, 'fl' => 37.5, 'tl' => 40.0, 'sl' => 35.5, 'weight' => 920, 'sex' => 'female', 'tkg' => 3, 'stomach' => 4, 'notes' => 'Isi lambung ikan teri dan selar muda.'],
                ['species' => $comSpecies, 'fl' => 68.0, 'tl' => 74.0, 'sl' => 64.0, 'weight' => 2800, 'sex' => 'male',   'tkg' => 3, 'stomach' => 3, 'notes' => 'Garis belang vertikal sisi tubuh tegas keperakan.'],
                ['species' => $comSpecies, 'fl' => 75.0, 'tl' => 81.5, 'sl' => 71.0, 'weight' => 3700, 'sex' => 'female', 'tkg' => 4, 'stomach' => 2, 'notes' => 'Tenggiri batang ukuran konsumsi premium.'],
            ],
            'SMP-202609-0006' => [
                // Cakalang (SKJ) Ulangan Lampulo
                ['species' => $skjSpecies, 'fl' => 44.0, 'tl' => 46.8, 'sl' => 41.5, 'weight' => 1750, 'sex' => 'male',   'tkg' => 3, 'stomach' => 3, 'notes' => 'Garis longitudinal perut 4-6 garis jelas.'],
                ['species' => $skjSpecies, 'fl' => 47.0, 'tl' => 50.0, 'sl' => 44.5, 'weight' => 2150, 'sex' => 'female', 'tkg' => 3, 'stomach' => 2, 'notes' => 'Diameter butir telur rata-rata 0.6 mm.'],
                ['species' => $skjSpecies, 'fl' => 50.5, 'tl' => 53.5, 'sl' => 47.8, 'weight' => 2650, 'sex' => 'female', 'tkg' => 4, 'stomach' => 4, 'notes' => 'Kondisi prima siap memijah.'],
                ['species' => $skjSpecies, 'fl' => 43.5, 'tl' => 46.0, 'sl' => 41.0, 'weight' => 1700, 'sex' => 'male',   'tkg' => 2, 'stomach' => 2, 'notes' => 'Spesimen ukuran menengah.'],
            ],
        ];

        foreach ($samplesData as $sampleInfo) {
            $code = $sampleInfo['sample_code'];
            $sample = Sample::firstOrCreate(
                ['sample_code' => $code],
                $sampleInfo
            );

            // Buat data pengukuran morfometrik biologis
            if (isset($measurementsSpec[$code])) {
                $totalGram = 0;
                $specimenNum = 1;

                foreach ($measurementsSpec[$code] as $spec) {
                    $species = $spec['species'] ?: $skjSpecies;
                    if (! $species) {
                        continue;
                    }

                    BiologicalMeasurement::firstOrCreate(
                        [
                            'sample_id' => $sample->id,
                            'specimen_number' => $specimenNum,
                        ],
                        [
                            'fish_species_id' => $species->id,
                            'fork_length_cm' => $spec['fl'],
                            'total_length_cm' => $spec['tl'],
                            'standard_length_cm' => $spec['sl'],
                            'weight_gram' => $spec['weight'],
                            'sex' => $spec['sex'],
                            'gonad_maturity_stage' => $spec['tkg'],
                            'stomach_fullness' => $spec['stomach'],
                            'notes' => $spec['notes'],
                        ]
                    );

                    $totalGram += $spec['weight'];
                    $specimenNum++;
                }

                // Update total spesimen & berat sampel (kg)
                $sample->update([
                    'total_specimens' => count($measurementsSpec[$code]),
                    'total_weight_kg' => round($totalGram / 1000, 2),
                ]);
            }
        }
    }
}

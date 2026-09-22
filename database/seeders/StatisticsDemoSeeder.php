<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\FishCatch;
use App\Models\Fisherman;
use App\Models\FishingEffort;
use App\Models\FishingGear;
use App\Models\FishingTrip;
use App\Models\Landing;
use App\Models\LandingItem;
use App\Models\LandingSite;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Species;
use App\Models\Vessel;
use App\Models\VesselType;
use App\Models\Village;
use App\Models\Wppnri;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatisticsDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Statistics Demo Seeder...');
        $this->cleanUp();

        // Get Master Data
        $province = Province::first();
        $vesselType = VesselType::first();
        $wpps = Wppnri::where('is_active', true)->limit(2)->get();
        $gears = FishingGear::where('is_active', true)->limit(3)->get();
        $landingSites = LandingSite::where('is_active', true)->limit(2)->get();
        $species = Species::where('is_active', true)->whereNotNull('fao_code')->limit(10)->get();
        $regency = Regency::first();
        $district = District::first();
        $village = Village::first();

        if (! $province || ! $vesselType || $wpps->count() < 2 || $gears->count() < 3 || $landingSites->count() < 2 || $species->count() < 8) {
            $this->command->error('Error: Master data is insufficient to run this seeder.');
            $this->command->line('Required: 1 Province, 1 VesselType, 2 WPPs, 3 Gears, 2 Landing Sites, 8 Species.');

            return;
        }

        // 1. Create Fishers (10)
        $fishers = collect();
        for ($i = 1; $i <= 10; $i++) {
            $fishers->push(Fisherman::create([
                'name' => "Demo Fisher $i",
                'nik' => "111111111111000$i",
                'gender' => 'L',
                'province_id' => $province->id,
                'regency_id' => $regency->id ?? null,
                'district_id' => $district->id ?? null,
                'village_id' => $village->id ?? null,
                'is_active' => true,
            ]));
        }

        // 2. Create Vessels (10)
        $vessels = collect();
        foreach ($fishers as $i => $fisher) {
            $index = $i + 1;
            $vessels->push(Vessel::create([
                'name' => "Demo Vessel $index",
                'registration_number' => "DEMO-V-$index",
                'owner_id' => $fisher->id,
                'vessel_type_id' => $vesselType->id,
                'gross_tonnage' => rand(5, 30),
                'primary_gear_id' => $gears->random()->id,
                'homeport_site_id' => $landingSites->random()->id,
                'is_active' => true,
            ]));
        }

        // 3. Create Fishing Trips (36 total, 6 per month for Jan-Jun 2026)
        $trips = collect();
        for ($month = 1; $month <= 6; $month++) {
            for ($t = 1; $t <= 6; $t++) {
                $vessel = $vessels->random();
                $departure = Carbon::create(2026, $month, rand(1, 20), 6, 0, 0);
                $return = $departure->copy()->addDays(rand(1, 5))->addHours(rand(1, 12));

                $trip = FishingTrip::create([
                    'trip_number' => "TRIP-DEMO-2026-$month-$t",
                    'vessel_id' => $vessel->id,
                    'captain_id' => $fishers->random()->id,
                    'departure_site_id' => $landingSites->random()->id,
                    'landing_site_id' => $landingSites->random()->id,
                    'departure_date' => $departure,
                    'return_date' => $return,
                    'crew_count' => rand(3, 10),
                    'primary_gear_id' => $vessel->primary_gear_id,
                    'wppnri_id' => $wpps->random()->id,
                    'notes' => 'DEMO_STATISTICS_2026',
                    'validation_status' => 'validated',
                ]);
                $trips->push($trip);

                // 4. Create Fishing Efforts (1-2 per trip)
                $numEfforts = rand(1, 2);
                for ($e = 1; $e <= $numEfforts; $e++) {
                    $effort = FishingEffort::create([
                        'fishing_trip_id' => $trip->id,
                        'fishing_gear_id' => $gears->random()->id,
                        'setting_date' => $departure->copy()->addHours($e * 5),
                        'hauling_date' => $departure->copy()->addHours($e * 5 + rand(4, 10)),
                        'duration_hours' => rand(4, 12),
                    ]);

                    // 8. Create Catches for this effort (2-4 species)
                    $caughtSpecies = $species->random(rand(2, 4));
                    foreach ($caughtSpecies as $sp) {
                        FishCatch::create([
                            'fishing_trip_id' => $trip->id,
                            'fishing_effort_id' => $effort->id,
                            'fish_species_id' => $sp->id,
                            'weight_kg' => rand(20, 150),
                            'fish_count' => rand(10, 50),
                            'notes' => 'DEMO_STATISTICS_2026',
                        ]);
                    }
                }

                // 6 & 7. Create Landing & Landing Items
                $landing = Landing::create([
                    'landing_number' => "LND-DEMO-2026-$month-$t",
                    'fishing_trip_id' => $trip->id,
                    'landing_site_id' => $trip->landing_site_id,
                    'landing_date' => $return,
                    'total_weight_kg' => 0,
                    'notes' => 'DEMO_STATISTICS_2026',
                ]);

                // Aggregate catches to landing items
                $tripCatches = FishCatch::where('fishing_trip_id', $trip->id)
                    ->select('fish_species_id', DB::raw('SUM(weight_kg) as total_weight'))
                    ->groupBy('fish_species_id')
                    ->get();

                $landingTotalWeight = 0;
                foreach ($tripCatches as $tc) {
                    LandingItem::create([
                        'landing_id' => $landing->id,
                        'fish_species_id' => $tc->fish_species_id,
                        'weight_kg' => $tc->total_weight,
                        'price_per_kg' => rand(10000, 50000),
                        'total_price' => $tc->total_weight * rand(10000, 50000),
                    ]);
                    $landingTotalWeight += $tc->total_weight;
                }

                $landing->update(['total_weight_kg' => $landingTotalWeight]);
            }
        }

        $this->command->info('Seeder finished successfully.');
    }

    public function cleanUp(): void
    {
        $this->command->info('Cleaning up old demo data...');

        $landings = Landing::where('notes', 'DEMO_STATISTICS_2026')->get();
        foreach ($landings as $l) {
            LandingItem::where('landing_id', $l->id)->delete();
            $l->delete();
        }

        FishCatch::where('notes', 'DEMO_STATISTICS_2026')->delete();

        $trips = FishingTrip::where('notes', 'DEMO_STATISTICS_2026')->get();
        foreach ($trips as $t) {
            FishingEffort::where('fishing_trip_id', $t->id)->delete();
            $t->delete();
        }

        Vessel::where('registration_number', 'LIKE', 'DEMO-V-%')->delete();
        Fisherman::where('name', 'LIKE', 'Demo Fisher %')->delete();

        $this->command->info('Old demo data cleaned.');
    }
}

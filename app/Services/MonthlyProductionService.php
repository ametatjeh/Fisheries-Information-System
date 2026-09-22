<?php

namespace App\Services;

use App\Models\Landing;
use App\Models\MonthlyProductionStatistic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonthlyProductionService
{
    /**
     * Hitung rekapitulasi produksi bulanan berbasis pendaratan aktual (Landing Production)
     * per stratum Tahun, Bulan, Kabupaten, Pangkalan/TPI, Alat Tangkap, dan Jenis Ikan.
     *
     * Semantik Tanggal: landings.landing_date (Waktu riil ikan dibongkar & ditimbang di pelabuhan).
     */
    public function aggregateFromLandings(int $year, ?int $month = null, ?int $landingSiteId = null): Collection
    {
        $driver = DB::connection()->getDriverName();
        $yearExpr = $driver === 'sqlite'
            ? "cast(strftime('%Y', landings.landing_date) as integer)"
            : 'YEAR(landings.landing_date)';
        $monthExpr = $driver === 'sqlite'
            ? "cast(strftime('%m', landings.landing_date) as integer)"
            : 'MONTH(landings.landing_date)';

        $query = DB::table('landing_items')
            ->join('landings', 'landing_items.landing_id', '=', 'landings.id')
            ->join('landing_sites', 'landings.landing_site_id', '=', 'landing_sites.id')
            ->leftJoin('fishing_trips', 'landings.fishing_trip_id', '=', 'fishing_trips.id')
            ->whereYear('landings.landing_date', $year);

        if ($month) {
            $query->whereMonth('landings.landing_date', $month);
        }
        if ($landingSiteId) {
            $query->where('landings.landing_site_id', $landingSiteId);
        }

        return $query->select([
            DB::raw("{$yearExpr} as year"),
            DB::raw("{$monthExpr} as month"),
            'landing_sites.regency_id',
            'landings.landing_site_id',
            'landing_items.fish_species_id',
            DB::raw('COALESCE(fishing_trips.primary_gear_id, 1) as fishing_gear_id'),
            DB::raw('SUM(landing_items.weight_kg) as total_volume_kg'),
            DB::raw('SUM(landing_items.total_price) as total_value_rp'),
            DB::raw('COUNT(DISTINCT landings.fishing_trip_id) as total_trips'),
            DB::raw('COUNT(DISTINCT fishing_trips.vessel_id) as total_active_vessels'),
        ])
            ->groupBy([
                DB::raw($yearExpr),
                DB::raw($monthExpr),
                'landing_sites.regency_id',
                'landings.landing_site_id',
                'landing_items.fish_species_id',
                'fishing_trips.primary_gear_id',
            ])
            ->get()
            ->map(function ($row) {
                $volume = (float) $row->total_volume_kg;
                $value = (float) $row->total_value_rp;
                $avgPrice = $volume > 0 ? round($value / $volume, 2) : 0.0;

                return [
                    'year' => (int) $row->year,
                    'month' => (int) $row->month,
                    'regency_id' => (int) $row->regency_id,
                    'landing_site_id' => (int) $row->landing_site_id,
                    'fish_species_id' => (int) $row->fish_species_id,
                    'fishing_gear_id' => (int) $row->fishing_gear_id,
                    'total_volume_kg' => $volume,
                    'total_value_rp' => $value,
                    'average_price_per_kg' => $avgPrice,
                    'total_active_vessels' => max((int) $row->total_active_vessels, 1),
                    'total_trips' => max((int) $row->total_trips, 1),
                ];
            });
    }

    /**
     * Sinkronisasikan agregasi pendaratan ke dalam tabel statistik produksi bulanan (monthly_production_statistics).
     *
     * @return int Jumlah baris statistik yang diperbarui/dibuat
     */
    public function syncMonthlyStatistics(int $year, ?int $month = null): int
    {
        $aggregated = $this->aggregateFromLandings($year, $month);
        $synced = 0;

        foreach ($aggregated as $item) {
            MonthlyProductionStatistic::updateOrCreate(
                [
                    'regency_id' => $item['regency_id'],
                    'landing_site_id' => $item['landing_site_id'],
                    'fish_species_id' => $item['fish_species_id'],
                    'fishing_gear_id' => $item['fishing_gear_id'],
                    'year' => $item['year'],
                    'month' => $item['month'],
                ],
                $item
            );
            $synced++;
        }

        return $synced;
    }

    /**
     * Ambil ringkasan produksi multi-dimensi (Bulan, Spesies, Alat Tangkap, WPP, Pangkalan)
     * beserta perbandingan tegas: Observed Catch, Landing Production, Estimated Production, dan Monthly Production.
     */
    public function getProductionSummary(array $filters = []): array
    {
        $year = (int) ($filters['year'] ?? 2026);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;
        $landingSiteId = ! empty($filters['landing_site_id']) ? (int) $filters['landing_site_id'] : null;
        $gearId = ! empty($filters['gear_id']) ? (int) $filters['gear_id'] : null;
        $speciesId = ! empty($filters['species_id']) ? (int) $filters['species_id'] : null;
        $wppnriId = ! empty($filters['wppnri_id']) ? (int) $filters['wppnri_id'] : null;

        // 1. Landing Production Query (Tabel landing_items + landings)
        $landingQuery = DB::table('landing_items')
            ->join('landings', 'landing_items.landing_id', '=', 'landings.id')
            ->leftJoin('fishing_trips', 'landings.fishing_trip_id', '=', 'fishing_trips.id')
            ->leftJoin('wppnri', 'fishing_trips.wppnri_id', '=', 'wppnri.id')
            ->leftJoin('species', 'landing_items.fish_species_id', '=', 'species.id')
            ->leftJoin('fishing_gears', 'fishing_trips.primary_gear_id', '=', 'fishing_gears.id')
            ->whereYear('landings.landing_date', $year);

        if ($month) {
            $landingQuery->whereMonth('landings.landing_date', $month);
        }
        if ($landingSiteId) {
            $landingQuery->where('landings.landing_site_id', $landingSiteId);
        }
        if ($gearId) {
            $landingQuery->where('fishing_trips.primary_gear_id', $gearId);
        }
        if ($speciesId) {
            $landingQuery->where('landing_items.fish_species_id', $speciesId);
        }
        if ($wppnriId) {
            $landingQuery->where('fishing_trips.wppnri_id', $wppnriId);
        }

        $totalLandingWeight = (float) (clone $landingQuery)->sum('landing_items.weight_kg');
        $totalLandingValue = (float) (clone $landingQuery)->sum('landing_items.total_price');

        // 2. Observed Catch Query (Tabel catches + fishing_trips)
        $catchQuery = DB::table('catches')
            ->join('fishing_efforts', 'catches.fishing_effort_id', '=', 'fishing_efforts.id')
            ->join('fishing_trips', 'fishing_efforts.fishing_trip_id', '=', 'fishing_trips.id')
            ->whereYear('fishing_trips.departure_date', $year);

        if ($month) {
            $catchQuery->whereMonth('fishing_trips.departure_date', $month);
        }
        if ($landingSiteId) {
            $catchQuery->where('fishing_trips.landing_site_id', $landingSiteId);
        }
        if ($gearId) {
            $catchQuery->where('fishing_efforts.fishing_gear_id', $gearId);
        }
        if ($speciesId) {
            $catchQuery->where('catches.fish_species_id', $speciesId);
        }
        if ($wppnriId) {
            $catchQuery->where('fishing_trips.wppnri_id', $wppnriId);
        }

        $totalObservedCatch = (float) $catchQuery->sum('catches.weight_kg');

        // 3. Estimated Production Query (Tabel catch_estimations)
        $estimationQuery = DB::table('catch_estimations')->where('year', $year);
        if ($month) {
            $estimationQuery->where('month', $month);
        }
        if ($landingSiteId) {
            $estimationQuery->where('landing_site_id', $landingSiteId);
        }
        if ($gearId) {
            $estimationQuery->where('fishing_gear_id', $gearId);
        }
        if ($speciesId) {
            $estimationQuery->where('fish_species_id', $speciesId);
        }

        $totalEstimatedCatch = (float) $estimationQuery->sum('estimated_catch_kg');

        // 4. Monthly Statistics Query (Tabel monthly_production_statistics)
        $monthlyStatQuery = DB::table('monthly_production_statistics')->where('year', $year);
        if ($month) {
            $monthlyStatQuery->where('month', $month);
        }
        if ($landingSiteId) {
            $monthlyStatQuery->where('landing_site_id', $landingSiteId);
        }
        if ($gearId) {
            $monthlyStatQuery->where('fishing_gear_id', $gearId);
        }
        if ($speciesId) {
            $monthlyStatQuery->where('fish_species_id', $speciesId);
        }

        $totalMonthlyStatVolume = (float) $monthlyStatQuery->sum('total_volume_kg');
        $totalMonthlyStatValue = (float) $monthlyStatQuery->sum('total_value_rp');

        // 5. Agregasi Produksi Pendaratan per Spesies
        $bySpecies = (clone $landingQuery)
            ->select([
                'species.id',
                DB::raw("COALESCE(species.local_name_id, species.scientific_name, 'Lainnya') as species_name"),
                'species.fao_code',
                DB::raw('SUM(landing_items.weight_kg) as volume_kg'),
                DB::raw('SUM(landing_items.total_price) as value_rp'),
            ])
            ->groupBy('species.id', 'species.local_name_id', 'species.scientific_name', 'species.fao_code')
            ->orderByDesc('volume_kg')
            ->limit(10)
            ->get();

        // 6. Agregasi Produksi Pendaratan per Alat Tangkap
        $byGear = (clone $landingQuery)
            ->select([
                DB::raw("COALESCE(fishing_gears.name, 'Alat Tangkap Standar') as gear_name"),
                DB::raw('SUM(landing_items.weight_kg) as volume_kg'),
            ])
            ->groupBy('gear_name')
            ->orderByDesc('volume_kg')
            ->get();

        // 7. Agregasi Produksi Pendaratan per WPP (NULL WPP handling -> 'Tidak Terpetakan')
        $byWpp = (clone $landingQuery)
            ->select([
                DB::raw("COALESCE(wppnri.name, 'Tidak Terpetakan') as wpp_name"),
                DB::raw('SUM(landing_items.weight_kg) as volume_kg'),
            ])
            ->groupBy('wpp_name')
            ->orderByDesc('volume_kg')
            ->get();

        return [
            'metrics' => [
                'total_landing_kg' => $totalLandingWeight,
                'total_landing_value_rp' => $totalLandingValue,
                'total_observed_catch_kg' => $totalObservedCatch,
                'total_estimated_production_kg' => $totalEstimatedCatch,
                'total_monthly_stat_kg' => $totalMonthlyStatVolume,
                'total_monthly_stat_value_rp' => $totalMonthlyStatValue,
            ],
            'by_species' => $bySpecies,
            'by_gear' => $byGear,
            'by_wpp' => $byWpp,
        ];
    }

    /**
     * Ambil data laporan rekapitulasi produksi bulanan (Monthly Production Report)
     * dengan dukungan paginasi, filter multidimensi (Tahun, Bulan, Pangkalan, Alat Tangkap, Spesies, WPP),
     * status validasi, dan rekonsiliasi statistik komparatif.
     */
    public function getMonthlyProductionReport(array $filters = [], bool $paginate = true, int $limit = 25): array
    {
        $driver = DB::connection()->getDriverName();
        $yearExpr = $driver === 'sqlite'
            ? "cast(strftime('%Y', landings.landing_date) as integer)"
            : 'YEAR(landings.landing_date)';
        $monthExpr = $driver === 'sqlite'
            ? "cast(strftime('%m', landings.landing_date) as integer)"
            : 'MONTH(landings.landing_date)';

        $year = (int) ($filters['year'] ?? 2026);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;
        $landingSiteId = ! empty($filters['landing_site_id']) ? (int) $filters['landing_site_id'] : null;
        $gearId = ! empty($filters['gear_id']) ? (int) $filters['gear_id'] : null;
        $speciesId = ! empty($filters['species_id']) ? (int) $filters['species_id'] : null;
        $wppnriId = ! empty($filters['wppnri_id']) ? (int) $filters['wppnri_id'] : null;

        $baseQuery = DB::table('landing_items')
            ->join('landings', 'landing_items.landing_id', '=', 'landings.id')
            ->join('landing_sites', 'landings.landing_site_id', '=', 'landing_sites.id')
            ->leftJoin('regencies', 'landing_sites.regency_id', '=', 'regencies.id')
            ->leftJoin('fishing_trips', 'landings.fishing_trip_id', '=', 'fishing_trips.id')
            ->leftJoin('wppnri', 'fishing_trips.wppnri_id', '=', 'wppnri.id')
            ->leftJoin('fishing_gears', 'fishing_trips.primary_gear_id', '=', 'fishing_gears.id')
            ->leftJoin('species', 'landing_items.fish_species_id', '=', 'species.id');

        if ($driver === 'sqlite') {
            $baseQuery->whereRaw("cast(strftime('%Y', landings.landing_date) as integer) = ?", [$year]);
            if ($month) {
                $baseQuery->whereRaw("cast(strftime('%m', landings.landing_date) as integer) = ?", [$month]);
            }
        } else {
            $baseQuery->whereYear('landings.landing_date', $year);
            if ($month) {
                $baseQuery->whereMonth('landings.landing_date', $month);
            }
        }

        if ($landingSiteId) {
            $baseQuery->where('landings.landing_site_id', $landingSiteId);
        }
        if ($gearId) {
            $baseQuery->where('fishing_trips.primary_gear_id', $gearId);
        }
        if ($speciesId) {
            $baseQuery->where('landing_items.fish_species_id', $speciesId);
        }
        if ($wppnriId) {
            $baseQuery->where('fishing_trips.wppnri_id', $wppnriId);
        }

        // Agregasi Total Bersih tanpa duplikasi join
        $totalVolumeKg = (float) (clone $baseQuery)->sum('landing_items.weight_kg');
        $totalValueRp = (float) (clone $baseQuery)->sum('landing_items.total_price');
        $avgPricePerKg = $totalVolumeKg > 0 ? round($totalValueRp / $totalVolumeKg, 2) : 0.0;
        $totalTrips = (int) (clone $baseQuery)->distinct()->count('landings.fishing_trip_id');
        $avgCpue = $totalTrips > 0 ? round($totalVolumeKg / $totalTrips, 2) : 0.0;

        // Query Per Strata (Tahun, Bulan, Pangkalan, WPP, Alat Tangkap, Spesies)
        $stratumQuery = (clone $baseQuery)->select([
            DB::raw("{$yearExpr} as year"),
            DB::raw("{$monthExpr} as month"),
            'landing_sites.name as landing_site_name',
            'regencies.name as regency_name',
            DB::raw("COALESCE(wppnri.name, 'Tidak Terpetakan') as wpp_name"),
            DB::raw("COALESCE(fishing_gears.name, 'Alat Tangkap Standar') as gear_name"),
            DB::raw("COALESCE(species.local_name_id, species.scientific_name, 'Lainnya') as species_name"),
            'species.scientific_name',
            'species.fao_code',
            DB::raw('SUM(landing_items.weight_kg) as volume_kg'),
            DB::raw('SUM(landing_items.total_price) as value_rp'),
            DB::raw("COALESCE(fishing_trips.validation_status, 'validated') as status"),
        ])
            ->groupBy([
                DB::raw($yearExpr),
                DB::raw($monthExpr),
                'landing_sites.name',
                'regencies.name',
                'wppnri.name',
                'fishing_gears.name',
                'species.local_name_id',
                'species.scientific_name',
                'species.fao_code',
                'fishing_trips.validation_status',
            ])
            ->orderByDesc('volume_kg');

        $records = $paginate
            ? $stratumQuery->paginate($limit)->withQueryString()
            : $stratumQuery->get();

        $totalRecords = $paginate ? $records->total() : $records->count();

        // Ringkasan metrik rekonsiliasi komparatif
        $summary = $this->getProductionSummary($filters);

        return [
            'records' => $records,
            'totalVolumeKg' => $totalVolumeKg,
            'totalVolumeTon' => round($totalVolumeKg / 1000, 2),
            'totalValueRp' => $totalValueRp,
            'avgPricePerKg' => $avgPricePerKg,
            'totalTrips' => $totalTrips,
            'avgCpue' => $avgCpue,
            'totalRecords' => $totalRecords,
            'summary' => $summary,
        ];
    }
}

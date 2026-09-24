<?php

namespace App\Services;

use App\Models\CatchEstimation;
use App\Models\FishCatch;
use App\Models\FishingEffort;
use App\Models\FishingGear;
use App\Models\FishingTrip;
use App\Models\Landing;
use App\Models\LandingItem;
use App\Models\LandingSite;
use App\Models\MonthlyProductionStatistic;
use App\Models\Species;
use App\Models\Vessel;
use App\Models\Wppnri;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdvancedStatisticService
{
    /**
     * Parse and normalize input filters.
     *
     * @param  array<string, mixed>  $rawFilters
     * @return array<string, mixed>
     */
    public function normalizeFilters(array $rawFilters = []): array
    {
        $year = isset($rawFilters['year']) && $rawFilters['year'] !== '' ? (int) $rawFilters['year'] : (isset($rawFilters['tahun']) && $rawFilters['tahun'] !== '' ? (int) $rawFilters['tahun'] : null);
        $month = isset($rawFilters['month']) && $rawFilters['month'] !== '' ? (int) $rawFilters['month'] : (isset($rawFilters['bulan']) && $rawFilters['bulan'] !== '' ? (int) $rawFilters['bulan'] : null);
        $startDate = ! empty($rawFilters['start_date']) ? Carbon::parse($rawFilters['start_date'])->format('Y-m-d') : null;
        $endDate = ! empty($rawFilters['end_date']) ? Carbon::parse($rawFilters['end_date'])->format('Y-m-d') : null;

        $wppnriId = ! empty($rawFilters['wppnri_id']) ? (int) $rawFilters['wppnri_id'] : null;
        $landingSiteId = ! empty($rawFilters['landing_site_id']) ? (int) $rawFilters['landing_site_id'] : null;
        $gearId = ! empty($rawFilters['gear_id']) ? (int) $rawFilters['gear_id'] : null;
        $speciesId = ! empty($rawFilters['species_id']) ? (int) $rawFilters['species_id'] : null;
        $vesselId = ! empty($rawFilters['vessel_id']) ? (int) $rawFilters['vessel_id'] : null;

        // Label periode manusiawi
        $periodLabel = 'Semua Periode';
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if ($startDate && $endDate) {
            $periodLabel = Carbon::parse($startDate)->format('d M Y').' - '.Carbon::parse($endDate)->format('d M Y');
        } elseif ($year && $month) {
            $periodLabel = ($monthNames[$month] ?? 'Bulan '.$month).' '.$year;
        } elseif ($year) {
            $periodLabel = 'Tahun '.$year;
        } elseif ($month) {
            $periodLabel = ($monthNames[$month] ?? 'Bulan '.$month);
        }

        return [
            'year' => $year,
            'month' => $month,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'wppnri_id' => $wppnriId,
            'landing_site_id' => $landingSiteId,
            'gear_id' => $gearId,
            'species_id' => $speciesId,
            'vessel_id' => $vesselId,
            'period_label' => $periodLabel,
        ];
    }

    /**
     * Helper to apply trip-level date/scope constraints to a FishingTrip query builder.
     */
    protected function applyTripFilters($query, array $filters, string $dateColumn = 'departure_date')
    {
        if (! empty($filters['year'])) {
            $query->whereYear($dateColumn, $filters['year']);
        }
        if (! empty($filters['month'])) {
            $query->whereMonth($dateColumn, $filters['month']);
        }
        if (! empty($filters['start_date'])) {
            $query->whereDate($dateColumn, '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $query->whereDate($dateColumn, '<=', $filters['end_date']);
        }
        if (! empty($filters['wppnri_id'])) {
            $query->where('wppnri_id', $filters['wppnri_id']);
        }
        if (! empty($filters['landing_site_id'])) {
            $query->where('landing_site_id', $filters['landing_site_id']);
        }
        if (! empty($filters['gear_id'])) {
            $query->where('primary_gear_id', $filters['gear_id']);
        }
        if (! empty($filters['vessel_id'])) {
            $query->where('vessel_id', $filters['vessel_id']);
        }

        return $query;
    }

    // =========================================================================
    // STAGE 13.2 — ADVANCED KPI
    // =========================================================================

    /**
     * 1. Activity KPIs:
     * - Total Fishing Trips: COUNT(DISTINCT fishing_trips.id)
     * - Active Vessels: COUNT(DISTINCT fishing_trips.vessel_id)
     * - Fishing Efforts: COUNT(fishing_efforts.id)
     * - Total Effort Hours: SUM(fishing_efforts.duration_hours)
     */
    public function getActivityKpi(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);

        // Trips query
        $tripQuery = FishingTrip::query();
        $this->applyTripFilters($tripQuery, $filters, 'departure_date');

        if (! empty($filters['species_id'])) {
            $tripQuery->whereHas('catches', fn ($c) => $c->where('fish_species_id', $filters['species_id']));
        }

        $totalTrips = (int) $tripQuery->count();
        $activeVesselsInTrips = (int) (clone $tripQuery)->whereNotNull('vessel_id')->distinct('vessel_id')->count('vessel_id');

        // Effort query
        $effortQuery = FishingEffort::query();
        $effortQuery->whereHas('fishingTrip', function ($t) use ($filters) {
            $this->applyTripFilters($t, $filters, 'departure_date');
        });
        if (! empty($filters['gear_id'])) {
            $effortQuery->where('fishing_gear_id', $filters['gear_id']);
        }
        if (! empty($filters['species_id'])) {
            $effortQuery->whereHas('catches', fn ($c) => $c->where('fish_species_id', $filters['species_id']));
        }

        $totalEfforts = (int) $effortQuery->count();
        $totalEffortHours = (float) $effortQuery->sum('duration_hours');

        return [
            'total_trips' => [
                'value' => $totalTrips,
                'formatted' => number_format($totalTrips, 0, ',', '.'),
                'unit' => 'Trip',
                'label' => 'Total Fishing Trip',
                'description' => 'Jumlah pelayaran penangkapan ikan terdaftar berdasarkan tanggal keberangkatan.',
                'period' => $filters['period_label'],
                'has_data' => $totalTrips > 0,
            ],
            'active_vessels' => [
                'value' => $activeVesselsInTrips,
                'formatted' => number_format($activeVesselsInTrips, 0, ',', '.'),
                'unit' => 'Kapal',
                'label' => 'Kapal Aktif Beroperasi',
                'description' => 'Jumlah armada kapal unik yang melakukan aktivitas pelayaran pada periode terpilih.',
                'period' => $filters['period_label'],
                'has_data' => $activeVesselsInTrips > 0,
            ],
            'fishing_efforts' => [
                'value' => $totalEfforts,
                'formatted' => number_format($totalEfforts, 0, ',', '.'),
                'unit' => 'Setting',
                'label' => 'Fishing Effort',
                'description' => 'Frekuensi operasi penurunan alat tangkap (setting/hauling) yang tercatat.',
                'period' => $filters['period_label'],
                'has_data' => $totalEfforts > 0,
            ],
            'total_effort_hours' => [
                'value' => round($totalEffortHours, 1),
                'formatted' => number_format($totalEffortHours, 1, ',', '.'),
                'unit' => 'Jam Operasi',
                'label' => 'Total Durasi Upaya',
                'description' => 'Akumulasi jam penarikan/operasi alat tangkap di laut.',
                'period' => $filters['period_label'],
                'has_data' => $totalEffortHours > 0,
            ],
        ];
    }

    /**
     * 2. Catch & Landing KPIs:
     * - Total Catch: SUM(catches.weight_kg)
     * - Total Landing: SUM(landing_items.weight_kg)
     * - Number of Catch Records: COUNT(catches.id)
     * - Number of Species: COUNT(DISTINCT catches.fish_species_id)
     * - CPUE: SUM(catch) / SUM(effort_hours)
     */
    public function getCatchKpi(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);

        // Physical Total Catch (includes legacy trip-level catch)
        $physicalCatchQuery = FishCatch::query();
        $physicalCatchQuery->whereHas('fishingTrip', function ($t) use ($filters) {
            $this->applyTripFilters($t, $filters, 'departure_date');
        });
        if (! empty($filters['species_id'])) {
            $physicalCatchQuery->where('fish_species_id', $filters['species_id']);
        }
        if (! empty($filters['gear_id'])) {
            $physicalCatchQuery->where(function ($q) use ($filters) {
                $q->whereHas('fishingEffort', fn ($e) => $e->where('fishing_gear_id', $filters['gear_id']))
                    ->orWhereHas('fishingTrip', fn ($t) => $t->where('primary_gear_id', $filters['gear_id']));
            });
        }

        $totalPhysicalCatchKg = (float) (clone $physicalCatchQuery)->sum('weight_kg');
        $catchRecordsCount = (int) (clone $physicalCatchQuery)->count();
        $distinctSpeciesCount = (int) (clone $physicalCatchQuery)->whereNotNull('fish_species_id')->distinct('fish_species_id')->count('fish_species_id');

        // Effort-linked Catch (where fishing_effort_id IS NOT NULL)
        $effortLinkedCatchKg = (float) (clone $physicalCatchQuery)->whereNotNull('fishing_effort_id')->sum('weight_kg');

        // Effort hours for CPUE denominator
        $effortQuery = FishingEffort::query();
        $effortQuery->whereHas('fishingTrip', function ($t) use ($filters) {
            $this->applyTripFilters($t, $filters, 'departure_date');
        });
        if (! empty($filters['gear_id'])) {
            $effortQuery->where('fishing_gear_id', $filters['gear_id']);
        }
        if (! empty($filters['species_id'])) {
            $effortQuery->whereHas('catches', fn ($c) => $c->where('fish_species_id', $filters['species_id']));
        }
        $totalEffortHours = (float) $effortQuery->sum('duration_hours');

        // CPUE ratio of sums (kg/jam)
        $cpuePerHour = $totalEffortHours > 0 ? round($effortLinkedCatchKg / $totalEffortHours, 2) : 0;

        // Landing Production (from landings.landing_date)
        $landingQuery = LandingItem::query();
        $landingQuery->whereHas('landing', function ($l) use ($filters) {
            if (! empty($filters['year'])) {
                $l->whereYear('landing_date', $filters['year']);
            }
            if (! empty($filters['month'])) {
                $l->whereMonth('landing_date', $filters['month']);
            }
            if (! empty($filters['start_date'])) {
                $l->whereDate('landing_date', '>=', $filters['start_date']);
            }
            if (! empty($filters['end_date'])) {
                $l->whereDate('landing_date', '<=', $filters['end_date']);
            }
            if (! empty($filters['landing_site_id'])) {
                $l->where('landing_site_id', $filters['landing_site_id']);
            }
            if (! empty($filters['wppnri_id'])) {
                $l->whereHas('fishingTrip', fn ($t) => $t->where('wppnri_id', $filters['wppnri_id']));
            }
            if (! empty($filters['vessel_id'])) {
                $l->where('vessel_id', $filters['vessel_id']);
            }
        });
        if (! empty($filters['species_id'])) {
            $landingQuery->where('fish_species_id', $filters['species_id']);
        }

        $totalLandingKg = (float) (clone $landingQuery)->sum('weight_kg');
        $totalLandingValueRp = (float) (clone $landingQuery)->sum('total_price');

        // Estimated Production (Stage 10 model)
        $estQuery = CatchEstimation::query();
        if (! empty($filters['year'])) {
            $estQuery->where('year', $filters['year']);
        }
        if (! empty($filters['month'])) {
            $estQuery->where('month', $filters['month']);
        }
        if (! empty($filters['landing_site_id'])) {
            $estQuery->where('landing_site_id', $filters['landing_site_id']);
        }
        if (! empty($filters['gear_id'])) {
            $estQuery->where('fishing_gear_id', $filters['gear_id']);
        }
        if (! empty($filters['species_id'])) {
            $estQuery->where('fish_species_id', $filters['species_id']);
        }
        $totalEstimatedKg = (float) $estQuery->sum('estimated_catch_kg');

        // Monthly Production (Stage 11 table)
        $monthlyProdQuery = MonthlyProductionStatistic::query();
        if (! empty($filters['year'])) {
            $monthlyProdQuery->where('year', $filters['year']);
        }
        if (! empty($filters['month'])) {
            $monthlyProdQuery->where('month', $filters['month']);
        }
        if (! empty($filters['landing_site_id'])) {
            $monthlyProdQuery->where('landing_site_id', $filters['landing_site_id']);
        }
        if (! empty($filters['gear_id'])) {
            $monthlyProdQuery->where('fishing_gear_id', $filters['gear_id']);
        }
        if (! empty($filters['species_id'])) {
            $monthlyProdQuery->where('fish_species_id', $filters['species_id']);
        }
        $totalMonthlyProdKg = (float) $monthlyProdQuery->sum('total_volume_kg');

        return [
            'total_catch_physical' => [
                'value' => $totalPhysicalCatchKg,
                'formatted' => number_format($totalPhysicalCatchKg, 0, ',', '.'),
                'ton' => round($totalPhysicalCatchKg / 1000, 2),
                'unit' => 'kg',
                'label' => 'Total Tangkapan Fisik (Observed)',
                'description' => 'Hasil penimbangan tangkapan langsung di laut saat trip (termasuk legacy catch ID 10 sebesar 290 kg).',
                'period' => $filters['period_label'],
                'has_data' => $totalPhysicalCatchKg > 0,
            ],
            'total_catch_effort_linked' => [
                'value' => $effortLinkedCatchKg,
                'formatted' => number_format($effortLinkedCatchKg, 0, ',', '.'),
                'ton' => round($effortLinkedCatchKg / 1000, 2),
                'unit' => 'kg',
                'label' => 'Tangkapan Terkait Upaya (Effort-linked)',
                'description' => 'Tangkapan yang memiliki kaitan langsung ke catatan setting penangkapan (digunakan untuk formula CPUE).',
                'period' => $filters['period_label'],
                'has_data' => $effortLinkedCatchKg > 0,
            ],
            'total_landing' => [
                'value' => $totalLandingKg,
                'formatted' => number_format($totalLandingKg, 0, ',', '.'),
                'ton' => round($totalLandingKg / 1000, 2),
                'unit' => 'kg',
                'label' => 'Total Produksi Pendaratan (Landing)',
                'description' => 'Volume ikan yang didaratkan dan ditimbang pada TPI / pangkalan pendaratan resmi.',
                'period' => $filters['period_label'],
                'has_data' => $totalLandingKg > 0,
            ],
            'total_landing_value' => [
                'value' => $totalLandingValueRp,
                'formatted' => 'Rp '.number_format($totalLandingValueRp, 0, ',', '.'),
                'unit' => 'Rupiah',
                'label' => 'Nilai Omzet Pendaratan',
                'description' => 'Total nilai transaksi lelang / pendaratan ikan di TPI.',
                'period' => $filters['period_label'],
                'has_data' => $totalLandingValueRp > 0,
            ],
            'catch_records_count' => [
                'value' => $catchRecordsCount,
                'formatted' => number_format($catchRecordsCount, 0, ',', '.'),
                'unit' => 'Catatan',
                'label' => 'Jumlah Catatan Tangkapan',
                'description' => 'Total baris data tangkapan (catches) terverifikasi.',
                'period' => $filters['period_label'],
                'has_data' => $catchRecordsCount > 0,
            ],
            'species_count' => [
                'value' => $distinctSpeciesCount,
                'formatted' => number_format($distinctSpeciesCount, 0, ',', '.'),
                'unit' => 'Spesies',
                'label' => 'Variasi Jenis Ikan',
                'description' => 'Jumlah jenis spesies ikan yang tertangkap pada periode terpilih.',
                'period' => $filters['period_label'],
                'has_data' => $distinctSpeciesCount > 0,
            ],
            'cpue' => [
                'value' => $cpuePerHour,
                'formatted' => number_format($cpuePerHour, 2, ',', '.'),
                'unit' => 'kg/jam',
                'label' => 'CPUE (Catch Per Unit Effort)',
                'description' => 'Rasio tangkapan per jam upaya penangkapan (SUM(Catch) / SUM(Effort Hours)).',
                'period' => $filters['period_label'],
                'has_data' => $cpuePerHour > 0,
            ],
            'estimated_production' => [
                'value' => $totalEstimatedKg,
                'formatted' => number_format($totalEstimatedKg, 0, ',', '.'),
                'ton' => round($totalEstimatedKg / 1000, 2),
                'unit' => 'kg',
                'label' => 'Estimasi Produksi (Stage 10)',
                'description' => 'Hasil estimasi produksi total menggunakan model raising factor terverifikasi.',
                'period' => $filters['period_label'],
                'has_data' => $totalEstimatedKg > 0,
            ],
            'monthly_production' => [
                'value' => $totalMonthlyProdKg,
                'formatted' => number_format($totalMonthlyProdKg, 0, ',', '.'),
                'ton' => round($totalMonthlyProdKg / 1000, 2),
                'unit' => 'kg',
                'label' => 'Statistik Produksi Bulanan (Stage 11)',
                'description' => 'Data agregasi resmi bulanan dinas kelautan dan perikanan.',
                'period' => $filters['period_label'],
                'has_data' => $totalMonthlyProdKg > 0,
            ],
        ];
    }

    /**
     * 3. Operational KPIs:
     * - Active Fishermen: Nahkoda / crew aktif pada trip
     * - Active Vessels: Total armada terdaftar & aktif
     * - Fishing Gears Used: Jumlah alat tangkap aktif
     * - WPP Used: Jumlah WPP aktif yang dijelajahi
     */
    public function getOperationalKpi(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);

        $tripQuery = FishingTrip::query();
        $this->applyTripFilters($tripQuery, $filters, 'departure_date');

        $activeCaptains = (int) (clone $tripQuery)->whereNotNull('captain_id')->distinct('captain_id')->count('captain_id');
        $totalCrew = (int) (clone $tripQuery)->sum('crew_count');
        $activeGears = (int) (clone $tripQuery)->whereNotNull('primary_gear_id')->distinct('primary_gear_id')->count('primary_gear_id');
        $activeWpp = (int) (clone $tripQuery)->whereNotNull('wppnri_id')->distinct('wppnri_id')->count('wppnri_id');
        $unmappedTrips = (int) (clone $tripQuery)->whereNull('wppnri_id')->count();

        $totalRegisteredVessels = Vessel::where('is_active', true)->count();

        return [
            'active_captains' => [
                'value' => $activeCaptains,
                'formatted' => number_format($activeCaptains, 0, ',', '.'),
                'unit' => 'Nahkoda',
                'label' => 'Nahkoda Aktif Berlayar',
                'description' => 'Jumlah nahkoda kapal perikanan unik yang memimpin trip penangkapan.',
                'period' => $filters['period_label'],
                'has_data' => $activeCaptains > 0,
            ],
            'total_crew' => [
                'value' => $totalCrew,
                'formatted' => number_format($totalCrew, 0, ',', '.'),
                'unit' => 'ABK / Nelayan',
                'label' => 'Total Tenaga Kerja Nelayan',
                'description' => 'Akumulasi Anak Buah Kapal (ABK) yang terlibat dalam trip penangkapan.',
                'period' => $filters['period_label'],
                'has_data' => $totalCrew > 0,
            ],
            'registered_vessels' => [
                'value' => $totalRegisteredVessels,
                'formatted' => number_format($totalRegisteredVessels, 0, ',', '.'),
                'unit' => 'Armada',
                'label' => 'Total Armada Terdaftar',
                'description' => 'Total kapal nelayan terdaftar dan aktif di pangkalan perikanan wilayah Aceh.',
                'period' => 'Master Data',
                'has_data' => $totalRegisteredVessels > 0,
            ],
            'fishing_gears_used' => [
                'value' => $activeGears,
                'formatted' => number_format($activeGears, 0, ',', '.'),
                'unit' => 'Tipe Alat',
                'label' => 'Variasi Alat Tangkap',
                'description' => 'Jumlah ragam alat penangkap ikan utama yang digunakan armada.',
                'period' => $filters['period_label'],
                'has_data' => $activeGears > 0,
            ],
            'wpp_used' => [
                'value' => $activeWpp,
                'formatted' => number_format($activeWpp, 0, ',', '.'),
                'unit' => 'WPP-NRI',
                'label' => 'WPP Dijelajahi',
                'description' => 'Wilayah Pengelolaan Perikanan Negara Republik Indonesia yang beroperasi (+'.$unmappedTrips.' trip belum terpetakan).',
                'period' => $filters['period_label'],
                'has_data' => $activeWpp > 0,
            ],
        ];
    }

    // =========================================================================
    // STAGE 13.3 — ADVANCED CHARTS
    // =========================================================================

    /**
     * Helper to get database-agnostic month expression for SQL.
     */
    protected function getMonthSql(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', {$column}) AS INTEGER)"
            : "MONTH({$column})";
    }

    /**
     * CHART 1 — PRODUCTION & CATCH TREND
     * Compares Observed Catch (departure_date) vs Landing Production (landing_date) vs Monthly Production (Stage 11).
     */
    public function getProductionTrend(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        // 1. Observed Catch by Month
        $catchQuery = DB::table('catches')
            ->join('fishing_trips', 'catches.fishing_trip_id', '=', 'fishing_trips.id');
        if (! empty($filters['year'])) {
            $catchQuery->whereYear('fishing_trips.departure_date', $filters['year']);
        }
        if (! empty($filters['wppnri_id'])) {
            $catchQuery->where('fishing_trips.wppnri_id', $filters['wppnri_id']);
        }
        if (! empty($filters['landing_site_id'])) {
            $catchQuery->where('fishing_trips.landing_site_id', $filters['landing_site_id']);
        }
        if (! empty($filters['gear_id'])) {
            $catchQuery->where('fishing_trips.primary_gear_id', $filters['gear_id']);
        }
        if (! empty($filters['species_id'])) {
            $catchQuery->where('catches.fish_species_id', $filters['species_id']);
        }

        $catchByMonth = $catchQuery->select(
            DB::raw($this->getMonthSql('fishing_trips.departure_date').' as month'),
            DB::raw('SUM(catches.weight_kg) as total_kg')
        )
            ->groupBy('month')
            ->pluck('total_kg', 'month')
            ->map(fn ($v) => (float) $v)
            ->all();

        // 2. Landing Production by Month
        $landingQuery = DB::table('landing_items')
            ->join('landings', 'landing_items.landing_id', '=', 'landings.id')
            ->leftJoin('fishing_trips', 'landings.fishing_trip_id', '=', 'fishing_trips.id');
        if (! empty($filters['year'])) {
            $landingQuery->whereYear('landings.landing_date', $filters['year']);
        }
        if (! empty($filters['landing_site_id'])) {
            $landingQuery->where('landings.landing_site_id', $filters['landing_site_id']);
        }
        if (! empty($filters['wppnri_id'])) {
            $landingQuery->where('fishing_trips.wppnri_id', $filters['wppnri_id']);
        }
        if (! empty($filters['species_id'])) {
            $landingQuery->where('landing_items.fish_species_id', $filters['species_id']);
        }

        $landingByMonth = $landingQuery->select(
            DB::raw($this->getMonthSql('landings.landing_date').' as month'),
            DB::raw('SUM(landing_items.weight_kg) as total_kg')
        )
            ->groupBy('month')
            ->pluck('total_kg', 'month')
            ->map(fn ($v) => (float) $v)
            ->all();

        // 3. Monthly Production Statistics (Stage 11)
        $monthlyStatQuery = MonthlyProductionStatistic::query();
        if (! empty($filters['year'])) {
            $monthlyStatQuery->where('year', $filters['year']);
        }
        if (! empty($filters['landing_site_id'])) {
            $monthlyStatQuery->where('landing_site_id', $filters['landing_site_id']);
        }
        if (! empty($filters['gear_id'])) {
            $monthlyStatQuery->where('fishing_gear_id', $filters['gear_id']);
        }
        if (! empty($filters['species_id'])) {
            $monthlyStatQuery->where('fish_species_id', $filters['species_id']);
        }
        $monthlyStatByMonth = $monthlyStatQuery->select(
            'month',
            DB::raw('SUM(total_volume_kg) as total_kg')
        )
            ->groupBy('month')
            ->pluck('total_kg', 'month')
            ->map(fn ($v) => (float) $v)
            ->all();

        // Determine active months
        $activeMonths = array_unique(array_merge(
            array_keys($catchByMonth),
            array_keys($landingByMonth),
            array_keys($monthlyStatByMonth)
        ));
        sort($activeMonths);

        if (empty($activeMonths) && ! empty($filters['year'])) {
            // Default show months 1..12 or empty
            $activeMonths = range(1, 12);
        }

        $labels = [];
        $catchData = [];
        $landingData = [];
        $monthlyData = [];

        foreach ($activeMonths as $m) {
            $mInt = (int) $m;
            if ($mInt < 1 || $mInt > 12) {
                continue;
            }
            $labels[] = $monthNames[$mInt] ?? 'Bln '.$mInt;
            $catchData[] = $catchByMonth[$mInt] ?? 0.0;
            $landingData[] = $landingByMonth[$mInt] ?? 0.0;
            $monthlyData[] = $monthlyStatByMonth[$mInt] ?? 0.0;
        }

        $hasData = (array_sum($catchData) + array_sum($landingData) + array_sum($monthlyData)) > 0;

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Observed Catch (Laut)',
                    'data' => $catchData,
                    'borderColor' => '#0284c7', // Ocean Blue
                    'backgroundColor' => 'rgba(2, 132, 199, 0.15)',
                    'unit' => 'kg',
                ],
                [
                    'label' => 'Landing Production (TPI)',
                    'data' => $landingData,
                    'borderColor' => '#10b981', // Emerald
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'unit' => 'kg',
                ],
                [
                    'label' => 'Monthly Production (Dinas)',
                    'data' => $monthlyData,
                    'borderColor' => '#f59e0b', // Amber
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'unit' => 'kg',
                ],
            ],
            'has_data' => $hasData,
        ];
    }

    /**
     * CHART 2 & 3 — CATCH BY SPECIES & COMPOSITION (%)
     */
    public function getCatchBySpecies(array $filters, int $limit = 10): array
    {
        $filters = $this->normalizeFilters($filters);

        $query = DB::table('catches')
            ->join('fishing_trips', 'catches.fishing_trip_id', '=', 'fishing_trips.id')
            ->join('species', 'catches.fish_species_id', '=', 'species.id');

        if (! empty($filters['year'])) {
            $query->whereYear('fishing_trips.departure_date', $filters['year']);
        }
        if (! empty($filters['month'])) {
            $query->whereMonth('fishing_trips.departure_date', $filters['month']);
        }
        if (! empty($filters['wppnri_id'])) {
            $query->where('fishing_trips.wppnri_id', $filters['wppnri_id']);
        }
        if (! empty($filters['landing_site_id'])) {
            $query->where('fishing_trips.landing_site_id', $filters['landing_site_id']);
        }
        if (! empty($filters['gear_id'])) {
            $query->where('fishing_trips.primary_gear_id', $filters['gear_id']);
        }
        if (! empty($filters['species_id'])) {
            $query->where('catches.fish_species_id', $filters['species_id']);
        }

        $speciesData = $query->select(
            'species.id as species_id',
            'species.local_name_id',
            'species.scientific_name',
            'species.fao_code',
            'species.family',
            DB::raw('SUM(catches.weight_kg) as total_weight_kg'),
            DB::raw('COUNT(catches.id) as catch_records')
        )
            ->groupBy('species.id', 'species.local_name_id', 'species.scientific_name', 'species.fao_code', 'species.family')
            ->orderByDesc('total_weight_kg')
            ->limit($limit)
            ->get();

        $totalCatchKg = (float) $speciesData->sum('total_weight_kg');

        $labels = [];
        $data = [];
        $items = [];
        $accumulatedPercent = 0.0;

        foreach ($speciesData as $index => $sp) {
            $weight = (float) $sp->total_weight_kg;
            $percent = $totalCatchKg > 0 ? round(($weight / $totalCatchKg) * 100, 1) : 0;
            $accumulatedPercent += $percent;

            $displayName = ! empty($sp->local_name_id) ? $sp->local_name_id : $sp->scientific_name;
            $labels[] = $displayName;
            $data[] = $weight;

            $items[] = [
                'species_id' => $sp->species_id,
                'local_name' => $sp->local_name_id,
                'scientific_name' => $sp->scientific_name,
                'fao_code' => $sp->fao_code,
                'family' => $sp->family,
                'weight_kg' => $weight,
                'weight_ton' => round($weight / 1000, 2),
                'percent' => $percent,
                'catch_records' => $sp->catch_records,
            ];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'items' => $items,
            'total_kg' => $totalCatchKg,
            'has_data' => count($items) > 0,
        ];
    }

    /**
     * CHART 4 — CPUE TREND (Ratio of sums: SUM(Catch) / SUM(Effort) per month in kg/jam)
     */
    public function getCpueTrend(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        // 1. Catch by month (effort-linked)
        $catchQuery = DB::table('catches')
            ->join('fishing_efforts', 'catches.fishing_effort_id', '=', 'fishing_efforts.id')
            ->join('fishing_trips', 'fishing_efforts.fishing_trip_id', '=', 'fishing_trips.id');

        if (! empty($filters['year'])) {
            $catchQuery->whereYear('fishing_trips.departure_date', $filters['year']);
        }
        if (! empty($filters['wppnri_id'])) {
            $catchQuery->where('fishing_trips.wppnri_id', $filters['wppnri_id']);
        }
        if (! empty($filters['landing_site_id'])) {
            $catchQuery->where('fishing_trips.landing_site_id', $filters['landing_site_id']);
        }
        if (! empty($filters['gear_id'])) {
            $catchQuery->where('fishing_efforts.fishing_gear_id', $filters['gear_id']);
        }
        if (! empty($filters['species_id'])) {
            $catchQuery->where('catches.fish_species_id', $filters['species_id']);
        }

        $catchByMonth = $catchQuery->select(
            DB::raw($this->getMonthSql('fishing_trips.departure_date').' as month'),
            DB::raw('SUM(catches.weight_kg) as total_kg')
        )
            ->groupBy('month')
            ->pluck('total_kg', 'month')
            ->all();

        // 2. Effort duration by month (unduplicated)
        $effortQuery = DB::table('fishing_efforts')
            ->join('fishing_trips', 'fishing_efforts.fishing_trip_id', '=', 'fishing_trips.id');

        if (! empty($filters['year'])) {
            $effortQuery->whereYear('fishing_trips.departure_date', $filters['year']);
        }
        if (! empty($filters['wppnri_id'])) {
            $effortQuery->where('fishing_trips.wppnri_id', $filters['wppnri_id']);
        }
        if (! empty($filters['landing_site_id'])) {
            $effortQuery->where('fishing_trips.landing_site_id', $filters['landing_site_id']);
        }
        if (! empty($filters['gear_id'])) {
            $effortQuery->where('fishing_efforts.fishing_gear_id', $filters['gear_id']);
        }
        if (! empty($filters['species_id'])) {
            $effortQuery->whereExists(function ($sub) use ($filters) {
                $sub->select(DB::raw(1))
                    ->from('catches')
                    ->whereColumn('catches.fishing_effort_id', 'fishing_efforts.id')
                    ->where('catches.fish_species_id', $filters['species_id']);
            });
        }

        $effortByMonth = $effortQuery->select(
            DB::raw($this->getMonthSql('fishing_trips.departure_date').' as month'),
            DB::raw('SUM(fishing_efforts.duration_hours) as total_hours')
        )
            ->groupBy('month')
            ->pluck('total_hours', 'month')
            ->all();

        $activeMonths = array_unique(array_merge(array_keys($catchByMonth), array_keys($effortByMonth)));
        sort($activeMonths);

        $labels = [];
        $cpueData = [];
        $catchData = [];
        $effortData = [];

        foreach ($activeMonths as $m) {
            $mInt = (int) $m;
            if ($mInt < 1 || $mInt > 12) {
                continue;
            }
            $labels[] = $monthNames[$mInt] ?? 'Bln '.$mInt;
            $kg = (float) ($catchByMonth[$mInt] ?? 0);
            $hrs = (float) ($effortByMonth[$mInt] ?? 0);
            $cpue = $hrs > 0 ? round($kg / $hrs, 2) : 0;

            $cpueData[] = $cpue;
            $catchData[] = $kg;
            $effortData[] = $hrs;
        }

        $hasData = array_sum($cpueData) > 0;

        return [
            'labels' => $labels,
            'cpue_data' => $cpueData,
            'catch_data' => $catchData,
            'effort_data' => $effortData,
            'unit' => 'kg/jam',
            'has_data' => $hasData,
        ];
    }

    /**
     * CHART 5 — LENGTH FREQUENCY (fork_length_cm > 0, 5 cm intervals, valid count, min, max)
     */
    public function getLengthFrequency(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);

        $query = DB::table('biological_measurements')
            ->join('samples', 'biological_measurements.sample_id', '=', 'samples.id')
            ->whereNotNull('biological_measurements.fork_length_cm')
            ->where('biological_measurements.fork_length_cm', '>', 0);

        if (! empty($filters['year'])) {
            $query->whereYear('samples.sample_date', $filters['year']);
        }
        if (! empty($filters['month'])) {
            $query->whereMonth('samples.sample_date', $filters['month']);
        }
        if (! empty($filters['landing_site_id'])) {
            $query->where('samples.landing_site_id', $filters['landing_site_id']);
        }
        if (! empty($filters['species_id'])) {
            $query->where('biological_measurements.fish_species_id', $filters['species_id']);
        }
        if (! empty($filters['wppnri_id']) || ! empty($filters['gear_id'])) {
            $query->join('fishing_trips', 'samples.fishing_trip_id', '=', 'fishing_trips.id');
            if (! empty($filters['wppnri_id'])) {
                $query->where('fishing_trips.wppnri_id', $filters['wppnri_id']);
            }
            if (! empty($filters['gear_id'])) {
                $query->where('fishing_trips.primary_gear_id', $filters['gear_id']);
            }
        }

        $lengths = $query->pluck('biological_measurements.fork_length_cm')->map(fn ($v) => (float) $v);
        $validCount = $lengths->count();
        $minLength = $validCount > 0 ? $lengths->min() : null;
        $maxLength = $validCount > 0 ? $lengths->max() : null;

        $bins = [];
        $labels = [];
        $data = [];

        if ($validCount > 0) {
            $minBin = (int) (floor($minLength / 5) * 5);
            $maxBin = (int) (floor($maxLength / 5) * 5);

            for ($b = $minBin; $b <= $maxBin; $b += 5) {
                $key = $b.'–'.($b + 4.9);
                $bins[$key] = 0;
            }

            foreach ($lengths as $len) {
                $binStart = (int) (floor($len / 5) * 5);
                $key = $binStart.'–'.($binStart + 4.9);
                if (! isset($bins[$key])) {
                    $bins[$key] = 0;
                }
                $bins[$key]++;
            }

            $labels = array_keys($bins);
            $data = array_values($bins);
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'valid_count' => $validCount,
            'min_length' => $minLength,
            'max_length' => $maxLength,
            'unit' => 'Ekor',
            'has_data' => $validCount > 0,
        ];
    }

    /**
     * CHART 6 — CATCH BY GEAR (fishing_gears.id, weight_kg, trips, cpue)
     */
    public function getCatchByGear(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);

        $gears = FishingGear::where('is_active', true)->orderBy('name')->get();
        $items = [];
        $labels = [];
        $data = [];
        $totalCatchAllGears = 0.0;

        foreach ($gears as $gear) {
            // Catch query
            $catchQuery = FishCatch::whereHas('fishingTrip', function ($t) use ($gear, $filters) {
                $t->where('primary_gear_id', $gear->id);
                $this->applyTripFilters($t, $filters, 'departure_date');
            });
            if (! empty($filters['species_id'])) {
                $catchQuery->where('fish_species_id', $filters['species_id']);
            }
            $gearCatchKg = (float) $catchQuery->sum('weight_kg');

            // Trips count
            $tripsQuery = FishingTrip::where('primary_gear_id', $gear->id);
            $this->applyTripFilters($tripsQuery, $filters, 'departure_date');
            $tripsCount = $tripsQuery->count();

            // Effort hours
            $effortQuery = FishingEffort::where('fishing_gear_id', $gear->id)
                ->whereHas('fishingTrip', fn ($t) => $this->applyTripFilters($t, $filters, 'departure_date'));
            $effortHours = (float) $effortQuery->sum('duration_hours');

            $cpue = $effortHours > 0 ? round($gearCatchKg / $effortHours, 2) : 0;

            if ($gearCatchKg > 0 || $tripsCount > 0) {
                $totalCatchAllGears += $gearCatchKg;
                $items[] = [
                    'gear_id' => $gear->id,
                    'gear_name' => $gear->name_id ?? $gear->name,
                    'gear_code' => $gear->code,
                    'gear_type' => $gear->gear_type,
                    'catch_kg' => $gearCatchKg,
                    'catch_ton' => round($gearCatchKg / 1000, 2),
                    'trips_count' => $tripsCount,
                    'effort_hours' => round($effortHours, 1),
                    'cpue' => $cpue,
                ];
            }
        }

        // Sort descending by catch
        usort($items, fn ($a, $b) => $b['catch_kg'] <=> $a['catch_kg']);

        foreach ($items as &$item) {
            $item['percent'] = $totalCatchAllGears > 0 ? round(($item['catch_kg'] / $totalCatchAllGears) * 100, 1) : 0;
            $labels[] = $item['gear_name'];
            $data[] = $item['catch_kg'];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'items' => $items,
            'total_kg' => $totalCatchAllGears,
            'has_data' => count($items) > 0,
        ];
    }

    /**
     * CHART 7 — CATCH BY WPP (Transparent WPP NULL labeled as 'Tidak Terpetakan')
     */
    public function getCatchByWpp(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);

        $wppList = Wppnri::orderBy('name')->get();
        $items = [];
        $labels = [];
        $data = [];
        $totalCatchAllWpp = 0.0;

        foreach ($wppList as $wpp) {
            $catchQuery = FishCatch::whereHas('fishingTrip', function ($t) use ($wpp, $filters) {
                $t->where('wppnri_id', $wpp->id);
                $this->applyTripFilters($t, $filters, 'departure_date');
            });
            if (! empty($filters['species_id'])) {
                $catchQuery->where('fish_species_id', $filters['species_id']);
            }
            if (! empty($filters['gear_id'])) {
                $catchQuery->whereHas('fishingTrip', fn ($t) => $t->where('primary_gear_id', $filters['gear_id']));
            }
            $wppCatchKg = (float) $catchQuery->sum('weight_kg');

            $tripsCount = FishingTrip::where('wppnri_id', $wpp->id)
                ->when(true, fn ($q) => $this->applyTripFilters($q, $filters, 'departure_date'))
                ->count();

            $effortHours = (float) FishingEffort::whereHas('fishingTrip', function ($t) use ($wpp, $filters) {
                $t->where('wppnri_id', $wpp->id);
                $this->applyTripFilters($t, $filters, 'departure_date');
            })->sum('duration_hours');

            $cpue = $effortHours > 0 ? round($wppCatchKg / $effortHours, 2) : 0;

            if ($wppCatchKg > 0 || $tripsCount > 0) {
                $totalCatchAllWpp += $wppCatchKg;
                $items[] = [
                    'wpp_id' => $wpp->id,
                    'wpp_code' => $wpp->code,
                    'wpp_name' => $wpp->name,
                    'catch_kg' => $wppCatchKg,
                    'catch_ton' => round($wppCatchKg / 1000, 2),
                    'trips_count' => $tripsCount,
                    'effort_hours' => round($effortHours, 1),
                    'cpue' => $cpue,
                ];
            }
        }

        // Tangani data tanpa WPP (WPP NULL -> 'Tidak Terpetakan')
        $unmappedCatchQuery = FishCatch::whereHas('fishingTrip', function ($t) use ($filters) {
            $t->whereNull('wppnri_id');
            $this->applyTripFilters($t, $filters, 'departure_date');
        });
        if (! empty($filters['species_id'])) {
            $unmappedCatchQuery->where('fish_species_id', $filters['species_id']);
        }
        if (! empty($filters['gear_id'])) {
            $unmappedCatchQuery->whereHas('fishingTrip', fn ($t) => $t->where('primary_gear_id', $filters['gear_id']));
        }
        $unmappedCatchKg = (float) $unmappedCatchQuery->sum('weight_kg');

        $unmappedTrips = FishingTrip::whereNull('wppnri_id')
            ->when(true, fn ($q) => $this->applyTripFilters($q, $filters, 'departure_date'))
            ->count();

        $unmappedEffortHours = (float) FishingEffort::whereHas('fishingTrip', function ($t) use ($filters) {
            $t->whereNull('wppnri_id');
            $this->applyTripFilters($t, $filters, 'departure_date');
        })->sum('duration_hours');

        $unmappedCpue = $unmappedEffortHours > 0 ? round($unmappedCatchKg / $unmappedEffortHours, 2) : 0;

        if ($unmappedCatchKg > 0 || $unmappedTrips > 0) {
            $totalCatchAllWpp += $unmappedCatchKg;
            $items[] = [
                'wpp_id' => null,
                'wpp_code' => '-',
                'wpp_name' => 'Tidak Terpetakan',
                'catch_kg' => $unmappedCatchKg,
                'catch_ton' => round($unmappedCatchKg / 1000, 2),
                'trips_count' => $unmappedTrips,
                'effort_hours' => round($unmappedEffortHours, 1),
                'cpue' => $unmappedCpue,
            ];
        }

        foreach ($items as &$item) {
            $item['percent'] = $totalCatchAllWpp > 0 ? round(($item['catch_kg'] / $totalCatchAllWpp) * 100, 1) : 0;
            $labels[] = $item['wpp_name'];
            $data[] = $item['catch_kg'];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'items' => $items,
            'total_kg' => $totalCatchAllWpp,
            'has_data' => count($items) > 0,
        ];
    }

    /**
     * CHART 8 — FISHING EFFORT (Effort hours & setting count per gear)
     */
    public function getFishingEffortChart(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);

        $effortQuery = DB::table('fishing_efforts')
            ->join('fishing_trips', 'fishing_efforts.fishing_trip_id', '=', 'fishing_trips.id')
            ->join('fishing_gears', 'fishing_efforts.fishing_gear_id', '=', 'fishing_gears.id');

        if (! empty($filters['year'])) {
            $effortQuery->whereYear('fishing_trips.departure_date', $filters['year']);
        }
        if (! empty($filters['month'])) {
            $effortQuery->whereMonth('fishing_trips.departure_date', $filters['month']);
        }
        if (! empty($filters['wppnri_id'])) {
            $effortQuery->where('fishing_trips.wppnri_id', $filters['wppnri_id']);
        }
        if (! empty($filters['landing_site_id'])) {
            $effortQuery->where('fishing_trips.landing_site_id', $filters['landing_site_id']);
        }
        if (! empty($filters['gear_id'])) {
            $effortQuery->where('fishing_efforts.fishing_gear_id', $filters['gear_id']);
        }

        $effortData = $effortQuery->select(
            'fishing_gears.name_id as gear_name',
            DB::raw('COUNT(fishing_efforts.id) as setting_count'),
            DB::raw('SUM(fishing_efforts.duration_hours) as total_hours')
        )
            ->groupBy('fishing_gears.id', 'fishing_gears.name_id')
            ->orderByDesc('total_hours')
            ->get();

        $labels = [];
        $hoursData = [];
        $settingsData = [];

        foreach ($effortData as $row) {
            $labels[] = $row->gear_name;
            $hoursData[] = round((float) $row->total_hours, 1);
            $settingsData[] = (int) $row->setting_count;
        }

        $hasData = count($labels) > 0;

        return [
            'labels' => $labels,
            'hours_data' => $hoursData,
            'settings_data' => $settingsData,
            'has_data' => $hasData,
        ];
    }

    // =========================================================================
    // STAGE 13.4 — MULTI-DIMENSIONAL ANALYSIS
    // =========================================================================

    /**
     * Multi-Dimensional Cross Analysis:
     * - Species x Gear
     * - Species x WPP
     * - Gear x WPP
     * - Species x Month
     *
     * Executed with single grouped queries to prevent N+1 queries.
     */
    public function getMultiDimensionalAnalysis(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);

        // 1. Species x Gear
        $spGearQuery = DB::table('catches')
            ->join('fishing_trips', 'catches.fishing_trip_id', '=', 'fishing_trips.id')
            ->join('species', 'catches.fish_species_id', '=', 'species.id')
            ->join('fishing_gears', 'fishing_trips.primary_gear_id', '=', 'fishing_gears.id');

        if (! empty($filters['year'])) {
            $spGearQuery->whereYear('fishing_trips.departure_date', $filters['year']);
        }
        if (! empty($filters['month'])) {
            $spGearQuery->whereMonth('fishing_trips.departure_date', $filters['month']);
        }
        if (! empty($filters['wppnri_id'])) {
            $spGearQuery->where('fishing_trips.wppnri_id', $filters['wppnri_id']);
        }
        if (! empty($filters['landing_site_id'])) {
            $spGearQuery->where('fishing_trips.landing_site_id', $filters['landing_site_id']);
        }

        $speciesXGear = $spGearQuery->select(
            'species.id as species_id',
            'species.local_name_id',
            'species.scientific_name',
            'fishing_gears.id as gear_id',
            'fishing_gears.name_id as gear_name',
            DB::raw('SUM(catches.weight_kg) as total_catch_kg'),
            DB::raw('COUNT(catches.id) as catch_records')
        )
            ->groupBy('species.id', 'species.local_name_id', 'species.scientific_name', 'fishing_gears.id', 'fishing_gears.name_id')
            ->orderByDesc('total_catch_kg')
            ->limit(15)
            ->get();

        // 2. Species x WPP (including unmapped)
        $spWppQuery = DB::table('catches')
            ->join('fishing_trips', 'catches.fishing_trip_id', '=', 'fishing_trips.id')
            ->join('species', 'catches.fish_species_id', '=', 'species.id')
            ->leftJoin('wppnri', 'fishing_trips.wppnri_id', '=', 'wppnri.id');

        if (! empty($filters['year'])) {
            $spWppQuery->whereYear('fishing_trips.departure_date', $filters['year']);
        }
        if (! empty($filters['month'])) {
            $spWppQuery->whereMonth('fishing_trips.departure_date', $filters['month']);
        }
        if (! empty($filters['landing_site_id'])) {
            $spWppQuery->where('fishing_trips.landing_site_id', $filters['landing_site_id']);
        }

        $speciesXWpp = $spWppQuery->select(
            'species.id as species_id',
            'species.local_name_id',
            'species.scientific_name',
            'wppnri.id as wpp_id',
            DB::raw("COALESCE(wppnri.name, 'Tidak Terpetakan') as wpp_name"),
            DB::raw('SUM(catches.weight_kg) as total_catch_kg'),
            DB::raw('COUNT(catches.id) as catch_records')
        )
            ->groupBy('species.id', 'species.local_name_id', 'species.scientific_name', 'wppnri.id', 'wppnri.name')
            ->orderByDesc('total_catch_kg')
            ->limit(15)
            ->get();

        // 3. Gear x WPP
        $gearWppQuery = DB::table('catches')
            ->join('fishing_trips', 'catches.fishing_trip_id', '=', 'fishing_trips.id')
            ->join('fishing_gears', 'fishing_trips.primary_gear_id', '=', 'fishing_gears.id')
            ->leftJoin('wppnri', 'fishing_trips.wppnri_id', '=', 'wppnri.id');

        if (! empty($filters['year'])) {
            $gearWppQuery->whereYear('fishing_trips.departure_date', $filters['year']);
        }
        if (! empty($filters['month'])) {
            $gearWppQuery->whereMonth('fishing_trips.departure_date', $filters['month']);
        }

        $gearXWpp = $gearWppQuery->select(
            'fishing_gears.id as gear_id',
            'fishing_gears.name_id as gear_name',
            'wppnri.id as wpp_id',
            DB::raw("COALESCE(wppnri.name, 'Tidak Terpetakan') as wpp_name"),
            DB::raw('SUM(catches.weight_kg) as total_catch_kg'),
            DB::raw('COUNT(DISTINCT fishing_trips.id) as trips_count')
        )
            ->groupBy('fishing_gears.id', 'fishing_gears.name_id', 'wppnri.id', 'wppnri.name')
            ->orderByDesc('total_catch_kg')
            ->limit(15)
            ->get();

        // 4. Species x Month (Top 5 species seasonal trend)
        $topSpeciesQuery = DB::table('catches')
            ->join('fishing_trips', 'catches.fishing_trip_id', '=', 'fishing_trips.id')
            ->join('species', 'catches.fish_species_id', '=', 'species.id');

        if (! empty($filters['year'])) {
            $topSpeciesQuery->whereYear('fishing_trips.departure_date', $filters['year']);
        }
        if (! empty($filters['landing_site_id'])) {
            $topSpeciesQuery->where('fishing_trips.landing_site_id', $filters['landing_site_id']);
        }
        if (! empty($filters['wppnri_id'])) {
            $topSpeciesQuery->where('fishing_trips.wppnri_id', $filters['wppnri_id']);
        }

        $topSpeciesIds = $topSpeciesQuery
            ->select('species.id')
            ->groupBy('species.id')
            ->orderByRaw('SUM(catches.weight_kg) DESC')
            ->limit(5)
            ->pluck('species.id')
            ->all();

        $speciesXMonth = [];
        if (! empty($topSpeciesIds)) {
            $spMonthQuery = DB::table('catches')
                ->join('fishing_trips', 'catches.fishing_trip_id', '=', 'fishing_trips.id')
                ->join('species', 'catches.fish_species_id', '=', 'species.id')
                ->whereIn('species.id', $topSpeciesIds);

            if (! empty($filters['year'])) {
                $spMonthQuery->whereYear('fishing_trips.departure_date', $filters['year']);
            }

            $spMonthRows = $spMonthQuery->select(
                'species.id as species_id',
                'species.local_name_id',
                'species.scientific_name',
                DB::raw($this->getMonthSql('fishing_trips.departure_date').' as month'),
                DB::raw('SUM(catches.weight_kg) as total_catch_kg')
            )
                ->groupBy('species.id', 'species.local_name_id', 'species.scientific_name', 'month')
                ->orderBy('month')
                ->get();

            foreach ($spMonthRows as $row) {
                $name = ! empty($row->local_name_id) ? $row->local_name_id : $row->scientific_name;
                $speciesXMonth[$name][$row->month] = (float) $row->total_catch_kg;
            }
        }

        return [
            'species_x_gear' => $speciesXGear,
            'species_x_wpp' => $speciesXWpp,
            'gear_x_wpp' => $gearXWpp,
            'species_x_month' => $speciesXMonth,
            'has_data' => count($speciesXGear) > 0 || count($speciesXWpp) > 0,
        ];
    }

    /**
     * Master Data Options for Filter Engine
     */
    public function getFilterOptions(): array
    {
        $years = DB::table('fishing_trips')
            ->whereNotNull('departure_date')
            ->select(DB::raw('DISTINCT '.(DB::connection()->getDriverName() === 'sqlite' ? "CAST(strftime('%Y', departure_date) AS INTEGER)" : 'YEAR(departure_date)').' as year'))
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->all();

        if (empty($years)) {
            $years = [2026, 2025];
        }

        $wppList = Wppnri::orderBy('name')->get();
        $landingSites = LandingSite::where('is_active', true)->orderBy('name')->get();
        $gears = FishingGear::where('is_active', true)->orderBy('name')->get();
        $topSpecies = Species::whereHas('catches')
            ->orderBy('local_name_id')
            ->limit(30)
            ->get();

        return [
            'years' => $years,
            'wpp_list' => $wppList,
            'landing_sites' => $landingSites,
            'gears' => $gears,
            'top_species' => $topSpecies,
        ];
    }

    /**
     * Engine komprehensif untuk Portal Publik Statistik & Analisis CPUE (/statistik).
     *
     * Menghitung rasio baku CPUE (kg/jam & kg/trip), agregasi bulanan,
     * matriks per alat tangkap (ISSCFG), per spesies, dan tabel tabular terinci.
     *
     * @param  array<string, mixed>  $rawFilters
     * @return array<string, mixed>
     */
    public function getPublicCpueDashboardData(array $rawFilters = []): array
    {
        $year = isset($rawFilters['tahun']) && $rawFilters['tahun'] !== '' ? (int) $rawFilters['tahun'] : (isset($rawFilters['year']) && $rawFilters['year'] !== '' ? (int) $rawFilters['year'] : null);
        $month = isset($rawFilters['bulan']) && $rawFilters['bulan'] !== '' ? (int) $rawFilters['bulan'] : (isset($rawFilters['month']) && $rawFilters['month'] !== '' ? (int) $rawFilters['month'] : null);

        $wppnriId = null;
        $wppInput = $rawFilters['wppnri_id'] ?? ($rawFilters['wilayah'] ?? null);
        if (! empty($wppInput)) {
            if (is_numeric($wppInput)) {
                $wppnriId = (int) $wppInput;
            } else {
                $wppnriId = DB::table('wppnri')->where('code', $wppInput)->value('id');
            }
        }

        $landingSiteId = ! empty($rawFilters['landing_site_id']) ? (int) $rawFilters['landing_site_id'] : (! empty($rawFilters['site']) ? (int) $rawFilters['site'] : null);
        $gearId = ! empty($rawFilters['fishing_gear_id']) ? (int) $rawFilters['fishing_gear_id'] : (! empty($rawFilters['gear_id']) ? (int) $rawFilters['gear_id'] : (! empty($rawFilters['gear']) ? (int) $rawFilters['gear'] : null));
        $family = ! empty($rawFilters['family']) ? trim((string) $rawFilters['family']) : null;

        $speciesId = null;
        $speciesInput = $rawFilters['species_id'] ?? ($rawFilters['species'] ?? null);
        if (! empty($speciesInput)) {
            if (is_numeric($speciesInput)) {
                $speciesId = (int) $speciesInput;
            } else {
                $speciesId = DB::table('species')
                    ->where('fao_code', $speciesInput)
                    ->orWhere('scientific_name', $speciesInput)
                    ->orWhere('local_name_id', $speciesInput)
                    ->value('id');
            }
        }

        $normalizedFilters = [
            'year' => $year,
            'month' => $month,
            'wppnri_id' => $wppnriId,
            'landing_site_id' => $landingSiteId,
            'gear_id' => $gearId,
            'fishing_gear_id' => $gearId,
            'family' => $family,
            'species_id' => $speciesId,
        ];

        // 1. Base Query for Trips
        $tripQuery = DB::table('fishing_trips')
            ->leftJoin('landings', 'fishing_trips.id', '=', 'landings.fishing_trip_id');

        if ($year) {
            $tripQuery->whereYear('fishing_trips.departure_date', $year);
        }
        if ($month) {
            $tripQuery->whereMonth('fishing_trips.departure_date', $month);
        }
        if ($wppnriId) {
            $tripQuery->where('fishing_trips.wppnri_id', $wppnriId);
        }
        if ($landingSiteId) {
            $tripQuery->where(function ($q) use ($landingSiteId) {
                $q->where('fishing_trips.landing_site_id', $landingSiteId)
                    ->orWhere('landings.landing_site_id', $landingSiteId);
            });
        }
        if ($gearId) {
            $tripQuery->where(function ($q) use ($gearId) {
                $q->where('fishing_trips.primary_gear_id', $gearId)
                    ->orWhereExists(function ($sub) use ($gearId) {
                        $sub->select(DB::raw(1))->from('fishing_efforts')
                            ->whereColumn('fishing_efforts.fishing_trip_id', 'fishing_trips.id')
                            ->where('fishing_efforts.fishing_gear_id', $gearId);
                    });
            });
        }
        if ($speciesId) {
            $tripQuery->whereExists(function ($sub) use ($speciesId) {
                $sub->select(DB::raw(1))->from('catches')
                    ->whereColumn('catches.fishing_trip_id', 'fishing_trips.id')
                    ->where('catches.fish_species_id', $speciesId);
            });
        }
        if ($family) {
            $tripQuery->whereExists(function ($sub) use ($family) {
                $sub->select(DB::raw(1))->from('catches')
                    ->join('species', 'catches.fish_species_id', '=', 'species.id')
                    ->whereColumn('catches.fishing_trip_id', 'fishing_trips.id')
                    ->where('species.family', $family);
            });
        }

        $totalTrips = (int) (clone $tripQuery)->distinct('fishing_trips.id')->count('fishing_trips.id');
        $totalVessels = (int) (clone $tripQuery)->whereNotNull('fishing_trips.vessel_id')->distinct('fishing_trips.vessel_id')->count('fishing_trips.vessel_id');

        // 2. Base Query for Catches (All catches tied to trips)
        $catchQuery = DB::table('catches')
            ->join('fishing_trips', 'catches.fishing_trip_id', '=', 'fishing_trips.id')
            ->leftJoin('fishing_efforts', 'catches.fishing_effort_id', '=', 'fishing_efforts.id')
            ->leftJoin('landings', 'fishing_trips.id', '=', 'landings.fishing_trip_id')
            ->join('species', 'catches.fish_species_id', '=', 'species.id');

        if ($year) {
            $catchQuery->whereYear('fishing_trips.departure_date', $year);
        }
        if ($month) {
            $catchQuery->whereMonth('fishing_trips.departure_date', $month);
        }
        if ($wppnriId) {
            $catchQuery->where('fishing_trips.wppnri_id', $wppnriId);
        }
        if ($landingSiteId) {
            $catchQuery->where(function ($q) use ($landingSiteId) {
                $q->where('fishing_trips.landing_site_id', $landingSiteId)
                    ->orWhere('landings.landing_site_id', $landingSiteId);
            });
        }
        if ($gearId) {
            $catchQuery->where(function ($q) use ($gearId) {
                $q->where('fishing_efforts.fishing_gear_id', $gearId)
                    ->orWhere(function ($sub) use ($gearId) {
                        $sub->whereNull('catches.fishing_effort_id')
                            ->where('fishing_trips.primary_gear_id', $gearId);
                    });
            });
        }
        if ($speciesId) {
            $catchQuery->where('catches.fish_species_id', $speciesId);
        }
        if ($family) {
            $catchQuery->where('species.family', $family);
        }

        $totalCatchKg = (float) (clone $catchQuery)->sum('catches.weight_kg');
        $totalSpeciesCount = (int) (clone $catchQuery)->distinct('catches.fish_species_id')->count('catches.fish_species_id');

        // 3. Base Query for Unduplicated Efforts
        $effortQuery = DB::table('fishing_efforts')
            ->join('fishing_trips', 'fishing_efforts.fishing_trip_id', '=', 'fishing_trips.id')
            ->leftJoin('landings', 'fishing_trips.id', '=', 'landings.fishing_trip_id');

        if ($year) {
            $effortQuery->whereYear('fishing_trips.departure_date', $year);
        }
        if ($month) {
            $effortQuery->whereMonth('fishing_trips.departure_date', $month);
        }
        if ($wppnriId) {
            $effortQuery->where('fishing_trips.wppnri_id', $wppnriId);
        }
        if ($landingSiteId) {
            $effortQuery->where(function ($q) use ($landingSiteId) {
                $q->where('fishing_trips.landing_site_id', $landingSiteId)
                    ->orWhere('landings.landing_site_id', $landingSiteId);
            });
        }
        if ($gearId) {
            $effortQuery->where('fishing_efforts.fishing_gear_id', $gearId);
        }
        if ($speciesId) {
            $effortQuery->whereExists(function ($sub) use ($speciesId) {
                $sub->select(DB::raw(1))->from('catches')
                    ->whereColumn('catches.fishing_effort_id', 'fishing_efforts.id')
                    ->where('catches.fish_species_id', $speciesId);
            });
        }
        if ($family) {
            $effortQuery->whereExists(function ($sub) use ($family) {
                $sub->select(DB::raw(1))->from('catches')
                    ->join('species', 'catches.fish_species_id', '=', 'species.id')
                    ->whereColumn('catches.fishing_effort_id', 'fishing_efforts.id')
                    ->where('species.family', $family);
            });
        }

        $totalEffortHours = (float) (clone $effortQuery)->sum('fishing_efforts.duration_hours');
        $totalSettings = (int) (clone $effortQuery)->count('fishing_efforts.id');

        // Global CPUE
        $cpueKgPerHour = $totalEffortHours > 0 ? round($totalCatchKg / $totalEffortHours, 2) : null;
        $cpueKgPerTrip = $totalTrips > 0 ? round($totalCatchKg / $totalTrips, 2) : null;
        $hasData = ($totalCatchKg > 0 || $totalTrips > 0);

        // 4. Monthly Trend Data (Only for months that have data)
        $monthSql = $this->getMonthSql('fishing_trips.departure_date');

        $monthlyCatchMap = (clone $catchQuery)
            ->select(DB::raw("{$monthSql} as m"), DB::raw('SUM(catches.weight_kg) as total_kg'))
            ->groupBy('m')
            ->pluck('total_kg', 'm')
            ->map(fn ($v) => (float) $v)
            ->all();

        $monthlyEffortRows = (clone $effortQuery)
            ->select(
                DB::raw("{$monthSql} as m"),
                DB::raw('SUM(fishing_efforts.duration_hours) as total_hours'),
                DB::raw('COUNT(DISTINCT fishing_trips.id) as trips_count')
            )
            ->groupBy('m')
            ->get()
            ->keyBy('m');

        $activeMonths = array_unique(array_merge(array_keys($monthlyCatchMap), array_keys($monthlyEffortRows->all())));
        sort($activeMonths);

        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $monthlyLabels = [];
        $monthlyCatchData = [];
        $monthlyEffortData = [];
        $monthlyTripData = [];
        $monthlyCpueHourData = [];
        $monthlyCpueTripData = [];

        foreach ($activeMonths as $m) {
            $mInt = (int) $m;
            if ($mInt < 1 || $mInt > 12) {
                continue;
            }
            $label = $monthNames[$mInt] ?? 'Bln '.$mInt;
            $kg = (float) ($monthlyCatchMap[$mInt] ?? 0.0);
            $effortRow = $monthlyEffortRows[$mInt] ?? null;
            $hrs = (float) ($effortRow?->total_hours ?? 0.0);
            $trips = (int) ($effortRow?->trips_count ?? 0);

            $cpueH = $hrs > 0 ? round($kg / $hrs, 2) : 0.0;
            $cpueT = $trips > 0 ? round($kg / $trips, 2) : 0.0;

            $monthlyLabels[] = $label;
            $monthlyCatchData[] = $kg;
            $monthlyEffortData[] = $hrs;
            $monthlyTripData[] = $trips;
            $monthlyCpueHourData[] = $cpueH;
            $monthlyCpueTripData[] = $cpueT;
        }

        // Backward compatibility structures for existing views
        $landingTrend = [
            'labels' => $monthlyLabels,
            'data' => $monthlyCatchData,
        ];
        $cpueTrend = [
            'labels' => $monthlyLabels,
            'data' => $monthlyCpueHourData,
        ];
        $monthlyCpue = [
            'labels' => $monthlyLabels,
            'catch_data' => $monthlyCatchData,
            'effort_data' => $monthlyEffortData,
            'trip_data' => $monthlyTripData,
            'cpue_hour_data' => $monthlyCpueHourData,
            'cpue_trip_data' => $monthlyCpueTripData,
            'has_data' => count($monthlyLabels) > 0,
        ];

        // 5. CPUE per Fishing Gear Table
        $gearsQuery = FishingGear::where('is_active', true);
        if ($gearId) {
            $gearsQuery->where('id', $gearId);
        }
        $availableGears = $gearsQuery->orderBy('name_id')->get();

        $gearCpueTable = [];
        $gearChartLabels = [];
        $gearChartData = [];
        $gearChartItems = [];

        foreach ($availableGears as $g) {
            $gearCatchQuery = clone $catchQuery;
            $gearCatchQuery->where(function ($q) use ($g) {
                $q->where('fishing_efforts.fishing_gear_id', $g->id)
                    ->orWhere(function ($sub) use ($g) {
                        $sub->whereNull('catches.fishing_effort_id')
                            ->where('fishing_trips.primary_gear_id', $g->id);
                    });
            });
            $cKg = (float) $gearCatchQuery->sum('catches.weight_kg');

            $gearEffortQuery = clone $effortQuery;
            $gearEffortQuery->where('fishing_efforts.fishing_gear_id', $g->id);
            $eHrs = (float) $gearEffortQuery->sum('fishing_efforts.duration_hours');
            $eSettings = (int) $gearEffortQuery->count('fishing_efforts.id');

            $gearTrips = (int) (clone $tripQuery)->where(function ($q) use ($g) {
                $q->where('fishing_trips.primary_gear_id', $g->id)
                    ->orWhereExists(function ($sub) use ($g) {
                        $sub->select(DB::raw(1))->from('fishing_efforts')
                            ->whereColumn('fishing_efforts.fishing_trip_id', 'fishing_trips.id')
                            ->where('fishing_efforts.fishing_gear_id', $g->id);
                    });
            })->distinct('fishing_trips.id')->count('fishing_trips.id');

            // Skip if no catch and no effort
            if ($cKg <= 0 && $eHrs <= 0 && $gearTrips <= 0) {
                continue;
            }

            $cpueH = $eHrs > 0 ? round($cKg / $eHrs, 2) : 0.0;
            $cpueT = $gearTrips > 0 ? round($cKg / $gearTrips, 2) : 0.0;
            $pct = $totalCatchKg > 0 ? round(($cKg / $totalCatchKg) * 100, 1) : 0.0;

            $item = [
                'gear_id' => $g->id,
                'gear_name' => $g->name_id ?: $g->name,
                'isscfg_code' => $g->isscfg_code ?: '-',
                'category' => $g->category,
                'catch_kg' => $cKg,
                'catch_ton' => round($cKg / 1000, 2),
                'percent' => $pct,
                'effort_hours' => $eHrs,
                'settings' => $eSettings,
                'trips' => $gearTrips,
                'cpue_hour' => $cpueH,
                'cpue_trip' => $cpueT,
            ];

            $gearCpueTable[] = $item;
            $gearChartLabels[] = $item['gear_name'];
            $gearChartData[] = $cKg;
            $gearChartItems[] = [
                'gear_id' => $g->id,
                'gear_name' => $item['gear_name'],
                'catch_kg' => $cKg,
            ];
        }

        usort($gearCpueTable, fn ($a, $b) => $b['catch_kg'] <=> $a['catch_kg']);

        $catchByGear = [
            'labels' => $gearChartLabels,
            'data' => $gearChartData,
            'items' => $gearChartItems,
            'unit' => 'kg',
        ];

        // 6. CPUE per Species Table & Catch Composition
        $speciesQuery = clone $catchQuery;
        $speciesRows = $speciesQuery->select(
            'species.id as species_id',
            'species.local_name_id',
            'species.scientific_name',
            'species.fao_code',
            'species.family',
            DB::raw('SUM(catches.weight_kg) as total_weight_kg'),
            DB::raw('COUNT(catches.id) as catch_records')
        )
            ->groupBy('species.id', 'species.local_name_id', 'species.scientific_name', 'species.fao_code', 'species.family')
            ->orderByDesc('total_weight_kg')
            ->limit(20)
            ->get();

        $speciesCpueTable = [];
        $speciesChartLabels = [];
        $speciesChartData = [];

        foreach ($speciesRows as $sp) {
            $w = (float) $sp->total_weight_kg;
            $name = ! empty($sp->local_name_id) ? $sp->local_name_id : $sp->scientific_name;
            $pct = $totalCatchKg > 0 ? round(($w / $totalCatchKg) * 100, 1) : 0.0;

            // Effort associated with this species
            $spEffortHrs = (float) (clone $effortQuery)->whereExists(function ($sub) use ($sp) {
                $sub->select(DB::raw(1))->from('catches')
                    ->whereColumn('catches.fishing_effort_id', 'fishing_efforts.id')
                    ->where('catches.fish_species_id', $sp->species_id);
            })->sum('fishing_efforts.duration_hours');

            $spTrips = (int) (clone $tripQuery)->whereExists(function ($sub) use ($sp) {
                $sub->select(DB::raw(1))->from('catches')
                    ->whereColumn('catches.fishing_trip_id', 'fishing_trips.id')
                    ->where('catches.fish_species_id', $sp->species_id);
            })->distinct('fishing_trips.id')->count('fishing_trips.id');

            $cpueH = $spEffortHrs > 0 ? round($w / $spEffortHrs, 2) : 0.0;
            $cpueT = $spTrips > 0 ? round($w / $spTrips, 2) : 0.0;

            $speciesCpueTable[] = [
                'species_id' => $sp->species_id,
                'species_name' => $name,
                'scientific_name' => $sp->scientific_name,
                'fao_code' => $sp->fao_code,
                'family' => $sp->family ?: '-',
                'catch_kg' => $w,
                'catch_ton' => round($w / 1000, 2),
                'percent' => $pct,
                'effort_hours' => $spEffortHrs,
                'trips' => $spTrips,
                'cpue_hour' => $cpueH,
                'cpue_trip' => $cpueT,
                'records' => $sp->catch_records,
            ];

            if (count($speciesChartLabels) < 10) {
                $speciesChartLabels[] = $name;
                $speciesChartData[] = $w;
            }
        }

        $speciesCatch = [
            'labels' => $speciesChartLabels,
            'data' => $speciesChartData,
        ];

        // 7. Detailed Statistics Matrix (Tahun, Bulan, Gear, Species, Catch, Effort, CPUE)
        $detailQuery = DB::table('catches')
            ->join('fishing_trips', 'catches.fishing_trip_id', '=', 'fishing_trips.id')
            ->leftJoin('fishing_efforts', 'catches.fishing_effort_id', '=', 'fishing_efforts.id')
            ->leftJoin('fishing_gears', function ($join) {
                $join->on('fishing_efforts.fishing_gear_id', '=', 'fishing_gears.id')
                    ->orWhere(function ($sub) {
                        $sub->whereNull('catches.fishing_effort_id')
                            ->whereColumn('fishing_trips.primary_gear_id', 'fishing_gears.id');
                    });
            })
            ->join('species', 'catches.fish_species_id', '=', 'species.id')
            ->leftJoin('landings', 'fishing_trips.id', '=', 'landings.fishing_trip_id');

        if ($year) {
            $detailQuery->whereYear('fishing_trips.departure_date', $year);
        }
        if ($month) {
            $detailQuery->whereMonth('fishing_trips.departure_date', $month);
        }
        if ($wppnriId) {
            $detailQuery->where('fishing_trips.wppnri_id', $wppnriId);
        }
        if ($landingSiteId) {
            $detailQuery->where(function ($q) use ($landingSiteId) {
                $q->where('fishing_trips.landing_site_id', $landingSiteId)
                    ->orWhere('landings.landing_site_id', $landingSiteId);
            });
        }
        if ($gearId) {
            $detailQuery->where(function ($q) use ($gearId) {
                $q->where('fishing_efforts.fishing_gear_id', $gearId)
                    ->orWhere(function ($sub) use ($gearId) {
                        $sub->whereNull('catches.fishing_effort_id')
                            ->where('fishing_trips.primary_gear_id', $gearId);
                    });
            });
        }
        if ($speciesId) {
            $detailQuery->where('catches.fish_species_id', $speciesId);
        }
        if ($family) {
            $detailQuery->where('species.family', $family);
        }

        $yearSql = DB::connection()->getDriverName() === 'sqlite' ? "CAST(strftime('%Y', fishing_trips.departure_date) AS INTEGER)" : 'YEAR(fishing_trips.departure_date)';
        $detailedRows = $detailQuery->select(
            DB::raw("{$yearSql} as year"),
            DB::raw("{$monthSql} as month"),
            'fishing_gears.name_id as gear_name',
            'fishing_gears.isscfg_code',
            'species.local_name_id as species_name',
            'species.scientific_name',
            'species.fao_code',
            DB::raw('SUM(catches.weight_kg) as catch_kg'),
            DB::raw('COUNT(DISTINCT fishing_trips.id) as trips_count'),
            DB::raw('COALESCE(SUM(DISTINCT fishing_efforts.duration_hours), 0) as effort_hours')
        )
            ->groupBy(
                'year', 'month',
                'fishing_gears.name_id', 'fishing_gears.isscfg_code',
                'species.local_name_id', 'species.scientific_name', 'species.fao_code'
            )
            ->orderByDesc('year')
            ->orderBy('month')
            ->orderBy('gear_name')
            ->orderByDesc('catch_kg')
            ->limit(150)
            ->get();

        $monthLongNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $detailedTable = [];
        foreach ($detailedRows as $row) {
            $cKg = (float) $row->catch_kg;
            $eHrs = (float) $row->effort_hours;
            $tCount = (int) $row->trips_count;
            $cpueH = $eHrs > 0 ? round($cKg / $eHrs, 2) : 0.0;
            $cpueT = $tCount > 0 ? round($cKg / $tCount, 2) : 0.0;

            $detailedTable[] = [
                'year' => (int) $row->year,
                'month' => (int) $row->month,
                'month_name' => $monthLongNames[(int) $row->month] ?? 'Bulan '.(int) $row->month,
                'gear_name' => $row->gear_name ?: 'Alat Tangkap Umum',
                'isscfg_code' => $row->isscfg_code ?: '-',
                'species_name' => $row->species_name ?: $row->scientific_name,
                'scientific_name' => $row->scientific_name,
                'fao_code' => $row->fao_code ?: '-',
                'catch_kg' => $cKg,
                'effort_hours' => $eHrs,
                'trips_count' => $tCount,
                'cpue_hour' => $cpueH,
                'cpue_trip' => $cpueT,
            ];
        }

        // 8. Length Frequency & Catch by WPP
        $lengthFrequency = $this->getLengthFrequency($normalizedFilters);
        $catchByWpp = $this->getCatchByWpp($normalizedFilters);

        // 9. GIS Fishing Ground & Locations
        $mapQuery = DB::table('fishing_efforts')
            ->join('fishing_trips', 'fishing_efforts.fishing_trip_id', '=', 'fishing_trips.id')
            ->leftJoin('fishing_gears', 'fishing_efforts.fishing_gear_id', '=', 'fishing_gears.id')
            ->leftJoin('wppnri', 'fishing_trips.wppnri_id', '=', 'wppnri.id')
            ->leftJoin('landings', 'fishing_trips.id', '=', 'landings.fishing_trip_id')
            ->whereNotNull('fishing_efforts.latitude_setting')
            ->whereNotNull('fishing_efforts.longitude_setting')
            ->whereBetween('fishing_efforts.latitude_setting', [-90, 90])
            ->whereBetween('fishing_efforts.longitude_setting', [-180, 180])
            ->where('fishing_efforts.latitude_setting', '!=', 0)
            ->where('fishing_efforts.longitude_setting', '!=', 0);

        if ($year) {
            $mapQuery->whereYear('fishing_trips.departure_date', $year);
        }
        if ($month) {
            $mapQuery->whereMonth('fishing_trips.departure_date', $month);
        }
        if ($wppnriId) {
            $mapQuery->where('fishing_trips.wppnri_id', $wppnriId);
        }
        if ($landingSiteId) {
            $mapQuery->where(function ($q) use ($landingSiteId) {
                $q->where('fishing_trips.landing_site_id', $landingSiteId)
                    ->orWhere('landings.landing_site_id', $landingSiteId);
            });
        }
        if ($gearId) {
            $mapQuery->where('fishing_efforts.fishing_gear_id', $gearId);
        }
        if ($speciesId) {
            $mapQuery->whereExists(function ($sub) use ($speciesId) {
                $sub->select(DB::raw(1))->from('catches')
                    ->whereColumn('catches.fishing_effort_id', 'fishing_efforts.id')
                    ->where('catches.fish_species_id', $speciesId);
            });
        }
        if ($family) {
            $mapQuery->whereExists(function ($sub) use ($family) {
                $sub->select(DB::raw(1))->from('catches')
                    ->join('species', 'catches.fish_species_id', '=', 'species.id')
                    ->whereColumn('catches.fishing_effort_id', 'fishing_efforts.id')
                    ->where('species.family', $family);
            });
        }

        $catchSubquery = 'SELECT COALESCE(SUM(c.weight_kg), 0) FROM catches c WHERE c.fishing_effort_id = fishing_efforts.id';
        if ($speciesId) {
            $catchSubquery .= ' AND c.fish_species_id = '.(int) $speciesId;
        }

        $mapData = $mapQuery->select(
            'fishing_efforts.id as effort_id',
            'fishing_efforts.latitude_setting as latitude',
            'fishing_efforts.longitude_setting as longitude',
            'fishing_trips.id as trip_id',
            'fishing_trips.trip_number',
            'fishing_gears.name_id as gear_name',
            'wppnri.code as wpp_code',
            'wppnri.name as wpp_name',
            DB::raw('('.$catchSubquery.') as total_catch')
        )
            ->orderBy('fishing_efforts.id')
            ->get();

        $fishingGround = ['points' => []];
        $fishingLocations = [];
        foreach ($mapData as $data) {
            $point = [
                'effort_id' => $data->effort_id,
                'latitude' => (float) $data->latitude,
                'longitude' => (float) $data->longitude,
                'lat' => (float) $data->latitude,
                'lng' => (float) $data->longitude,
                'trip_id' => $data->trip_id,
                'trip_number' => $data->trip_number,
                'gear_name' => $data->gear_name ?? '-',
                'wpp_code' => ! empty($data->wpp_code) ? ('WPP-'.$data->wpp_code) : '-',
                'wpp_name' => $data->wpp_name ?? '-',
                'catch_kg' => (float) $data->total_catch,
                'catch' => (float) $data->total_catch,
                'name' => 'Lokasi Fishing Effort',
            ];
            $fishingGround['points'][] = $point;
            $fishingLocations[] = $point;
        }

        return [
            'trip_count' => $totalTrips,
            'vessel_count' => $totalVessels,
            'catch_weight' => $totalCatchKg,
            'catch_ton' => round($totalCatchKg / 1000, 2),
            'species_count' => $totalSpeciesCount,
            'effort_hours' => $totalEffortHours,
            'setting_count' => $totalSettings,
            'cpue_kg_per_hour' => $cpueKgPerHour,
            'cpue_kg_per_trip' => $cpueKgPerTrip,
            'has_data' => $hasData,
            'filters' => [
                'tahun' => $year,
                'bulan' => $month,
                'wppnri_id' => $wppnriId,
                'landing_site_id' => $landingSiteId,
                'fishing_gear_id' => $gearId,
                'family' => $family,
                'species_id' => $speciesId,
            ],
            'landing_trend' => $landingTrend,
            'cpue_trend' => $cpueTrend,
            'monthly_cpue' => $monthlyCpue,
            'species_catch' => $speciesCatch,
            'length_frequency' => $lengthFrequency,
            'catch_by_gear' => $catchByGear,
            'catch_by_wpp' => $catchByWpp,
            'gear_cpue_table' => $gearCpueTable,
            'species_cpue_table' => $speciesCpueTable,
            'detailed_table' => $detailedTable,
            'fishing_ground' => $fishingGround,
            'fishing_locations' => $fishingLocations,
        ];
    }
}

<?php

namespace App\Http\Controllers\Analysis;

use App\Http\Controllers\Controller;
use App\Models\LandingSite;
use App\Models\MonthlyProductionStatistic;
use App\Services\AdvancedStatisticService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatisticController extends Controller
{
    public function __construct(
        protected AdvancedStatisticService $statisticService
    ) {}

    /**
     * Tampilkan dashboard analitik statistik perikanan terpadu:
     * - Stage 13.2 Advanced KPI (Activity, Catch, Operational)
     * - Stage 13.3 Advanced 8 Charts (Trend, Species, Composition, CPUE, Length Freq, Gear, WPP, Effort)
     * - Stage 13.4 Multi-Dimensional Analysis (Species x Gear, Species x WPP, Gear x WPP, Species x Month)
     * - Stage 13.5 Unified Filter Engine
     * - Stage 13.6 Executive & Detail UX
     */
    public function index(Request $request): View
    {
        $rawFilters = $request->only([
            'year', 'month', 'start_date', 'end_date',
            'wppnri_id', 'landing_site_id', 'gear_id', 'species_id',
        ]);

        if (empty($rawFilters['year']) && empty($rawFilters['start_date'])) {
            $rawFilters['year'] = 2026;
        }

        $filters = $this->statisticService->normalizeFilters($rawFilters);
        $year = $filters['year'];
        $month = $filters['month'];
        $wppnriId = $filters['wppnri_id'];
        $landingSiteId = $filters['landing_site_id'];
        $gearId = $filters['gear_id'];
        $speciesId = $filters['species_id'];

        // 1. KPI Groups
        $activityKpis = $this->statisticService->getActivityKpi($filters);
        $catchKpis = $this->statisticService->getCatchKpi($filters);
        $operationalKpis = $this->statisticService->getOperationalKpi($filters);

        // Backward compatibility bindings
        $totalCatchKg = $catchKpis['total_catch_physical']['value'];
        $totalCatchTon = $catchKpis['total_catch_physical']['ton'];
        $totalTrips = $activityKpis['total_trips']['value'];
        $totalSettings = $activityKpis['fishing_efforts']['value'];
        $totalDurationHours = $activityKpis['total_effort_hours']['value'];

        $cpuePerHour = $catchKpis['cpue']['value'];
        $cpuePerTrip = $totalTrips > 0 ? round($totalCatchKg / $totalTrips, 2) : 0;
        $cpuePerSetting = $totalSettings > 0 ? round($totalCatchKg / $totalSettings, 2) : 0;

        $totalProductionKg = $catchKpis['total_landing']['value'];
        $totalProductionTon = $catchKpis['total_landing']['ton'];
        $totalProductionValueRp = $catchKpis['total_landing_value']['value'];
        $avgPricePerKg = $totalProductionKg > 0 ? round($totalProductionValueRp / $totalProductionKg, 2) : 0;

        $totalEstimatedKg = $catchKpis['estimated_production']['value'];
        $totalEstimatedTon = $catchKpis['estimated_production']['ton'];

        // 2. Advanced 8 Charts
        $trendChart = $this->statisticService->getProductionTrend($filters);
        $speciesChart = $this->statisticService->getCatchBySpecies($filters, 10);
        $cpueTrend = $this->statisticService->getCpueTrend($filters);
        $lengthFrequency = $this->statisticService->getLengthFrequency($filters);
        $gearChart = $this->statisticService->getCatchByGear($filters);
        $wppChart = $this->statisticService->getCatchByWpp($filters);
        $effortChart = $this->statisticService->getFishingEffortChart($filters);

        // Backward compatibility structures for existing view loops
        $gearCpueData = $gearChart['items'];
        $speciesBreakdown = collect($speciesChart['items'])->map(function ($item) {
            return [
                'species' => (object) [
                    'id' => $item['species_id'],
                    'local_name_id' => $item['local_name'],
                    'scientific_name' => $item['scientific_name'],
                    'fao_code' => $item['fao_code'],
                ],
                'catch_kg' => $item['weight_kg'],
                'percent' => $item['percent'],
            ];
        });
        $wppBreakdown = $wppChart['items'];

        // Pelabuhan / Landing site breakdown
        $siteBreakdown = LandingSite::withSum(['landings as total_landed_kg' => function ($q) use ($year, $month, $wppnriId) {
            if ($year) {
                $q->whereYear('landing_date', $year);
            }
            if ($month) {
                $q->whereMonth('landing_date', $month);
            }
            if ($wppnriId) {
                $q->whereHas('fishingTrip', fn ($t) => $t->where('wppnri_id', $wppnriId));
            }
        }], 'total_weight_kg')
            ->withSum(['landings as total_revenue_rp' => function ($q) use ($year, $month, $wppnriId) {
                if ($year) {
                    $q->whereYear('landing_date', $year);
                }
                if ($month) {
                    $q->whereMonth('landing_date', $month);
                }
                if ($wppnriId) {
                    $q->whereHas('fishingTrip', fn ($t) => $t->where('wppnri_id', $wppnriId));
                }
            }], 'total_value_rp')
            ->whereHas('landings')
            ->orderByDesc('total_landed_kg')
            ->get();

        // 3. Multi-Dimensional Cross Analysis (Stage 13.4)
        $multiDimensional = $this->statisticService->getMultiDimensionalAnalysis($filters);

        // 4. Tabel Rekapitulasi Statistik Produksi Bulanan (Stage 11)
        $statsQuery = MonthlyProductionStatistic::with([
            'regency',
            'landingSite',
            'fishSpecies',
            'fishingGear',
        ])
            ->when($year, fn ($q) => $q->where('year', $year))
            ->when($month, fn ($q) => $q->where('month', $month))
            ->when($landingSiteId, fn ($q) => $q->where('landing_site_id', $landingSiteId))
            ->when($gearId, fn ($q) => $q->where('fishing_gear_id', $gearId))
            ->when($speciesId, fn ($q) => $q->where('fish_species_id', $speciesId))
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('total_volume_kg');

        $monthlyStats = $statsQuery->paginate(10)->withQueryString();

        $aggVolumeKg = MonthlyProductionStatistic::when($year, fn ($q) => $q->where('year', $year))->sum('total_volume_kg');
        $aggValueRp = MonthlyProductionStatistic::when($year, fn ($q) => $q->where('year', $year))->sum('total_value_rp');
        $aggTrips = MonthlyProductionStatistic::when($year, fn ($q) => $q->where('year', $year))->sum('total_trips');
        $aggVessels = MonthlyProductionStatistic::when($year, fn ($q) => $q->where('year', $year))->max('total_active_vessels');
        $aggCpue = $aggTrips > 0 ? round($aggVolumeKg / $aggTrips, 2) : 0;

        // 5. Master Filters Options
        $filterOptions = $this->statisticService->getFilterOptions();
        $landingSites = $filterOptions['landing_sites'];
        $gearsList = $filterOptions['gears'];
        $wppList = $filterOptions['wpp_list'];

        return view('analysis.statistics.index', compact(
            'filters',
            'filterOptions',
            'year',
            'month',
            'landingSiteId',
            'gearId',
            'speciesId',
            'wppnriId',
            'activityKpis',
            'catchKpis',
            'operationalKpis',
            'totalCatchKg',
            'totalCatchTon',
            'totalTrips',
            'totalSettings',
            'totalDurationHours',
            'cpuePerTrip',
            'cpuePerSetting',
            'cpuePerHour',
            'totalProductionKg',
            'totalProductionTon',
            'totalProductionValueRp',
            'avgPricePerKg',
            'totalEstimatedKg',
            'totalEstimatedTon',
            'gearCpueData',
            'speciesBreakdown',
            'siteBreakdown',
            'wppBreakdown',
            'monthlyStats',
            'aggVolumeKg',
            'aggValueRp',
            'aggTrips',
            'aggVessels',
            'aggCpue',
            'landingSites',
            'gearsList',
            'wppList',
            'trendChart',
            'speciesChart',
            'cpueTrend',
            'lengthFrequency',
            'gearChart',
            'wppChart',
            'effortChart',
            'multiDimensional'
        ));
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdvancedStatisticService;
use App\Services\MonthlyProductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticsApiController extends Controller
{
    public function __construct(
        protected AdvancedStatisticService $statisticService,
        protected MonthlyProductionService $productionService
    ) {}

    /**
     * GET /api/statistics/kpi
     *
     * Mengembalikan KPI aktivitas, tangkapan, dan operasional dalam format JSON.
     */
    public function kpi(Request $request): JsonResponse
    {
        $rawFilters = $request->only([
            'year', 'month', 'start_date', 'end_date',
            'wppnri_id', 'landing_site_id', 'gear_id', 'species_id',
        ]);

        if (empty($rawFilters['year']) && empty($rawFilters['start_date'])) {
            $rawFilters['year'] = now()->year;
        }

        $filters = $this->statisticService->normalizeFilters($rawFilters);

        return response()->json([
            'status' => 'ok',
            'period' => $filters['period_label'],
            'data' => [
                'activity' => $this->statisticService->getActivityKpi($filters),
                'catch' => $this->statisticService->getCatchKpi($filters),
                'operational' => $this->statisticService->getOperationalKpi($filters),
            ],
        ]);
    }

    /**
     * GET /api/statistics/production
     *
     * Mengembalikan ringkasan produksi multi-dimensi (Landing, Observed, Estimated, Monthly).
     */
    public function production(Request $request): JsonResponse
    {
        $filters = $request->only([
            'year', 'month', 'landing_site_id', 'gear_id', 'species_id', 'wppnri_id',
        ]);

        if (empty($filters['year'])) {
            $filters['year'] = now()->year;
        }

        $summary = $this->productionService->getProductionSummary($filters);

        return response()->json([
            'status' => 'ok',
            'data' => $summary,
        ]);
    }

    /**
     * GET /api/statistics/trend
     *
     * Mengembalikan data tren produksi bulanan (Observed Catch vs Landing vs Monthly).
     */
    public function trend(Request $request): JsonResponse
    {
        $rawFilters = $request->only([
            'year', 'month', 'wppnri_id', 'landing_site_id', 'gear_id', 'species_id',
        ]);

        if (empty($rawFilters['year'])) {
            $rawFilters['year'] = now()->year;
        }

        $filters = $this->statisticService->normalizeFilters($rawFilters);
        $trend = $this->statisticService->getProductionTrend($filters);

        return response()->json([
            'status' => 'ok',
            'period' => $filters['period_label'],
            'data' => $trend,
        ]);
    }

    /**
     * GET /api/statistics/cpue
     *
     * Mengembalikan data tren CPUE bulanan.
     */
    public function cpue(Request $request): JsonResponse
    {
        $rawFilters = $request->only([
            'year', 'month', 'wppnri_id', 'landing_site_id', 'gear_id', 'species_id',
        ]);

        if (empty($rawFilters['year'])) {
            $rawFilters['year'] = now()->year;
        }

        $filters = $this->statisticService->normalizeFilters($rawFilters);
        $cpueTrend = $this->statisticService->getCpueTrend($filters);

        return response()->json([
            'status' => 'ok',
            'period' => $filters['period_label'],
            'data' => $cpueTrend,
        ]);
    }
}

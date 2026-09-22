<?php

namespace App\Http\Controllers;

use App\Services\AdvancedStatisticService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected AdvancedStatisticService $statisticService
    ) {}

    /**
     * Tampilkan Ringkasan Dashboard Analitik & Operasional Terpadu
     */
    public function index(Request $request): View
    {
        $rawFilters = $request->only([
            'year', 'month', 'start_date', 'end_date',
            'wppnri_id', 'landing_site_id', 'gear_id', 'species_id',
        ]);

        // Default ke tahun 2026 jika tidak ada filter tahun
        if (empty($rawFilters['year']) && empty($rawFilters['start_date'])) {
            $rawFilters['year'] = 2026;
        }

        $filters = $this->statisticService->normalizeFilters($rawFilters);

        // Ambil metrik terstandarisasi
        $activityKpis = $this->statisticService->getActivityKpi($filters);
        $catchKpis = $this->statisticService->getCatchKpi($filters);
        $operationalKpis = $this->statisticService->getOperationalKpi($filters);

        // Chart ringkasan untuk dashboard eksekutif
        $trendChart = $this->statisticService->getProductionTrend($filters);
        $speciesChart = $this->statisticService->getCatchBySpecies($filters, 6);
        $cpueTrend = $this->statisticService->getCpueTrend($filters);
        $wppChart = $this->statisticService->getCatchByWpp($filters);

        // Opsi filter
        $filterOptions = $this->statisticService->getFilterOptions();

        return view('dashboard', compact(
            'filters',
            'activityKpis',
            'catchKpis',
            'operationalKpis',
            'trendChart',
            'speciesChart',
            'cpueTrend',
            'wppChart',
            'filterOptions'
        ));
    }
}

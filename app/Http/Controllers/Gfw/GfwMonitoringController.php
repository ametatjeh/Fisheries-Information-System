<?php

namespace App\Http\Controllers\Gfw;

use App\Http\Controllers\Controller;
use App\Services\Gfw\GfwActivityService;
use App\Services\Gfw\GfwRegionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GfwMonitoringController extends Controller
{
    public function __construct(
        protected GfwRegionService $regionService
    ) {}

    /**
     * Display the GFW Vessel Monitoring GIS map view.
     */
    public function index(Request $request): View
    {
        $regions = $this->regionService->getSupportedRegions();
        $selectedRegionKey = (string) $request->query('region', 'aceh_waters');
        $selectedRegion = $this->regionService->getRegion($selectedRegionKey) ?? $this->regionService->getAcehRegion();

        $timezone = config('app.timezone', 'Asia/Jakarta');
        $defaultEnd = Carbon::now($timezone)->subDays(3)->toDateString();
        $defaultStart = Carbon::now($timezone)->subDays(17)->toDateString();

        $startDate = $request->query('start_date', $defaultStart);
        $endDate = $request->query('end_date', $defaultEnd);

        $latencyNotice = GfwActivityService::LATENCY_NOTICE;

        return view('gfw.monitoring', compact(
            'regions',
            'selectedRegion',
            'selectedRegionKey',
            'startDate',
            'endDate',
            'latencyNotice'
        ));
    }
}

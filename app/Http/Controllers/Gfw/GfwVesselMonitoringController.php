<?php

namespace App\Http\Controllers\Gfw;

use App\Http\Controllers\Controller;
use App\Services\Gfw\AoiService;
use App\Services\Gfw\GfwActivityService;
use App\Services\Gfw\GfwRegionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class GfwVesselMonitoringController extends Controller
{
    public function __construct(
        protected AoiService $aoiService,
        protected GfwRegionService $regionService
    ) {}

    /**
     * Display the GFW Vessel Monitoring workspace view.
     */
    public function index(Request $request): View
    {
        $timezone = (string) config('app.timezone', 'Asia/Jakarta');
        $defaultEnd = Carbon::now($timezone)->subDays(3)->toDateString();
        $defaultStart = Carbon::now($timezone)->subDays(10)->toDateString();

        $startDate = (string) $request->query('start_date', $defaultStart);
        $endDate = (string) $request->query('end_date', $defaultEnd);

        $aoiSummary = [
            'name' => 'ZEE Indonesia - Kawasan Aceh',
            'geometry_type' => 'Polygon',
            'crs' => 'EPSG:4326',
            'feature_count' => 1,
        ];

        try {
            $aoiSummary = $this->aoiService->getZeeIndonesiaAcehSummary();
        } catch (Throwable) {
            // Fallback gracefully
        }

        $bufferSummary = null;
        try {
            $bufferSummary = $this->aoiService->getZeeIndonesiaAcehBuffer100NmSummary();
        } catch (Throwable) {
            // Fallback gracefully
        }

        $latencyNotice = GfwActivityService::LATENCY_NOTICE;

        return view('gfw.vessels', compact(
            'startDate',
            'endDate',
            'aoiSummary',
            'bufferSummary',
            'latencyNotice'
        ));
    }
}

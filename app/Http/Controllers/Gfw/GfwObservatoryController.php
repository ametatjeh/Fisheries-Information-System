<?php

namespace App\Http\Controllers\Gfw;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gfw\ObservatoryVesselRequest;
use App\Services\Gfw\AoiService;
use App\Services\Gfw\GfwActivityService;
use App\Services\Gfw\GfwObservatoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Throwable;

class GfwObservatoryController extends Controller
{
    public function __construct(
        protected GfwObservatoryService $observatoryService,
        protected AoiService $aoiService
    ) {}

    /**
     * Display the GFW Vessel Observatory page.
     */
    public function index(): View
    {
        $stats = $this->observatoryService->getObservatoryStats();
        $syncStatus = $this->observatoryService->getSyncStatus();

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

        $latencyNotice = GfwActivityService::LATENCY_NOTICE;

        return view('gfw.observatory', compact(
            'stats',
            'syncStatus',
            'aoiSummary',
            'latencyNotice'
        ));
    }

    /**
     * Paginated list of ingested GFW vessels from sistem_gfw.
     */
    public function vessels(ObservatoryVesselRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);

        $vessels = $this->observatoryService->getVessels($filters, $perPage);

        return $this->successResponse($vessels->items(), [
            'total' => $vessels->total(),
            'per_page' => $vessels->perPage(),
            'current_page' => $vessels->currentPage(),
            'last_page' => $vessels->lastPage(),
        ]);
    }

    /**
     * Single vessel detail by GFW vessel ID.
     */
    public function vesselShow(string $gfwVesselId): JsonResponse
    {
        $cleanId = trim($gfwVesselId);
        if ($cleanId === '') {
            return $this->errorResponse('Identifier vessel tidak valid.', 400);
        }

        $result = $this->observatoryService->getVessel($cleanId);

        if (! $result) {
            return $this->errorResponse('Vessel tidak ditemukan dalam data Observatory.', 404);
        }

        return $this->successResponse($result, [
            'gfw_vessel_id' => $cleanId,
        ]);
    }

    /**
     * Presence history for a specific vessel.
     */
    public function vesselPresence(string $gfwVesselId, ObservatoryVesselRequest $request): JsonResponse
    {
        $cleanId = trim($gfwVesselId);
        if ($cleanId === '') {
            return $this->errorResponse('Identifier vessel tidak valid.', 400);
        }

        $filters = $request->validated();
        $perPage = (int) ($filters['per_page'] ?? 50);

        $presence = $this->observatoryService->getVesselPresence($cleanId, $filters, $perPage);

        return $this->successResponse($presence->items(), [
            'gfw_vessel_id' => $cleanId,
            'total' => $presence->total(),
            'per_page' => $presence->perPage(),
            'current_page' => $presence->currentPage(),
            'last_page' => $presence->lastPage(),
        ]);
    }

    /**
     * Aggregated observatory statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = $this->observatoryService->getObservatoryStats();

        return $this->successResponse($stats);
    }

    /**
     * Recent sync run history.
     */
    public function syncRuns(): JsonResponse
    {
        $runs = $this->observatoryService->getSyncRuns();

        return $this->successResponse($runs->toArray(), [
            'total' => $runs->count(),
        ]);
    }

    /**
     * Current sync status and health.
     */
    public function syncStatus(): JsonResponse
    {
        $status = $this->observatoryService->getSyncStatus();

        return $this->successResponse($status, [
            'health' => $status['health'],
        ]);
    }

    /**
     * Standard successful JSON response envelope.
     *
     * @param  array<string, mixed>  $meta
     */
    protected function successResponse(mixed $data, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'source' => 'gfw_observatory',
            'data' => $data,
            'meta' => $meta,
        ], $status);
    }

    /**
     * Standard error JSON response envelope.
     */
    protected function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'source' => 'gfw_observatory',
            'error' => $message,
            'data' => [],
        ], $status);
    }
}

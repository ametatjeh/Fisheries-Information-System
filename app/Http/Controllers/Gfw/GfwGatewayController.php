<?php

namespace App\Http\Controllers\Gfw;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gfw\ActivityQueryRequest;
use App\Http\Requests\Gfw\EventQueryRequest;
use App\Http\Requests\Gfw\VesselSearchRequest;
use App\Services\Gfw\GfwActivityService;
use App\Services\Gfw\GfwApiService;
use App\Services\Gfw\GfwEventService;
use App\Services\Gfw\GfwRegionService;
use App\Services\Gfw\GfwVesselService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GfwGatewayController extends Controller
{
    public function __construct(
        protected GfwApiService $apiService,
        protected GfwVesselService $vesselService,
        protected GfwActivityService $activityService,
        protected GfwEventService $eventService,
        protected GfwRegionService $regionService
    ) {}

    /**
     * Search vessels on GFW API with caching and normalization.
     */
    public function vessels(VesselSearchRequest $request): JsonResponse
    {
        if (! $this->apiService->isConfigured()) {
            return $this->errorResponse('GFW API key is not configured.', 503);
        }

        $query = (string) ($request->validated('query') ?? '');
        $limit = (int) ($request->validated('limit') ?? 20);
        $refresh = $request->boolean('refresh');

        $result = $this->vesselService->search($query, [
            'limit' => $limit,
            'refresh' => $refresh,
        ]);

        if (! $result['success']) {
            return $this->errorResponse($result['error'] ?? 'GFW Vessel search failed.', 502);
        }

        return $this->successResponse($result['data'], [
            'count' => count($result['data']),
            'total' => $result['total'] ?? count($result['data']),
            'cached' => $result['cached'] ?? false,
            'query' => $query,
        ]);
    }

    /**
     * Get single vessel identity by GFW Vessel ID.
     */
    public function vesselShow(string $id, Request $request): JsonResponse
    {
        $cleanId = trim($id);
        if ($cleanId === '') {
            return $this->errorResponse('Identifier vessel tidak valid.', 400);
        }

        if (! $this->apiService->isConfigured()) {
            return $this->errorResponse('GFW API key is not configured.', 503);
        }

        $refresh = $request->boolean('refresh');
        $vessel = $this->vesselService->getById($cleanId, $refresh);

        if (! $vessel) {
            return $this->errorResponse('Vessel tidak ditemukan pada data GFW.', 404);
        }

        return $this->successResponse($vessel, [
            'gfw_vessel_id' => $cleanId,
            'cached' => ! $refresh,
        ]);
    }

    /**
     * Query regional vessel presence observations.
     */
    public function activity(ActivityQueryRequest $request): JsonResponse
    {
        if (! $this->apiService->isConfigured()) {
            return $this->errorResponse('GFW API key is not configured.', 503);
        }

        $region = (string) ($request->validated('region') ?? 'indonesia_eez');
        $startDate = $request->validated('start_date');
        $endDate = $request->validated('end_date');
        $limit = (int) ($request->validated('limit') ?? 20);
        $refresh = $request->boolean('refresh');

        $result = $this->activityService->getVesselPresence($region, [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'limit' => $limit,
            'refresh' => $refresh,
        ]);

        if (! $result['success']) {
            $status = str_contains($result['error'] ?? '', 'Region') ? 404 : 502;

            return $this->errorResponse($result['error'] ?? 'Gagal mengambil data kehadiran kapal.', $status);
        }

        return $this->successResponse($result['data'], [
            'region' => $result['region'] ?? null,
            'query_period' => $result['query_period'] ?? null,
            'total' => $result['total'] ?? count($result['data']),
            'cached' => $result['cached'] ?? false,
            'latency_notice' => $result['latency_notice'] ?? GfwActivityService::LATENCY_NOTICE,
        ]);
    }

    /**
     * Query activity tracks and points for a single GFW vessel.
     */
    public function vesselActivity(string $id, ActivityQueryRequest $request): JsonResponse
    {
        $cleanId = trim($id);
        if ($cleanId === '') {
            return $this->errorResponse('Identifier vessel tidak valid.', 400);
        }

        if (! $this->apiService->isConfigured()) {
            return $this->errorResponse('GFW API key is not configured.', 503);
        }

        $startDate = $request->validated('start_date');
        $endDate = $request->validated('end_date');
        $refresh = $request->boolean('refresh');

        $result = $this->activityService->getVesselActivity($cleanId, [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'refresh' => $refresh,
        ]);

        if (! $result['success']) {
            $isValidationError = isset($result['error']) && (
                str_contains($result['error'], 'date') ||
                str_contains($result['error'], 'tanggal') ||
                str_contains($result['error'], 'Rentang') ||
                str_contains($result['error'], 'Format')
            );
            $status = $isValidationError ? 422 : 502;

            return $this->errorResponse($result['error'] ?? 'Gagal mengambil lintasan aktivitas kapal.', $status);
        }

        return $this->successResponse($result['data'], [
            'gfw_vessel_id' => $cleanId,
            'query_period' => $result['query_period'] ?? null,
            'total' => $result['total'] ?? count($result['data']),
            'cached' => $result['cached'] ?? false,
            'latency_notice' => $result['latency_notice'] ?? GfwActivityService::LATENCY_NOTICE,
        ]);
    }

    /**
     * Query GFW Events with optional event type filtering.
     */
    public function events(EventQueryRequest $request): JsonResponse
    {
        $type = (string) ($request->validated('type') ?? 'apparent_fishing');

        return match ($type) {
            'potential_encounter', 'encounter' => $this->eventEncounters($request),
            'loitering' => $this->eventLoitering($request),
            'port_visit', 'port-visits' => $this->eventPortVisits($request),
            default => $this->eventFishing($request),
        };
    }

    /**
     * Query Apparent Fishing Events.
     */
    public function eventFishing(EventQueryRequest $request): JsonResponse
    {
        return $this->runEventQuery(fn ($opts) => $this->eventService->fishingEvents($opts), $request);
    }

    /**
     * Query Potential Encounters.
     */
    public function eventEncounters(EventQueryRequest $request): JsonResponse
    {
        return $this->runEventQuery(fn ($opts) => $this->eventService->encounters($opts), $request);
    }

    /**
     * Query Loitering Events.
     */
    public function eventLoitering(EventQueryRequest $request): JsonResponse
    {
        return $this->runEventQuery(fn ($opts) => $this->eventService->loitering($opts), $request);
    }

    /**
     * Query Port Visits.
     */
    public function eventPortVisits(EventQueryRequest $request): JsonResponse
    {
        return $this->runEventQuery(fn ($opts) => $this->eventService->portVisits($opts), $request);
    }

    /**
     * Get single GFW event detail by Event ID.
     */
    public function eventShow(string $id, Request $request): JsonResponse
    {
        $cleanId = trim($id);
        if ($cleanId === '') {
            return $this->errorResponse('Event ID tidak valid.', 400);
        }

        if (! $this->apiService->isConfigured()) {
            return $this->errorResponse('GFW API key is not configured.', 503);
        }

        $refresh = $request->boolean('refresh');
        $event = $this->eventService->getEventById($cleanId, ['refresh' => $refresh]);

        if (! $event) {
            return $this->errorResponse('Event tidak ditemukan pada data GFW.', 404);
        }

        return $this->successResponse($event, [
            'gfw_event_id' => $cleanId,
            'semantic_label' => $event['semantic_label'] ?? 'GFW Event',
            'semantic_disclaimer' => $event['semantic_disclaimer'] ?? '',
        ]);
    }

    /**
     * List all supported geographic and maritime regions for GFW queries.
     */
    public function regions(): JsonResponse
    {
        $regions = $this->regionService->getSupportedRegions();

        return $this->successResponse(array_values($regions), [
            'total' => count($regions),
        ]);
    }

    /**
     * Show geographic specifications for a specific region.
     */
    public function regionShow(string $key, Request $request): JsonResponse
    {
        $region = $this->regionService->getRegion($key);
        if (! $region) {
            return $this->errorResponse("Region [{$key}] tidak ditemukan atau belum didukung.", 404);
        }

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $queryParams = $this->regionService->buildQueryParams($region['key'], array_filter([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]));

        return $this->successResponse(array_merge($region, $queryParams), [
            'region_key' => $region['key'],
            'region_type' => $region['type'],
        ]);
    }

    /**
     * Validate custom coordinates, bounding box, or GeoJSON polygon.
     */
    public function validateGeometry(Request $request): JsonResponse
    {
        if ($request->has(['latitude', 'longitude'])) {
            $lat = (float) $request->input('latitude');
            $lon = (float) $request->input('longitude');
            $valid = $this->regionService->validateCoordinates($lat, $lon);

            return $valid
                ? response()->json([
                    'success' => true,
                    'source' => 'global_fishing_watch',
                    'valid' => true,
                    'type' => 'coordinate_pair',
                    'data' => ['latitude' => $lat, 'longitude' => $lon],
                    'meta' => ['type' => 'coordinate_pair', 'valid' => true],
                ], 200)
                : $this->errorResponse('Koordinat tidak valid (-90 s/d 90 untuk latitude, -180 s/d 180 untuk longitude).', 422);
        }

        if ($request->has('bounding_box')) {
            $bbox = $request->input('bounding_box');
            if (! is_array($bbox)) {
                return $this->errorResponse('Parameter bounding_box harus berupa array 4 elemen.', 422);
            }

            $result = $this->regionService->validateBoundingBox($bbox);

            return $result['valid']
                ? response()->json([
                    'success' => true,
                    'source' => 'global_fishing_watch',
                    'valid' => true,
                    'type' => 'bounding_box',
                    'data' => $result['bbox'],
                    'meta' => ['type' => 'bounding_box', 'valid' => true],
                ], 200)
                : $this->errorResponse($result['error'] ?? 'Bounding box tidak valid.', 422);
        }

        if ($request->has('geojson')) {
            $geojson = $request->input('geojson');
            if (! is_array($geojson)) {
                return $this->errorResponse('Parameter geojson harus berupa object JSON representasi polygon.', 422);
            }

            $result = $this->regionService->validateGeoJsonPolygon($geojson);

            return $result['valid']
                ? response()->json([
                    'success' => true,
                    'source' => 'global_fishing_watch',
                    'valid' => true,
                    'type' => 'geojson_polygon',
                    'data' => $result['polygon'],
                    'meta' => ['type' => 'geojson_polygon', 'valid' => true],
                ], 200)
                : $this->errorResponse($result['error'] ?? 'GeoJSON polygon tidak valid.', 422);
        }

        return $this->errorResponse('Wajib menyertakan salah satu dari: latitude & longitude, bounding_box, atau geojson.', 422);
    }

    /**
     * Check GFW API health and connectivity status.
     */
    public function health(): JsonResponse
    {
        $health = $this->apiService->checkHealth();
        $statusCode = $health['success'] ? 200 : 503;

        return response()->json($health, $statusCode);
    }

    /**
     * Internal event query dispatcher with standardized envelope formatting.
     *
     * @param  callable(array<string, mixed>): array<string, mixed>  $queryRunner
     */
    protected function runEventQuery(callable $queryRunner, EventQueryRequest $request): JsonResponse
    {
        if (! $this->apiService->isConfigured()) {
            return $this->errorResponse('GFW API key is not configured.', 503);
        }

        $region = (string) ($request->validated('region') ?? 'indonesia_eez');
        $startDate = $request->validated('start_date');
        $endDate = $request->validated('end_date');
        $vesselId = $request->validated('vessel_id');
        $limit = (int) ($request->validated('limit') ?? 20);
        $refresh = $request->boolean('refresh');

        $options = array_filter([
            'region' => $region,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'vessel_id' => $vesselId,
            'limit' => $limit,
            'refresh' => $refresh,
        ], fn ($val) => $val !== null && $val !== '');

        $result = $queryRunner($options);

        if (! $result['success']) {
            $status = str_contains($result['error'] ?? '', 'Region') ? 404 : 502;

            return $this->errorResponse($result['error'] ?? 'Gagal mengambil data peristiwa GFW.', $status);
        }

        return $this->successResponse($result['data'], [
            'event_type' => $result['event_type'] ?? null,
            'semantic_label' => $result['semantic_label'] ?? null,
            'semantic_disclaimer' => $result['semantic_disclaimer'] ?? null,
            'region' => $result['region'] ?? null,
            'query_period' => $result['query_period'] ?? null,
            'total' => $result['total'] ?? count($result['data']),
            'cached' => $result['cached'] ?? false,
        ]);
    }

    /**
     * Standard successful JSON response envelope.
     *
     * @param  array<string, mixed>  $meta
     */
    protected function successResponse(mixed $data, array $meta = [], int $status = 200): JsonResponse
    {
        $defaultMeta = [
            'count' => is_countable($data) ? count($data) : 1,
        ];

        $meta = array_merge($defaultMeta, $meta);

        $payload = [
            'success' => true,
            'source' => 'global_fishing_watch',
            'data' => $data,
            'meta' => $meta,
        ];

        foreach (['total', 'count', 'cached', 'type', 'valid', 'latency_notice', 'event_type', 'semantic_label', 'semantic_disclaimer', 'region', 'query_period'] as $key) {
            if (isset($meta[$key])) {
                $payload[$key] = $meta[$key];
            }
        }

        return response()->json($payload, $status);
    }

    /**
     * Standard error JSON response envelope.
     *
     * @param  array<string, mixed>  $extra
     */
    protected function errorResponse(string $message, int $status = 400, array $extra = []): JsonResponse
    {
        $payload = array_merge([
            'success' => false,
            'source' => 'global_fishing_watch',
            'error' => $message,
            'valid' => false,
            'total' => 0,
            'data' => [],
            'status' => $status,
        ], $extra);

        return response()->json($payload, $status);
    }
}

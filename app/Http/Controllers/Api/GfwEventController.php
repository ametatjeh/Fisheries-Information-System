<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Gfw\GfwApiService;
use App\Services\Gfw\GfwEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GfwEventController extends Controller
{
    public function __construct(
        protected GfwEventService $eventService,
        protected GfwApiService $apiService
    ) {}

    /**
     * Query Apparent Fishing Events.
     */
    public function fishing(Request $request): JsonResponse
    {
        return $this->handleEventQuery(fn ($options) => $this->eventService->fishingEvents($options), $request);
    }

    /**
     * Query Potential Encounters between vessels.
     */
    public function encounters(Request $request): JsonResponse
    {
        return $this->handleEventQuery(fn ($options) => $this->eventService->encounters($options), $request);
    }

    /**
     * Query Loitering Events.
     */
    public function loitering(Request $request): JsonResponse
    {
        return $this->handleEventQuery(fn ($options) => $this->eventService->loitering($options), $request);
    }

    /**
     * Query Port Visits.
     */
    public function portVisits(Request $request): JsonResponse
    {
        return $this->handleEventQuery(fn ($options) => $this->eventService->portVisits($options), $request);
    }

    /**
     * Get single GFW event by Event ID.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $cleanId = trim($id);
        if ($cleanId === '') {
            return response()->json([
                'success' => false,
                'error' => 'Event ID tidak valid.',
            ], 422);
        }

        if (! $this->apiService->isConfigured()) {
            return response()->json([
                'success' => false,
                'error' => 'GFW API key is not configured.',
                'data' => null,
            ], 503);
        }

        $refresh = $request->boolean('refresh');
        $event = $this->eventService->getEventById($cleanId, ['refresh' => $refresh]);

        if (! $event) {
            return response()->json([
                'success' => false,
                'error' => 'Event tidak ditemukan pada data GFW.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $event,
        ], 200);
    }

    /**
     * Generic handler for event collection endpoints with validation and error formatting.
     *
     * @param  callable(array<string, mixed>): array<string, mixed>  $queryRunner
     */
    protected function handleEventQuery(callable $queryRunner, Request $request): JsonResponse
    {
        if (! $this->apiService->isConfigured()) {
            return response()->json([
                'success' => false,
                'error' => 'GFW API key is not configured.',
                'data' => [],
            ], 503);
        }

        $region = (string) $request->query('region', 'indonesia_eez');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $vesselId = $request->query('vessel_id');
        $limit = (int) $request->query('limit', 20);
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

        $isRegionNotFound = isset($result['error']) && str_contains($result['error'], 'Region');
        $isValidationError = isset($result['error']) && (
            str_contains($result['error'], 'date') ||
            str_contains($result['error'], 'tanggal') ||
            str_contains($result['error'], 'Rentang') ||
            str_contains($result['error'], 'Format')
        );

        $statusCode = $result['success'] ? 200 : ($isRegionNotFound ? 404 : ($isValidationError ? 422 : 502));

        return response()->json($result, $statusCode);
    }
}

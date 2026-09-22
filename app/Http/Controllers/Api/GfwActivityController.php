<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Gfw\GfwActivityService;
use App\Services\Gfw\GfwApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GfwActivityController extends Controller
{
    public function __construct(
        protected GfwActivityService $activityService,
        protected GfwApiService $apiService
    ) {}

    /**
     * Get activity track points and positions for a single GFW vessel.
     */
    public function vesselActivity(string $id, Request $request): JsonResponse
    {
        $cleanId = trim($id);
        if ($cleanId === '') {
            return response()->json([
                'success' => false,
                'error' => 'Identifier vessel tidak valid.',
            ], 422);
        }

        if (! $this->apiService->isConfigured()) {
            return response()->json([
                'success' => false,
                'error' => 'GFW API key is not configured.',
                'data' => [],
            ], 503);
        }

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $refresh = $request->boolean('refresh');

        $result = $this->activityService->getVesselActivity($cleanId, [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'refresh' => $refresh,
        ]);

        $isValidationError = isset($result['error']) && (
            str_contains($result['error'], 'date') ||
            str_contains($result['error'], 'tanggal') ||
            str_contains($result['error'], 'Rentang') ||
            str_contains($result['error'], 'Format')
        );

        $statusCode = $result['success'] ? 200 : ($isValidationError ? 422 : 502);

        return response()->json($result, $statusCode);
    }

    /**
     * Get vessel presence observations within a specified geographic/maritime region.
     */
    public function presence(Request $request): JsonResponse
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
        $limit = (int) $request->query('limit', 20);
        $refresh = $request->boolean('refresh');

        $result = $this->activityService->getVesselPresence($region, [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'limit' => $limit,
            'refresh' => $refresh,
        ]);

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

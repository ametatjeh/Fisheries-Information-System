<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Gfw\GfwApiService;
use App\Services\Gfw\GfwVesselService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GfwVesselController extends Controller
{
    public function __construct(
        protected GfwVesselService $vesselService,
        protected GfwApiService $apiService
    ) {}

    /**
     * Search vessels via GFW API v3 with caching and normalization.
     */
    public function search(Request $request): JsonResponse
    {
        $query = (string) $request->query('query', '');
        $trimmed = trim($query);

        if ($trimmed === '' || strlen($trimmed) < 2) {
            return response()->json([
                'success' => false,
                'error' => 'Parameter query minimal 2 karakter.',
                'data' => [],
            ], 422);
        }

        if (! $this->apiService->isConfigured()) {
            return response()->json([
                'success' => false,
                'error' => 'GFW API key is not configured.',
                'data' => [],
            ], 503);
        }

        $limit = max(1, min((int) $request->query('limit', 10), 50));
        $refresh = $request->boolean('refresh');

        $result = $this->vesselService->search($trimmed, [
            'limit' => $limit,
            'refresh' => $refresh,
        ]);

        $statusCode = $result['success'] ? 200 : 502;

        return response()->json($result, $statusCode);
    }

    /**
     * Get single vessel identity by GFW Vessel ID.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $cleanId = trim($id);
        if ($cleanId === '') {
            return response()->json([
                'success' => false,
                'error' => 'Identifier vessel tidak valid.',
            ], 422);
        }

        $refresh = $request->boolean('refresh');
        $vessel = $this->vesselService->getById($cleanId, $refresh);

        if (! $vessel) {
            return response()->json([
                'success' => false,
                'error' => 'Vessel tidak ditemukan pada data GFW.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $vessel,
        ], 200);
    }
}

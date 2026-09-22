<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Gis\BigMaritimeBoundaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BigZeeApiController extends Controller
{
    public function __construct(
        protected BigMaritimeBoundaryService $bigService
    ) {}

    /**
     * Retrieve official BIG Peta Batas ZEE GeoJSON boundary.
     *
     * @response array{
     *     success: bool,
     *     source: string,
     *     layer: string,
     *     layer_id: int,
     *     crs: string,
     *     data?: array{type: 'FeatureCollection', features: list<array<string, mixed>>},
     *     error?: string
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $forceRefresh = $request->boolean('refresh');
        $result = $this->bigService->getZeeGeoJson($forceRefresh);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'source' => $result['source'] ?? 'BIG',
                'layer' => $result['layer'] ?? 'Peta Batas ZEE',
                'layer_id' => $result['layer_id'] ?? 10,
                'crs' => 'EPSG:4326',
                'error' => $result['error'] ?? 'Garis ZEE BIG tidak dapat dimuat.',
            ], 502);
        }

        if ($request->boolean('geojson') || $request->boolean('raw')) {
            return response()->json($result['data'], 200, [
                'Content-Type' => 'application/geo+json',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        $responsePayload = [
            'success' => true,
            'source' => $result['source'],
            'layer' => $result['layer'],
            'layer_id' => $result['layer_id'],
            'crs' => $result['crs'],
            'data' => $result['data'],
        ];

        if (isset($result['notice'])) {
            $responsePayload['notice'] = $result['notice'];
        }

        if (isset($result['cached'])) {
            $responsePayload['cached'] = $result['cached'];
        }

        return response()->json($responsePayload, 200, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Retrieve official BIG Peta Batas ZEE GeoJSON boundary specifically for Aceh.
     */
    public function aceh(Request $request): JsonResponse
    {
        $forceRefresh = $request->boolean('refresh');
        $geoJson = $this->bigService->getZeeForAceh($forceRefresh);

        return response()->json($geoJson, 200, [
            'Content-Type' => 'application/geo+json',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}

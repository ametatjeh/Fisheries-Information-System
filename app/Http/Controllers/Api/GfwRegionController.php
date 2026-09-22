<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Gfw\GfwRegionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GfwRegionController extends Controller
{
    public function __construct(
        protected GfwRegionService $regionService
    ) {}

    /**
     * List all supported geographic and maritime regions for GFW queries.
     */
    public function index(): JsonResponse
    {
        $regions = $this->regionService->getSupportedRegions();

        return response()->json([
            'success' => true,
            'source' => 'global_fishing_watch',
            'data' => array_values($regions),
            'total' => count($regions),
        ]);
    }

    /**
     * Retrieve specifications and GFW query parameters for a specific region.
     */
    public function show(string $key, Request $request): JsonResponse
    {
        $region = $this->regionService->getRegion($key);

        if (! $region) {
            return response()->json([
                'success' => false,
                'error' => "Region [{$key}] tidak ditemukan atau belum didukung.",
            ], 404);
        }

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $customOptions = array_filter([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $queryParams = $this->regionService->buildQueryParams($region['key'], $customOptions);

        return response()->json([
            'success' => true,
            'data' => array_merge($region, $queryParams),
        ]);
    }

    /**
     * Validate custom coordinate pairs, bounding box, or GeoJSON polygon.
     */
    public function validateGeometry(Request $request): JsonResponse
    {
        // 1. Single coordinate pair check
        if ($request->has(['latitude', 'longitude'])) {
            $lat = (float) $request->input('latitude');
            $lon = (float) $request->input('longitude');

            $valid = $this->regionService->validateCoordinates($lat, $lon);

            return response()->json([
                'success' => $valid,
                'type' => 'coordinate_pair',
                'valid' => $valid,
                'data' => [
                    'latitude' => $lat,
                    'longitude' => $lon,
                ],
                'error' => $valid ? null : 'Koordinat tidak valid (-90 s/d 90 untuk latitude, -180 s/d 180 untuk longitude).',
            ], $valid ? 200 : 422);
        }

        // 2. Bounding Box check
        if ($request->has('bounding_box')) {
            $bbox = $request->input('bounding_box');
            if (! is_array($bbox)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Parameter bounding_box harus berupa array 4 elemen [min_lon, min_lat, max_lon, max_lat].',
                ], 422);
            }

            $result = $this->regionService->validateBoundingBox($bbox);

            return response()->json([
                'success' => $result['valid'],
                'type' => 'bounding_box',
                'valid' => $result['valid'],
                'data' => $result['bbox'] ?? null,
                'error' => $result['error'] ?? null,
            ], $result['valid'] ? 200 : 422);
        }

        // 3. GeoJSON Polygon check
        if ($request->has('geojson')) {
            $geojson = $request->input('geojson');
            if (! is_array($geojson)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Parameter geojson harus berupa object JSON representasi polygon.',
                ], 422);
            }

            $result = $this->regionService->validateGeoJsonPolygon($geojson);

            return response()->json([
                'success' => $result['valid'],
                'type' => 'geojson_polygon',
                'valid' => $result['valid'],
                'data' => $result['polygon'] ?? null,
                'error' => $result['error'] ?? null,
            ], $result['valid'] ? 200 : 422);
        }

        return response()->json([
            'success' => false,
            'error' => 'Wajib menyertakan salah satu dari: latitude & longitude, bounding_box, atau geojson.',
        ], 422);
    }
}

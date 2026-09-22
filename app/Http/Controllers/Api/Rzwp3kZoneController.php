<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rzwp3kZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class Rzwp3kZoneController extends Controller
{
    /**
     * Cache TTL in seconds (1 hour).
     */
    public const CACHE_TTL = 3600;

    /**
     * Cache key prefix.
     */
    public const CACHE_PREFIX = 'rzwp3k:zones:geojson:';

    /**
     * Display a GeoJSON FeatureCollection of active RZWP3K zones with valid geometries.
     */
    public function index(Request $request): JsonResponse
    {
        $zoneType = $request->query('zone_type');
        $subzoneType = $request->query('subzone_type');

        // Sanitize & validate filter parameters
        $validZoneTypes = ['KPU', 'KK', 'AL', 'KSNT'];
        $cleanZoneType = in_array(strtoupper((string) $zoneType), $validZoneTypes, true)
            ? strtoupper((string) $zoneType)
            : null;

        $cleanSubzoneType = is_string($subzoneType) && strlen(trim($subzoneType)) > 0
            ? trim($subzoneType)
            : null;

        $cacheKey = self::CACHE_PREFIX.md5(json_encode([
            'zone_type' => $cleanZoneType,
            'subzone_type' => $cleanSubzoneType,
        ]));

        $featureCollection = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($cleanZoneType, $cleanSubzoneType) {
            $query = Rzwp3kZone::query()
                ->active()
                ->withGeometry();

            if ($cleanZoneType) {
                $query->ofZoneType($cleanZoneType);
            }

            if ($cleanSubzoneType) {
                $query->ofSubzoneType($cleanSubzoneType);
            }

            $zones = $query->get();

            $features = [];
            foreach ($zones as $zone) {
                $feature = $zone->toGeoJsonFeature();
                if ($feature !== null) {
                    $features[] = $feature;
                }
            }

            return [
                'type' => 'FeatureCollection',
                'features' => $features,
                'metadata' => [
                    'legal_basis' => 'Qanun Aceh Nomor 1 Tahun 2020',
                    'authority' => 'Pemerintah Aceh / DKP Aceh',
                    'target_crs' => 'EPSG:4326',
                    'count' => count($features),
                    'disclaimer' => 'Informasi ini merupakan informasi zonasi spasial. Tampilan spasial tidak dengan sendirinya menentukan status legalitas suatu aktivitas atau kapal.',
                ],
            ];
        });

        return response()->json($featureCollection, 200, [
            'Content-Type' => 'application/geo+json',
        ]);
    }

    /**
     * Flush all cached RZWP3K GeoJSON responses.
     */
    public static function clearCache(): void
    {
        // Clear all standard filtered hashes if tag unsupported or flush specific keys
        Cache::forget(self::CACHE_PREFIX.md5(json_encode(['zone_type' => null, 'subzone_type' => null])));
        foreach (['KPU', 'KK', 'AL', 'KSNT'] as $type) {
            Cache::forget(self::CACHE_PREFIX.md5(json_encode(['zone_type' => $type, 'subzone_type' => null])));
        }
    }
}

<?php

namespace App\Services\Gis;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class BigMaritimeBoundaryService
{
    public const BIG_ZEE_LAYER_ID = 10;

    public const BIG_LAYER_NAME = 'Peta Batas ZEE';

    public const BIG_QUERY_URL = 'https://kspservices.big.go.id/satupeta/rest/services/PUBLIK/BATAS_WILAYAH/MapServer/10/query';

    public const CACHE_KEY = 'big_zee_geojson';

    public const CACHE_KEY_ACEH = 'big_zee_geojson_aceh';

    public const CACHE_TTL_SECONDS = 86400; // 24 hours

    /**
     * Official BIG ZEE status code mapping:
     * 1 = Kesepakatan
     * 2 = Unilateral
     * 3 = Kesepakatan Belum Diratifikasi
     * 4 = Perlu Kesepakatan
     *
     * @var array<int, string>
     */
    public const STATUS_MAP = [
        1 => 'Kesepakatan',
        2 => 'Unilateral',
        3 => 'Kesepakatan Belum Diratifikasi',
        4 => 'Perlu Kesepakatan',
    ];

    /**
     * Retrieve the official BIG Peta Batas ZEE GeoJSON FeatureCollection.
     *
     * @return array{
     *     success: bool,
     *     source: string,
     *     layer: string,
     *     layer_id: int,
     *     crs: string,
     *     data?: array{type: 'FeatureCollection', features: list<array<string, mixed>>},
     *     cached?: bool,
     *     upstream_available?: bool,
     *     notice?: string,
     *     error?: string
     * }
     */
    public function getZeeGeoJson(bool $forceRefresh = false): array
    {
        // 1. Check cache first (unless forceRefresh)
        if (! $forceRefresh && Cache::has(self::CACHE_KEY)) {
            /** @var array{type: 'FeatureCollection', features: list<array<string, mixed>>} $cachedGeoJson */
            $cachedGeoJson = Cache::get(self::CACHE_KEY);

            return [
                'success' => true,
                'source' => 'BIG',
                'layer' => self::BIG_LAYER_NAME,
                'layer_id' => self::BIG_ZEE_LAYER_ID,
                'crs' => 'EPSG:4326',
                'data' => $cachedGeoJson,
                'cached' => true,
                'upstream_available' => true,
            ];
        }

        // 2. Fetch from upstream BIG endpoint
        try {
            $response = Http::timeout(15)
                ->withOptions(['verify' => false])
                ->get(self::BIG_QUERY_URL, [
                    'where' => '1=1',
                    'outFields' => '*',
                    'returnGeometry' => 'true',
                    'f' => 'geojson',
                ]);

            if ($response->successful()) {
                $rawPayload = $response->json();
                if (is_array($rawPayload) && ($rawPayload['type'] ?? '') === 'FeatureCollection' && isset($rawPayload['features']) && is_array($rawPayload['features'])) {
                    $normalizedGeoJson = $this->normalizeGeoJson($rawPayload);

                    // Cache in memory / cache store
                    Cache::put(self::CACHE_KEY, $normalizedGeoJson, self::CACHE_TTL_SECONDS);

                    // Persist to local backup storage
                    $this->saveToStorageBackup($normalizedGeoJson);

                    return [
                        'success' => true,
                        'source' => 'BIG',
                        'layer' => self::BIG_LAYER_NAME,
                        'layer_id' => self::BIG_ZEE_LAYER_ID,
                        'crs' => 'EPSG:4326',
                        'data' => $normalizedGeoJson,
                        'cached' => false,
                        'upstream_available' => true,
                    ];
                }
            }

            Log::warning('[BigMaritimeBoundaryService] Upstream BIG returned unsuccessful response', [
                'status' => $response->status(),
            ]);
        } catch (Throwable $e) {
            Log::warning('[BigMaritimeBoundaryService] Exception querying BIG ZEE endpoint: '.$e->getMessage());
        }

        // 3. Fallback: try cache or storage backup if upstream failed
        if (Cache::has(self::CACHE_KEY)) {
            /** @var array{type: 'FeatureCollection', features: list<array<string, mixed>>} $cachedGeoJson */
            $cachedGeoJson = Cache::get(self::CACHE_KEY);

            return [
                'success' => true,
                'source' => 'BIG',
                'layer' => self::BIG_LAYER_NAME,
                'layer_id' => self::BIG_ZEE_LAYER_ID,
                'crs' => 'EPSG:4326',
                'data' => $cachedGeoJson,
                'cached' => true,
                'upstream_available' => false,
                'notice' => 'Data ZEE BIG sementara menggunakan cache terakhir.',
            ];
        }

        $storageBackup = $this->loadFromStorageBackup();
        if ($storageBackup !== null) {
            Cache::put(self::CACHE_KEY, $storageBackup, self::CACHE_TTL_SECONDS);

            return [
                'success' => true,
                'source' => 'BIG',
                'layer' => self::BIG_LAYER_NAME,
                'layer_id' => self::BIG_ZEE_LAYER_ID,
                'crs' => 'EPSG:4326',
                'data' => $storageBackup,
                'cached' => true,
                'upstream_available' => false,
                'notice' => 'Data ZEE BIG sementara menggunakan cache terakhir.',
            ];
        }

        // 4. Complete failure
        return [
            'success' => false,
            'source' => 'BIG',
            'layer' => self::BIG_LAYER_NAME,
            'layer_id' => self::BIG_ZEE_LAYER_ID,
            'crs' => 'EPSG:4326',
            'error' => 'Garis ZEE BIG tidak dapat dimuat.',
        ];
    }

    /**
     * Normalize BIG GeoJSON features and enrich attributes with status labels and attribution.
     *
     * @param  array{type: string, features: list<array<string, mixed>>}  $raw
     * @return array{type: 'FeatureCollection', features: list<array<string, mixed>>}
     */
    protected function normalizeGeoJson(array $raw): array
    {
        $features = [];

        foreach ($raw['features'] as $f) {
            if (! is_array($f)) {
                continue;
            }

            $props = is_array($f['properties'] ?? null) ? $f['properties'] : [];
            $stslat = isset($props['stslat']) ? (int) $props['stslat'] : null;
            $statusLabel = $stslat !== null && isset(self::STATUS_MAP[$stslat])
                ? self::STATUS_MAP[$stslat]
                : 'Tidak diketahui';

            $enrichedProps = array_merge($props, [
                'stslat' => $stslat,
                'status_label' => $statusLabel,
                'source' => 'Badan Informasi Geospasial (BIG)',
                'layer' => self::BIG_LAYER_NAME,
                'layer_id' => self::BIG_ZEE_LAYER_ID,
            ]);

            $features[] = [
                'type' => 'Feature',
                'geometry' => $f['geometry'] ?? null,
                'properties' => $enrichedProps,
            ];
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    /**
     * Get human-readable status label from BIG stslat code.
     */
    public function getStatusLabel(?int $stslat): string
    {
        if ($stslat === null) {
            return 'Tidak tersedia';
        }

        return self::STATUS_MAP[$stslat] ?? 'Tidak diketahui';
    }

    /**
     * Get all supported BIG status values for legend rendering.
     *
     * @return array<int, string>
     */
    public function getStatusLegend(): array
    {
        return self::STATUS_MAP;
    }

    /**
     * Save GeoJSON to storage backup.
     *
     * @param  array<string, mixed>  $geojson
     */
    protected function saveToStorageBackup(array $geojson): void
    {
        try {
            $dir = storage_path('app/private/gis');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($dir.'/big_peta_batas_zee.geojson', json_encode($geojson, JSON_UNESCAPED_SLASHES));
        } catch (Throwable) {
            // Ignore write failures gracefully
        }
    }

    /**
     * Load GeoJSON from storage backup.
     *
     * @return array{type: 'FeatureCollection', features: list<array<string, mixed>>}|null
     */
    protected function loadFromStorageBackup(): ?array
    {
        try {
            $filePath = storage_path('app/private/gis/big_peta_batas_zee.geojson');
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                if ($content !== false) {
                    $json = json_decode($content, true);
                    if (is_array($json) && ($json['type'] ?? '') === 'FeatureCollection' && isset($json['features'])) {
                        return $this->normalizeGeoJson($json);
                    }
                }
            }
        } catch (Throwable) {
            // Ignore read failures gracefully
        }

        return null;
    }

    /**
     * Retrieve the official BIG Peta Batas ZEE filtered specifically for the Aceh region.
     *
     * @return array{
     *     type: 'FeatureCollection',
     *     name: string,
     *     wilayah: string,
     *     source: string,
     *     layer: string,
     *     layer_id: int,
     *     crs: array{type: string, properties: array<string, string>},
     *     features: list<array<string, mixed>>,
     *     cached?: bool,
     *     notice?: string
     * }
     */
    public function getZeeForAceh(bool $forceRefresh = false): array
    {
        if (! $forceRefresh && Cache::has(self::CACHE_KEY_ACEH)) {
            /** @var array{type: 'FeatureCollection', features: list<array<string, mixed>>} $cachedAceh */
            $cachedAceh = Cache::get(self::CACHE_KEY_ACEH);
            $cachedAceh['cached'] = true;

            return $cachedAceh;
        }

        // Get national BIG ZEE GeoJSON
        $nationalResult = $this->getZeeGeoJson($forceRefresh);

        /** @var list<array<string, mixed>> $rawFeatures */
        $rawFeatures = $nationalResult['data']['features'] ?? [];

        // Spatial selection area / envelope for Aceh maritime waters
        // (offshore envelope used strictly as a filter tool to select authentic BIG segments)
        $envelope = [
            'minLon' => 91.5,
            'maxLon' => 101.0,
            'minLat' => 1.5,
            'maxLat' => 8.5,
        ];

        $acehFeatures = [];
        foreach ($rawFeatures as $feature) {
            $coords = $feature['geometry']['coordinates'] ?? [];
            if (! is_array($coords) || empty($coords)) {
                continue;
            }

            if ($this->featureIntersectsEnvelope($coords, $envelope)) {
                $props = $feature['properties'] ?? [];
                // Exact label requirements from GFW-V13.1 spec:
                // ZEE — Data Resmi BIG
                // Wilayah: Aceh
                $props['label'] = 'ZEE — Data Resmi BIG';
                $props['wilayah'] = 'Aceh';
                $props['source'] = 'Badan Informasi Geospasial (BIG)';
                $props['layer'] = self::BIG_LAYER_NAME;
                $props['layer_id'] = self::BIG_ZEE_LAYER_ID;

                $acehFeatures[] = [
                    'type' => 'Feature',
                    'geometry' => $feature['geometry'],
                    'properties' => $props,
                ];
            }
        }

        $collection = [
            'type' => 'FeatureCollection',
            'name' => 'ZEE — Data Resmi BIG',
            'wilayah' => 'Aceh',
            'source' => 'Badan Informasi Geospasial (BIG)',
            'layer' => self::BIG_LAYER_NAME,
            'layer_id' => self::BIG_ZEE_LAYER_ID,
            'crs' => [
                'type' => 'name',
                'properties' => [
                    'name' => 'urn:ogc:def:crs:OGC:1.3:CRS84',
                ],
            ],
            'features' => $acehFeatures,
            'cached' => false,
        ];

        if (isset($nationalResult['notice'])) {
            $collection['notice'] = $nationalResult['notice'];
        }

        Cache::put(self::CACHE_KEY_ACEH, $collection, self::CACHE_TTL_SECONDS);

        return $collection;
    }

    /**
     * Recursively check if coordinates array intersects the spatial bounding envelope.
     *
     * @param  list<mixed>  $coords
     * @param  array{minLon: float, maxLon: float, minLat: float, maxLat: float}  $envelope
     */
    protected function featureIntersectsEnvelope(array $coords, array $envelope): bool
    {
        foreach ($coords as $pt) {
            if (is_array($pt) && isset($pt[0], $pt[1]) && is_numeric($pt[0]) && is_numeric($pt[1])) {
                if ($pt[0] >= $envelope['minLon'] && $pt[0] <= $envelope['maxLon'] &&
                    $pt[1] >= $envelope['minLat'] && $pt[1] <= $envelope['maxLat']) {
                    return true;
                }
            } elseif (is_array($pt)) {
                if ($this->featureIntersectsEnvelope($pt, $envelope)) {
                    return true;
                }
            }
        }

        return false;
    }
}

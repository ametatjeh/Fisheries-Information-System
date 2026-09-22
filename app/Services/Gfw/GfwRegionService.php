<?php

namespace App\Services\Gfw;

use InvalidArgumentException;

class GfwRegionService
{
    /**
     * Map of supported geographic regions with their GFW dataset references and coordinates.
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $regions = [
        'indonesia_eez' => [
            'key' => 'indonesia_eez',
            'name' => 'Indonesia EEZ (Exclusive Economic Zone)',
            'type' => 'eez',
            'gfw_dataset' => 'public-eez-areas',
            'gfw_region_id' => 'IDN',
            'description' => 'Zona Ekonomi Eksklusif Republik Indonesia sesuai referensi resmi Marine Regions / GFW.',
            'bounding_box' => [95.0, -11.0, 141.0, 6.0], // [min_lon, min_lat, max_lon, max_lat]
            'provenance_note' => 'Cakupan seluruh ZEE Indonesia (perairan yurisdiksi nasional).',
        ],
        'aceh_waters' => [
            'key' => 'aceh_waters',
            'name' => 'Perairan Aceh (Aceh Maritime Observation Zone)',
            'type' => 'maritime_zone',
            'gfw_dataset' => 'custom_geometry',
            'gfw_region_id' => null,
            'description' => 'Zona Pengamatan Maritim Perairan Aceh (mencakup koridor Selat Malaka Utara dan Samudera Hindia Barat Aceh).',
            'bounding_box' => [94.5, 1.8, 98.3, 6.2], // [min_lon, min_lat, max_lon, max_lat]
            'polygon' => [
                'type' => 'Polygon',
                'coordinates' => [
                    [
                        [94.5, 1.8],
                        [94.5, 6.2],
                        [98.3, 6.2],
                        [98.3, 3.8],
                        [97.8, 1.8],
                        [94.5, 1.8],
                    ],
                ],
            ],
            'provenance_note' => 'Menunjukkan area spasial perairan laut di sekitar Aceh untuk pengamatan aktivitas kapal (GFW AIS/VMS). Tidak mencerminkan pelabuhan pangkalan atau kepemilikan kapal administratif lokal Aceh.',
        ],
        'aceh_waters_buffer_100nm' => [
            'key' => 'aceh_waters_buffer_100nm',
            'name' => 'Zona Observasi GFW +100 NM',
            'type' => 'observation_zone',
            'gfw_dataset' => 'custom_geometry',
            'gfw_region_id' => null,
            'description' => 'Zona Observasi GFW dengan radius tambahan 100 NM (185.2 km / 185,200 meter) di luar kawasan perairan ZEE Aceh.',
            'buffer_distance_meters' => 185200.0,
            'buffer_distance_km' => 185.2,
            'buffer_distance_nm' => 100.0,
            'bounding_box' => [92.826537, 0.136321, 99.973463, 7.863669],
            'polygon' => [
                'type' => 'Polygon',
                'coordinates' => [
                    [
                        [94.498495, 0.136321],
                        [94.174183, 0.168524],
                        [93.862364, 0.263313],
                        [93.574992, 0.417049],
                        [93.323081, 0.623836],
                        [93.116285, 0.875743],
                        [92.962531, 1.163113],
                        [92.867718, 1.474931],
                        [92.835499, 1.799241],
                        [92.826537, 6.197376],
                        [92.857521, 6.521267],
                        [92.95142, 6.832965],
                        [93.104757, 7.120521],
                        [93.311752, 7.372887],
                        [93.564528, 7.580348],
                        [93.853403, 7.734901],
                        [94.16726, 7.830575],
                        [94.493984, 7.863669],
                        [98.293984, 7.863669],
                        [98.622416, 7.832617],
                        [98.938267, 7.738333],
                        [99.229232, 7.584499],
                        [99.484009, 7.377112],
                        [99.692751, 7.124245],
                        [99.84744, 6.835722],
                        [99.942191, 6.52273],
                        [99.973463, 6.197376],
                        [99.967344, 3.798396],
                        [99.966464, 3.747478],
                        [99.964024, 3.696611],
                        [99.960026, 3.645842],
                        [99.954474, 3.595219],
                        [99.947373, 3.54479],
                        [99.93873, 3.4946],
                        [99.928554, 3.444698],
                        [99.916854, 3.39513],
                        [99.414494, 1.395925],
                        [99.325549, 1.135105],
                        [99.194792, 0.892531],
                        [99.025812, 0.674849],
                        [98.823239, 0.488026],
                        [98.592623, 0.337183],
                        [98.340282, 0.226458],
                        [98.073132, 0.158886],
                        [97.798495, 0.136321],
                        [94.498495, 0.136321],
                    ],
                ],
            ],
            'provenance_note' => 'Zona observasi teknis GFW +100 NM (185,200 meter) untuk deteksi pergerakan kapal sebelum memasuki perairan ZEE. Bukan merupakan batas hukum atau yurisdiksi ZEE.',
        ],
        'wppnri_571' => [
            'key' => 'wppnri_571',
            'name' => 'WPPNRI 571 (Selat Malaka dan Laut Andaman)',
            'type' => 'wppnri',
            'gfw_dataset' => 'custom_geometry',
            'gfw_region_id' => null,
            'description' => 'Wilayah Pengelolaan Perikanan Negara Republik Indonesia 571 meliputi Selat Malaka dan Laut Andaman.',
            'bounding_box' => [95.0, 1.5, 104.5, 6.0],
            'provenance_note' => 'Batas pengelolaan perikanan perairan Selat Malaka dan Laut Andaman.',
        ],
        'wppnri_572' => [
            'key' => 'wppnri_572',
            'name' => 'WPPNRI 572 (Samudera Hindia Sebelah Barat Sumatera)',
            'type' => 'wppnri',
            'gfw_dataset' => 'custom_geometry',
            'gfw_region_id' => null,
            'description' => 'Wilayah Pengelolaan Perikanan Negara Republik Indonesia 572 meliputi Samudera Hindia Barat Sumatera dan Selat Sunda.',
            'bounding_box' => [91.0, -6.0, 103.0, 6.0],
            'provenance_note' => 'Batas pengelolaan perikanan perairan Samudera Hindia Barat Sumatera.',
        ],
        'fao_57' => [
            'key' => 'fao_57',
            'name' => 'FAO Area 57 (Indian Ocean, Eastern)',
            'type' => 'fao',
            'gfw_dataset' => 'public-fao-areas',
            'gfw_region_id' => '57',
            'description' => 'Kawasan Perikanan Mayor FAO 57 (Samudera Hindia Timur).',
            'bounding_box' => [77.0, -55.0, 150.0, 23.0],
            'provenance_note' => 'Area statistik perikanan internasional FAO 57.',
        ],
    ];

    /**
     * Get all supported geographic regions.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getSupportedRegions(): array
    {
        return $this->regions;
    }

    /**
     * Get details of a specific supported region.
     *
     * @return array<string, mixed>|null
     */
    public function getRegion(string $key): ?array
    {
        $normalizedKey = strtolower(trim($key));

        // Aliases support
        $aliasMap = [
            'indonesia' => 'indonesia_eez',
            'idn' => 'indonesia_eez',
            'eez_indonesia' => 'indonesia_eez',
            'aceh' => 'aceh_waters',
            'perairan_aceh' => 'aceh_waters',
            '571' => 'wppnri_571',
            '572' => 'wppnri_572',
            'fao57' => 'fao_57',
        ];

        $effectiveKey = $aliasMap[$normalizedKey] ?? $normalizedKey;

        return $this->regions[$effectiveKey] ?? null;
    }

    /**
     * Get Indonesia EEZ region descriptor.
     *
     * @return array<string, mixed>
     */
    public function getIndonesiaRegion(): array
    {
        return $this->regions['indonesia_eez'];
    }

    /**
     * Get Aceh Maritime Waters region descriptor.
     *
     * @return array<string, mixed>
     */
    public function getAcehRegion(): array
    {
        return $this->regions['aceh_waters'];
    }

    /**
     * Validate latitude and longitude coordinates.
     */
    public function validateCoordinates(float $latitude, float $longitude): bool
    {
        return $latitude >= -90.0
            && $latitude <= 90.0
            && $longitude >= -180.0
            && $longitude <= 180.0;
    }

    /**
     * Validate a bounding box [min_lon, min_lat, max_lon, max_lat].
     *
     * @param  array<mixed>  $bbox
     * @return array{valid: bool, error?: string, bbox?: array{min_lon: float, min_lat: float, max_lon: float, max_lat: float}}
     */
    public function validateBoundingBox(array $bbox): array
    {
        if (count($bbox) !== 4) {
            return [
                'valid' => false,
                'error' => 'Bounding box harus memiliki tepat 4 koordinat numerik [min_lon, min_lat, max_lon, max_lat].',
            ];
        }

        $minLon = (float) $bbox[0];
        $minLat = (float) $bbox[1];
        $maxLon = (float) $bbox[2];
        $maxLat = (float) $bbox[3];

        if (! $this->validateCoordinates($minLat, $minLon) || ! $this->validateCoordinates($maxLat, $maxLon)) {
            return [
                'valid' => false,
                'error' => 'Koordinat bounding box berada di luar batas rentang dunia (-90 s/d 90 untuk lat, -180 s/d 180 untuk lon).',
            ];
        }

        if ($minLon >= $maxLon) {
            return [
                'valid' => false,
                'error' => 'min_lon harus lebih kecil daripada max_lon.',
            ];
        }

        if ($minLat >= $maxLat) {
            return [
                'valid' => false,
                'error' => 'min_lat harus lebih kecil daripada max_lat.',
            ];
        }

        return [
            'valid' => true,
            'bbox' => [
                'min_lon' => $minLon,
                'min_lat' => $minLat,
                'max_lon' => $maxLon,
                'max_lat' => $maxLat,
            ],
        ];
    }

    /**
     * Validate a GeoJSON Polygon structure.
     *
     * @param  array<string, mixed>  $geojson
     * @return array{valid: bool, error?: string, polygon?: array<string, mixed>}
     */
    public function validateGeoJsonPolygon(array $geojson): array
    {
        if (($geojson['type'] ?? null) !== 'Polygon') {
            return [
                'valid' => false,
                'error' => 'Tipe GeoJSON harus bertipe "Polygon".',
            ];
        }

        $coordinates = $geojson['coordinates'] ?? null;
        if (! is_array($coordinates) || empty($coordinates) || ! is_array($coordinates[0])) {
            return [
                'valid' => false,
                'error' => 'Array coordinates polygon tidak valid.',
            ];
        }

        $outerRing = $coordinates[0];
        if (count($outerRing) < 4) {
            return [
                'valid' => false,
                'error' => 'Outer ring polygon minimal harus terdiri dari 4 titik koordinat (termasuk titik penutup).',
            ];
        }

        foreach ($outerRing as $index => $point) {
            if (! is_array($point) || count($point) < 2 || ! is_numeric($point[0]) || ! is_numeric($point[1])) {
                return [
                    'valid' => false,
                    'error' => "Titik koordinat pada indeks {$index} tidak valid. Harus berupa pasangan [lon, lat].",
                ];
            }

            $lon = (float) $point[0];
            $lat = (float) $point[1];

            if (! $this->validateCoordinates($lat, $lon)) {
                return [
                    'valid' => false,
                    'error' => "Koordinat [{$lon}, {$lat}] pada indeks {$index} di luar batas koordinat bumi.",
                ];
            }
        }

        // Validate that outer ring is closed (first and last coordinate are identical)
        $first = $outerRing[0];
        $last = $outerRing[count($outerRing) - 1];
        if ((float) $first[0] !== (float) $last[0] || (float) $first[1] !== (float) $last[1]) {
            return [
                'valid' => false,
                'error' => 'Polygon ring harus tertutup (titik pertama dan titik terakhir harus bernilai sama persis).',
            ];
        }

        return [
            'valid' => true,
            'polygon' => $geojson,
        ];
    }

    /**
     * Build standard GFW API geographic query parameters for a given region.
     *
     * @param  array<string, mixed>  $customOptions
     * @return array<string, mixed>
     */
    public function buildQueryParams(string $regionKey, array $customOptions = []): array
    {
        $region = $this->getRegion($regionKey);
        if (! $region) {
            throw new InvalidArgumentException("Region [{$regionKey}] tidak didukung.");
        }

        $params = [];

        if ($region['type'] === 'eez' && ! empty($region['gfw_region_id'])) {
            $params['region'] = [
                'dataset' => $region['gfw_dataset'],
                'id' => $region['gfw_region_id'],
            ];
        } elseif ($region['type'] === 'fao' && ! empty($region['gfw_region_id'])) {
            $params['region'] = [
                'dataset' => $region['gfw_dataset'],
                'id' => $region['gfw_region_id'],
            ];
        } elseif (! empty($region['polygon'])) {
            $params['geojson'] = $region['polygon'];
        } elseif (! empty($region['bounding_box'])) {
            $params['bounding_box'] = $region['bounding_box'];
        }

        if (! empty($customOptions['start_date'])) {
            $params['start_date'] = $customOptions['start_date'];
            $params['start-date'] = $customOptions['start_date'];
        }
        if (! empty($customOptions['end_date'])) {
            $params['end_date'] = $customOptions['end_date'];
            $params['end-date'] = $customOptions['end_date'];
        }

        return [
            'region_key' => $region['key'],
            'region_name' => $region['name'],
            'region_type' => $region['type'],
            'query_parameters' => $params,
            'provenance' => $this->formatProvenance($region['key'], $customOptions),
        ];
    }

    /**
     * Format data provenance envelope for geographic responses.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public function formatProvenance(string $regionKey, array $extra = []): array
    {
        $region = $this->getRegion($regionKey);

        return [
            'source' => 'global_fishing_watch',
            'region_key' => $regionKey,
            'region_name' => $region['name'] ?? $regionKey,
            'region_type' => $region['type'] ?? 'custom',
            'observation_concept' => 'vessel_activity_observed_within_geographic_bounds',
            'administrative_disclaimer' => 'Pengamatan posisi kapal GFW merepresentasikan kehadiran AIS/VMS di area laut terpilih dan tidak menentukan asal pangkalan, izin tangkap, atau kepemilikan administratif daerah.',
            'query_period' => [
                'start_date' => $extra['start_date'] ?? null,
                'end_date' => $extra['end_date'] ?? null,
            ],
            'timestamp' => now()->toIso8601String(),
        ];
    }
}

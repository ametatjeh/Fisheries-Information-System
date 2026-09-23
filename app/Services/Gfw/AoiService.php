<?php

namespace App\Services\Gfw;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AoiService
{
    protected string $relativeStoragePath = 'private/gfw/zee-indonesia-aceh.geojson';

    protected string $buffer100NmStoragePath = 'private/gfw/zee-indonesia-aceh-buffer-100nm.geojson';

    protected string $legacyStoragePath = 'private/gfw/zee-aceh.geojson';

    public const BUFFER_100_NM_METERS = 185200; // 100 NM = 185.2 km = 185,200 meters

    /**
     * Get the absolute path to the ZEE Indonesia - Kawasan Aceh GeoJSON file.
     */
    public function getStoragePath(string $relative = 'private/gfw/zee-indonesia-aceh.geojson'): string
    {
        return storage_path('app/'.$relative);
    }

    /**
     * Read and validate the ZEE Indonesia di Kawasan Aceh GeoJSON file from storage.
     *
     * @return array{
     *     type: string,
     *     features?: array<int, array<string, mixed>>,
     *     properties?: array<string, mixed>,
     *     coordinates?: array<mixed>,
     *     name?: string,
     *     crs?: string,
     *     geometry_type?: string,
     *     feature_count?: int
     * }
     *
     * @throws RuntimeException
     */
    public function getZeeIndonesiaAcehGeometry(): array
    {
        return $this->readGeoJsonFile($this->relativeStoragePath);
    }

    /**
     * Retrieve the GeoJSON geometry for Zona Observasi GFW +100 NM (185,200 meters).
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    public function getZeeIndonesiaAcehBuffer100NmGeometry(): array
    {
        if (file_exists($this->getStoragePath($this->buffer100NmStoragePath))) {
            return $this->readGeoJsonFile($this->buffer100NmStoragePath);
        }

        $baseGeoJson = $this->getZeeIndonesiaAcehGeometry();

        return $this->computeGeodesicBuffer($baseGeoJson, self::BUFFER_100_NM_METERS);
    }

    /**
     * Get summary metadata of the Zona Observasi GFW +100 NM.
     *
     * @return array{
     *     success: bool,
     *     name: string,
     *     geometry_type: string,
     *     crs: string,
     *     feature_count: int,
     *     bounding_box: array{min_lon: float, min_lat: float, max_lon: float, max_lat: float},
     *     buffer_distance_meters: int,
     *     buffer_distance_km: float,
     *     buffer_distance_nm: int
     * }
     */
    public function getZeeIndonesiaAcehBuffer100NmSummary(): array
    {
        $bufferGeoJson = $this->getZeeIndonesiaAcehBuffer100NmGeometry();
        $summary = $this->extractSummaryFromGeoJson($bufferGeoJson, 'Zona Observasi GFW +100 NM');
        $summary['buffer_distance_meters'] = self::BUFFER_100_NM_METERS;
        $summary['buffer_distance_km'] = round(self::BUFFER_100_NM_METERS / 1000.0, 1);
        $summary['buffer_distance_nm'] = 100;

        return $summary;
    }

    /**
     * Read and validate the legacy/regional ZEE Aceh GeoJSON file from storage.
     *
     * @return array{
     *     type: string,
     *     features?: array<int, array<string, mixed>>,
     *     properties?: array<string, mixed>,
     *     coordinates?: array<mixed>,
     *     name?: string,
     *     crs?: string,
     *     geometry_type?: string,
     *     feature_count?: int
     * }
     *
     * @throws RuntimeException
     */
    public function getZeeAcehGeometry(): array
    {
        if (file_exists($this->getStoragePath($this->legacyStoragePath))) {
            return $this->readGeoJsonFile($this->legacyStoragePath);
        }

        return $this->getZeeIndonesiaAcehGeometry();
    }

    /**
     * Generic reader and validator for internal GeoJSON files.
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    protected function readGeoJsonFile(string $relativePath): array
    {
        $path = $this->getStoragePath($relativePath);

        if (! file_exists($path)) {
            if (Storage::disk('local')->exists($relativePath)) {
                $path = Storage::disk('local')->path($relativePath);
            } else {
                throw new RuntimeException("Berkas GeoJSON AOI tidak ditemukan di [{$path}].");
            }
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException("Gagal membaca berkas GeoJSON AOI pada [{$path}].");
        }

        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            throw new RuntimeException('Format JSON GeoJSON AOI tidak valid: '.json_last_error_msg());
        }

        $validation = $this->validateGeoJsonStructure($decoded);
        if (! $validation['valid']) {
            throw new RuntimeException('Struktur GeoJSON AOI tidak valid: '.($validation['error'] ?? 'Unknown error'));
        }

        return $decoded;
    }

    /**
     * Get summary metadata of the ZEE Indonesia - Kawasan Aceh AOI for internal testing endpoints.
     *
     * @return array{
     *     success: bool,
     *     name: string,
     *     geometry_type: string,
     *     crs: string,
     *     feature_count: int,
     *     bounding_box: array{min_lon: float, min_lat: float, max_lon: float, max_lat: float}
     * }
     */
    public function getZeeIndonesiaAcehSummary(): array
    {
        $geoJson = $this->getZeeIndonesiaAcehGeometry();

        return $this->extractSummaryFromGeoJson($geoJson, 'ZEE Indonesia - Kawasan Aceh');
    }

    /**
     * Get summary metadata of the ZEE Aceh AOI.
     *
     * @return array{
     *     success: bool,
     *     name: string,
     *     geometry_type: string,
     *     crs: string,
     *     feature_count: int,
     *     bounding_box: array{min_lon: float, min_lat: float, max_lon: float, max_lat: float}
     * }
     */
    public function getSummary(?string $fallbackName = 'ZEE Aceh'): array
    {
        $geoJson = $this->getZeeAcehGeometry();

        return $this->extractSummaryFromGeoJson($geoJson, $fallbackName ?? 'ZEE Aceh');
    }

    /**
     * Extract structured summary metadata from GeoJSON.
     *
     * @param  array<string, mixed>  $geoJson
     * @return array{
     *     success: bool,
     *     name: string,
     *     geometry_type: string,
     *     crs: string,
     *     feature_count: int,
     *     bounding_box: array{min_lon: float, min_lat: float, max_lon: float, max_lat: float}
     * }
     */
    protected function extractSummaryFromGeoJson(array $geoJson, string $defaultName): array
    {
        $type = $geoJson['type'] ?? 'FeatureCollection';

        $featureCount = 1;
        $geometryType = 'Polygon';
        $name = $defaultName;
        $crs = 'EPSG:4326';
        $id = 'zee-indonesia-aceh';
        $source = 'BIG';
        $coordinates = [];

        if ($type === 'FeatureCollection' && ! empty($geoJson['features'])) {
            $featureCount = count($geoJson['features']);
            $firstFeature = $geoJson['features'][0];
            $geometryType = $firstFeature['geometry']['type'] ?? 'Polygon';
            $name = $firstFeature['properties']['name'] ?? $defaultName;
            $crs = $firstFeature['properties']['crs'] ?? 'EPSG:4326';
            $id = $firstFeature['properties']['id'] ?? $id;
            $source = $firstFeature['properties']['source'] ?? $source;
            $coordinates = $firstFeature['geometry']['coordinates'] ?? [];
        } elseif ($type === 'Feature') {
            $geometryType = $geoJson['geometry']['type'] ?? 'Polygon';
            $name = $geoJson['properties']['name'] ?? $defaultName;
            $crs = $geoJson['properties']['crs'] ?? 'EPSG:4326';
            $id = $geoJson['properties']['id'] ?? $id;
            $source = $geoJson['properties']['source'] ?? $source;
            $coordinates = $geoJson['geometry']['coordinates'] ?? [];
        } elseif (in_array($type, ['Polygon', 'MultiPolygon'])) {
            $geometryType = $type;
            $coordinates = $geoJson['coordinates'] ?? [];
        }

        $bbox = $this->computeBoundingBox($coordinates, $geometryType);

        return [
            'success' => true,
            'id' => $id,
            'source' => $source,
            'name' => $name,
            'geometry_type' => $geometryType,
            'crs' => $crs,
            'feature_count' => $featureCount,
            'bounding_box' => $bbox,
        ];
    }

    /**
     * Validate the basic RFC 7946 structure of the GeoJSON array.
     *
     * @param  array<string, mixed>  $geoJson
     * @return array{valid: bool, error?: string}
     */
    public function validateGeoJsonStructure(array $geoJson): array
    {
        $type = $geoJson['type'] ?? null;
        if (! $type) {
            return ['valid' => false, 'error' => 'Atribut "type" tidak ditemukan pada root GeoJSON.'];
        }

        if ($type === 'FeatureCollection') {
            if (! isset($geoJson['features']) || ! is_array($geoJson['features']) || empty($geoJson['features'])) {
                return ['valid' => false, 'error' => 'FeatureCollection harus memiliki array "features" yang tidak kosong.'];
            }

            foreach ($geoJson['features'] as $idx => $feature) {
                $featValid = $this->validateFeatureStructure($feature, $idx);
                if (! $featValid['valid']) {
                    return $featValid;
                }
            }

            return ['valid' => true];
        }

        if ($type === 'Feature') {
            return $this->validateFeatureStructure($geoJson, 0);
        }

        if (in_array($type, ['Polygon', 'MultiPolygon'])) {
            return $this->validateGeometryCoordinates($type, $geoJson['coordinates'] ?? null);
        }

        return ['valid' => false, 'error' => "Tipe GeoJSON '{$type}' tidak didukung untuk AOI."];
    }

    /**
     * Validate a single Feature structure.
     *
     * @return array{valid: bool, error?: string}
     */
    protected function validateFeatureStructure(mixed $feature, int $index): array
    {
        if (! is_array($feature) || ($feature['type'] ?? null) !== 'Feature') {
            return ['valid' => false, 'error' => "Elemen pada indeks {$index} bukan Feature GeoJSON yang valid."];
        }

        $geometry = $feature['geometry'] ?? null;
        if (! is_array($geometry) || empty($geometry['type']) || ! isset($geometry['coordinates'])) {
            return ['valid' => false, 'error' => "Geometry pada Feature indeks {$index} tidak valid."];
        }

        $geomType = $geometry['type'];
        if (! in_array($geomType, ['Polygon', 'MultiPolygon'])) {
            return ['valid' => false, 'error' => "Tipe geometri '{$geomType}' pada Feature indeks {$index} tidak didukung. Harus Polygon atau MultiPolygon."];
        }

        return $this->validateGeometryCoordinates($geomType, $geometry['coordinates']);
    }

    /**
     * Validate coordinates of Polygon or MultiPolygon.
     *
     * @return array{valid: bool, error?: string}
     */
    protected function validateGeometryCoordinates(string $type, mixed $coordinates): array
    {
        if (! is_array($coordinates) || empty($coordinates)) {
            return ['valid' => false, 'error' => 'Array coordinates kosong atau tidak valid.'];
        }

        $polygonRings = $type === 'Polygon' ? [$coordinates] : $coordinates;

        foreach ($polygonRings as $polyIdx => $rings) {
            if (! is_array($rings) || empty($rings) || ! is_array($rings[0])) {
                return ['valid' => false, 'error' => "Struktur ring pada polygon indeks {$polyIdx} tidak valid."];
            }

            $outerRing = $rings[0];
            if (count($outerRing) < 4) {
                return ['valid' => false, 'error' => "Outer ring pada polygon indeks {$polyIdx} minimal harus memiliki 4 koordinat."];
            }

            foreach ($outerRing as $ptIdx => $point) {
                if (! is_array($point) || count($point) < 2 || ! is_numeric($point[0]) || ! is_numeric($point[1])) {
                    return ['valid' => false, 'error' => "Titik koordinat pada indeks [{$polyIdx}, 0, {$ptIdx}] tidak valid. Format harus [lon, lat]."];
                }

                $lon = (float) $point[0];
                $lat = (float) $point[1];

                if ($lon < -180.0 || $lon > 180.0 || $lat < -90.0 || $lat > 90.0) {
                    return ['valid' => false, 'error' => "Koordinat [{$lon}, {$lat}] berada di luar rentang geografis dunia."];
                }
            }

            // Ring closure
            $first = $outerRing[0];
            $last = $outerRing[count($outerRing) - 1];
            if ((float) $first[0] !== (float) $last[0] || (float) $first[1] !== (float) $last[1]) {
                return ['valid' => false, 'error' => "Outer ring pada polygon indeks {$polyIdx} harus tertutup (titik awal dan akhir harus sama)."];
            }
        }

        return ['valid' => true];
    }

    /**
     * Compute bounding box [min_lon, min_lat, max_lon, max_lat] from coordinates.
     *
     * @param  array<mixed>  $coordinates
     * @return array{min_lon: float, min_lat: float, max_lon: float, max_lat: float}
     */
    protected function computeBoundingBox(array $coordinates, string $geometryType): array
    {
        $minLon = 180.0;
        $maxLon = -180.0;
        $minLat = 90.0;
        $maxLat = -90.0;

        $polygons = $geometryType === 'Polygon' ? [$coordinates] : $coordinates;

        foreach ($polygons as $rings) {
            if (! is_array($rings)) {
                continue;
            }
            foreach ($rings as $ring) {
                if (! is_array($ring)) {
                    continue;
                }
                foreach ($ring as $pt) {
                    if (is_array($pt) && count($pt) >= 2 && is_numeric($pt[0]) && is_numeric($pt[1])) {
                        $lon = (float) $pt[0];
                        $lat = (float) $pt[1];

                        if ($lon < $minLon) {
                            $minLon = $lon;
                        }
                        if ($lon > $maxLon) {
                            $maxLon = $lon;
                        }
                        if ($lat < $minLat) {
                            $minLat = $lat;
                        }
                        if ($lat > $maxLat) {
                            $maxLat = $lat;
                        }
                    }
                }
            }
        }

        return [
            'min_lon' => $minLon <= $maxLon ? $minLon : 0.0,
            'min_lat' => $minLat <= $maxLat ? $minLat : 0.0,
            'max_lon' => $minLon <= $maxLon ? $maxLon : 0.0,
            'max_lat' => $minLat <= $maxLat ? $maxLat : 0.0,
        ];
    }

    /**
     * Compute a true geodesic buffer (in meters) for an EPSG:4326 GeoJSON Polygon.
     *
     * @param  array<string, mixed>  $geoJson
     * @return array<string, mixed>
     */
    public function computeGeodesicBuffer(array $geoJson, float $distanceMeters = self::BUFFER_100_NM_METERS): array
    {
        $features = $geoJson['features'] ?? null;
        if (! is_array($features) || empty($features)) {
            throw new RuntimeException('GeoJSON tidak memiliki features untuk dibuffer.');
        }

        $geometry = $features[0]['geometry'] ?? null;
        if (! is_array($geometry) || ($geometry['type'] ?? '') !== 'Polygon') {
            throw new RuntimeException('Buffer saat ini hanya mendukung geometri tipe Polygon.');
        }

        $coordinates = $geometry['coordinates'] ?? [];
        if (empty($coordinates) || ! is_array($coordinates[0])) {
            throw new RuntimeException('Koordinat polygon tidak valid.');
        }

        $outerRing = $coordinates[0];
        $n = count($outerRing) - 1;
        if ($n < 3) {
            throw new RuntimeException('Outer ring minimal harus memiliki 3 titik unik.');
        }

        // Compute shoelace signed area to determine vertex winding order
        $signedArea = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $signedArea += ($outerRing[$i][0] * $outerRing[$i + 1][1]) - ($outerRing[$i + 1][0] * $outerRing[$i][1]);
        }

        // Outward normal offset angle:
        // When signedArea < 0 (clockwise in lon/lat), normal pointing to exterior is bearing - 90
        // When signedArea > 0 (counter-clockwise), normal pointing to exterior is bearing + 90
        $outwardOffset = ($signedArea > 0) ? 90.0 : -90.0;

        $earthRadius = 6378137.0; // WGS-84 equatorial radius
        $d = $distanceMeters / $earthRadius;

        $destinationPoint = function (float $lon, float $lat, float $bearingDeg) use ($d): array {
            $b = deg2rad($bearingDeg);
            $latRad = deg2rad($lat);
            $lonRad = deg2rad($lon);

            $outLatRad = asin(sin($latRad) * cos($d) + cos($latRad) * sin($d) * cos($b));
            $outLonRad = $lonRad + atan2(sin($b) * sin($d) * cos($latRad), cos($d) - sin($latRad) * sin($outLatRad));

            return [round(rad2deg($outLonRad), 6), round(rad2deg($outLatRad), 6)];
        };

        $bearing = function (float $lon1, float $lat1, float $lon2, float $lat2): float {
            $y = sin(deg2rad($lon2 - $lon1)) * cos(deg2rad($lat2));
            $x = cos(deg2rad($lat1)) * sin(deg2rad($lat2)) - sin(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon2 - $lon1));

            return fmod(rad2deg(atan2($y, $x)) + 360.0, 360.0);
        };

        $bufferedRing = [];
        for ($i = 0; $i < $n; $i++) {
            $prev = $outerRing[($i - 1 + $n) % $n];
            $curr = $outerRing[$i];
            $next = $outerRing[$i + 1];

            $bPrev = $bearing((float) $prev[0], (float) $prev[1], (float) $curr[0], (float) $curr[1]);
            $bNext = $bearing((float) $curr[0], (float) $curr[1], (float) $next[0], (float) $next[1]);

            $norm1 = fmod($bPrev + $outwardOffset + 360.0, 360.0);
            $norm2 = fmod($bNext + $outwardOffset + 360.0, 360.0);

            $diff = fmod($norm2 - $norm1 + 360.0, 360.0);
            if ($diff > 180.0) {
                $diff -= 360.0;
            }

            $steps = 8;
            for ($s = 0; $s <= $steps; $s++) {
                $ang = fmod($norm1 + ($diff * $s / $steps) + 360.0, 360.0);
                $bufferedRing[] = $destinationPoint((float) $curr[0], (float) $curr[1], $ang);
            }
        }

        // Ensure closure of ring
        if (! empty($bufferedRing)) {
            $bufferedRing[] = $bufferedRing[0];
        }

        $bufferedGeoJson = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'name' => 'Zona Observasi GFW +100 NM',
                        'label' => 'Zona Observasi GFW +100 NM',
                        'type' => 'observation_zone',
                        'buffer_distance_meters' => $distanceMeters,
                        'buffer_distance_km' => round($distanceMeters / 1000.0, 1),
                        'buffer_distance_nm' => round($distanceMeters / 1852.0, 1),
                        'crs' => 'EPSG:4326',
                        'disclaimer' => 'Zona Observasi GFW +100 NM merupakan zona pengamatan teknis satelit AIS/VMS, bukan merupakan batas hukum atau yurisdiksi ZEE.',
                    ],
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [$bufferedRing],
                    ],
                ],
            ],
        ];

        $validation = $this->validateGeoJsonStructure($bufferedGeoJson);
        if (! $validation['valid']) {
            throw new RuntimeException('Geometri buffer GeoJSON tidak valid: '.($validation['error'] ?? 'Unknown error'));
        }

        return $bufferedGeoJson;
    }

    /**
     * Strictly validate that the BIG ZEE Aceh GeoJSON file exists, has valid syntax,
     * valid Polygon/MultiPolygon geometry and CRS EPSG:4326.
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    public function validateAoiOrThrow(string $relative = 'private/gfw/zee-indonesia-aceh.geojson'): array
    {
        $path = $this->getStoragePath($relative);
        if (! file_exists($path) && ! Storage::disk('local')->exists($relative)) {
            throw new RuntimeException('BIG ZEE Aceh AOI configuration is invalid: File not found.');
        }

        try {
            $geoJson = $this->readGeoJsonFile($relative);
        } catch (\Throwable $e) {
            throw new RuntimeException('BIG ZEE Aceh AOI configuration is invalid: '.$e->getMessage(), 0, $e);
        }

        $summary = $this->extractSummaryFromGeoJson($geoJson, 'ZEE Indonesia - Kawasan Aceh');
        if (($summary['crs'] ?? '') !== 'EPSG:4326') {
            throw new RuntimeException('BIG ZEE Aceh AOI configuration is invalid: CRS must be EPSG:4326.');
        }

        return $geoJson;
    }

    /**
     * Check if a 2D point [longitude, latitude] falls within the official BIG ZEE Aceh AOI.
     */
    public function isPointInAoi(float $lon, float $lat): bool
    {
        $geometry = $this->getZeeIndonesiaAcehGeometry();

        return $this->isPointInGeometry($lon, $lat, $geometry);
    }

    /**
     * Check if a 2D point [longitude, latitude] falls within any GeoJSON Geometry, Feature, or FeatureCollection.
     *
     * @param  array<string, mixed>  $geometry
     */
    public function isPointInGeometry(float $lon, float $lat, array $geometry): bool
    {
        $type = $geometry['type'] ?? '';

        if ($type === 'FeatureCollection' && ! empty($geometry['features'])) {
            foreach ($geometry['features'] as $feature) {
                if (isset($feature['geometry']) && $this->isPointInGeometry($lon, $lat, $feature['geometry'])) {
                    return true;
                }
            }

            return false;
        }

        if ($type === 'Feature' && isset($geometry['geometry'])) {
            return $this->isPointInGeometry($lon, $lat, $geometry['geometry']);
        }

        $coords = $geometry['coordinates'] ?? [];

        if ($type === 'Polygon' && is_array($coords)) {
            return $this->isPointInPolygon($lon, $lat, $coords);
        }

        if ($type === 'MultiPolygon' && is_array($coords)) {
            return $this->isPointInMultiPolygon($lon, $lat, $coords);
        }

        return false;
    }

    /**
     * Check if a 2D point [longitude, latitude] falls within a GeoJSON Polygon exterior ring and outside interior holes.
     *
     * @param  array<mixed>  $polygonRings
     */
    public function isPointInPolygon(float $lon, float $lat, array $polygonRings): bool
    {
        if (empty($polygonRings) || ! isset($polygonRings[0]) || ! is_array($polygonRings[0])) {
            return false;
        }

        // 1. Check outer exterior ring
        $exteriorRing = $polygonRings[0];
        if (! $this->pointInLinearRing($lon, $lat, $exteriorRing)) {
            return false;
        }

        // 2. Check interior rings (holes)
        $ringCount = count($polygonRings);
        for ($i = 1; $i < $ringCount; $i++) {
            if (is_array($polygonRings[$i]) && $this->pointInLinearRing($lon, $lat, $polygonRings[$i])) {
                return false; // Point falls inside a hole
            }
        }

        return true;
    }

    /**
     * Check if a 2D point [longitude, latitude] falls within a GeoJSON MultiPolygon.
     *
     * @param  array<mixed>  $multiPolygonCoordinates
     */
    public function isPointInMultiPolygon(float $lon, float $lat, array $multiPolygonCoordinates): bool
    {
        foreach ($multiPolygonCoordinates as $polygonRings) {
            if (is_array($polygonRings) && $this->isPointInPolygon($lon, $lat, $polygonRings)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ray-casting point-in-polygon algorithm on a closed linear ring with boundary inclusion.
     *
     * @param  array<mixed>  $ring
     */
    public function pointInLinearRing(float $x, float $y, array $ring): bool
    {
        $numPoints = count($ring);
        if ($numPoints < 3) {
            return false;
        }

        $inside = false;

        for ($i = 0, $j = $numPoints - 1; $i < $numPoints; $j = $i++) {
            $xi = (float) ($ring[$i][0] ?? 0);
            $yi = (float) ($ring[$i][1] ?? 0);
            $xj = (float) ($ring[$j][0] ?? 0);
            $yj = (float) ($ring[$j][1] ?? 0);

            // Check if point lies directly on edge or vertex (boundary inclusion)
            if ($this->isPointOnSegment($x, $y, $xi, $yi, $xj, $yj)) {
                return true;
            }

            $intersect = (($yi > $y) !== ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1e-12) + $xi);

            if ($intersect) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    /**
     * Check if a point (px, py) lies on line segment from (ax, ay) to (bx, by) within tolerance.
     */
    public function isPointOnSegment(float $px, float $py, float $ax, float $ay, float $bx, float $by, float $epsilon = 1e-7): bool
    {
        // Check bounding box first
        $minX = min($ax, $bx) - $epsilon;
        $maxX = max($ax, $bx) + $epsilon;
        $minY = min($ay, $by) - $epsilon;
        $maxY = max($ay, $by) + $epsilon;

        if ($px < $minX || $px > $maxX || $py < $minY || $py > $maxY) {
            return false;
        }

        // Cross product for collinearity: (px - ax)*(by - ay) - (py - ay)*(bx - ax)
        $crossProduct = ($px - $ax) * ($by - $ay) - ($py - $ay) * ($bx - $ax);

        return abs($crossProduct) <= $epsilon;
    }
}

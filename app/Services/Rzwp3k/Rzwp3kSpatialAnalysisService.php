<?php

namespace App\Services\Rzwp3k;

use App\Models\FishingGround;
use App\Models\GfwVesselActivity;
use App\Models\Rzwp3kZone;
use App\Services\Gfw\AoiService;
use App\Services\GFWService;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class Rzwp3kSpatialAnalysisService
{
    public const DISCLAIMER = 'Informasi ini merupakan hasil analisis spasial berdasarkan koordinat event dan geometry RZWP3K yang tersedia. Hasil ini tidak merupakan penetapan status legalitas aktivitas.';

    public function __construct(
        protected ?GFWService $gfwService = null,
        protected ?AoiService $aoiService = null
    ) {
        $this->gfwService = $gfwService ?? app(GFWService::class);
        $this->aoiService = $aoiService ?? app(AoiService::class);
    }

    /**
     * Perform spatial overlay analysis between GFW Fishing Events and RZWP3K Aceh Zones.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function analyzeGfwEvents(string $startDate, string $endDate, array $options = []): array
    {
        $limit = isset($options['limit']) ? max(1, min(100, (int) $options['limit'])) : 50;
        $offset = isset($options['offset']) ? max(0, (int) $options['offset']) : 0;
        $zoneType = isset($options['zone_type']) && $options['zone_type'] !== ''
            ? strtoupper((string) $options['zone_type'])
            : null;
        $zoneId = isset($options['zone_id']) && $options['zone_id'] !== ''
            ? (int) $options['zone_id']
            : null;

        // 1. Load RZWP3K Zones (allow custom array for testing/injection)
        $zones = $options['zones'] ?? $this->loadRzwp3kZones($zoneType, $zoneId);

        // 2. Classify Zone Geometries
        $classifiedZones = [];
        $readyZones = [];

        foreach ($zones as $zone) {
            $id = is_object($zone) ? $zone->id : ($zone['id'] ?? null);
            $name = is_object($zone) ? $zone->name : ($zone['name'] ?? 'Unknown');
            $code = is_object($zone) ? ($zone->code ?? null) : ($zone['code'] ?? null);
            $zType = is_object($zone) ? ($zone->zone_type ?? null) : ($zone['zone_type'] ?? null);
            $subzoneType = is_object($zone) ? ($zone->subzone_type ?? null) : ($zone['subzone_type'] ?? null);
            $geometry = is_object($zone) ? ($zone->geometry ?? null) : ($zone['geometry'] ?? null);

            $isReady = $this->isValidPolygonGeometry($geometry);

            $summary = [
                'id' => $id,
                'name' => $name,
                'code' => $code,
                'zone_type' => $zType,
                'subzone_type' => $subzoneType,
                'geometry_status' => $isReady ? 'READY' : 'NOT_READY',
                'matched_event_count' => 0,
            ];

            $classifiedZones[$id] = $summary;

            if ($isReady) {
                $readyZones[$id] = [
                    'id' => $id,
                    'name' => $name,
                    'code' => $code,
                    'zone_type' => $zType,
                    'subzone_type' => $subzoneType,
                    'geometry' => $geometry,
                ];
            }
        }

        // 3. Load AOI Geometry for GFW Events Query (if custom events not provided)
        $events = $options['events'] ?? null;
        $gfwResult = null;

        if ($events === null) {
            try {
                $aoiGeometry = $this->aoiService->getZeeIndonesiaAcehGeometry();
            } catch (Throwable $e) {
                return [
                    'success' => false,
                    'message' => 'Gagal memuat AOI ZEE Indonesia Kawasan Aceh: '.$e->getMessage(),
                    'status' => 500,
                ];
            }

            // 4. Fetch GFW Events
            $gfwResult = $this->gfwService->getEvents(
                $aoiGeometry,
                $startDate,
                $endDate,
                [
                    'aoi_name' => 'ZEE Indonesia - Kawasan Aceh',
                    'limit' => $limit,
                    'offset' => $offset,
                ]
            );

            if (! ($gfwResult['success'] ?? false)) {
                return [
                    'success' => false,
                    'message' => $gfwResult['message'] ?? 'Gagal mengambil data event dari GFW API',
                    'status' => $gfwResult['status'] ?? 502,
                ];
            }

            $events = $gfwResult['events'] ?? [];
        }

        $matches = [];

        // 5. Point in Polygon Spatial Overlay
        foreach ($events as $event) {
            $coords = $this->extractValidCoordinates($event);
            if ($coords === null) {
                // Invalid or missing coordinates: skipped gracefully
                continue;
            }

            $lat = $coords['lat'];
            $lon = $coords['lon'];

            foreach ($readyZones as $zId => $rzZone) {
                if ($this->isPointInGeometry($lon, $lat, $rzZone['geometry'])) {
                    $matches[] = [
                        'event_id' => $event['id'] ?? null,
                        'zone_id' => $rzZone['id'],
                        'zone_name' => $rzZone['name'],
                        'zone_code' => $rzZone['code'],
                        'zone_type' => $rzZone['zone_type'],
                        'subzone_type' => $rzZone['subzone_type'],
                        'latitude' => $lat,
                        'longitude' => $lon,
                        'event_type' => $event['type'] ?? 'fishing',
                        'spatial_relation' => 'Within RZWP3K Zone',
                        'vessel_name' => $event['vessel']['name'] ?? null,
                        'ssvid' => $event['vessel']['ssvid'] ?? null,
                        'flag' => $event['vessel']['flag'] ?? null,
                        'start' => $event['start'] ?? null,
                        'end' => $event['end'] ?? null,
                        'dataset' => $event['dataset'] ?? null,
                    ];

                    $classifiedZones[$zId]['matched_event_count']++;
                }
            }
        }

        $totalEvents = $gfwResult['event_count'] ?? count($events);
        $returnedCount = $gfwResult['returned_count'] ?? count($events);

        return [
            'success' => true,
            'source' => 'Global Fishing Watch',
            'analysis' => 'GFW Event × RZWP3K Aceh',
            'aoi' => 'ZEE Indonesia - Kawasan Aceh',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'event_count' => $totalEvents,
            'returned_count' => $returnedCount,
            'spatial_match_count' => count($matches),
            'pagination' => $gfwResult['pagination'] ?? [
                'limit' => $limit,
                'offset' => $offset,
                'total' => $totalEvents,
                'next_offset' => ($offset + $returnedCount < $totalEvents) ? $offset + $returnedCount : null,
            ],
            'zones' => array_values($classifiedZones),
            'matches' => $matches,
            'disclaimer' => self::DISCLAIMER,
            'status' => 200,
        ];
    }

    /**
     * Check if a 2D point [longitude, latitude] falls within a GeoJSON Polygon exterior ring and outside interior holes.
     *
     * @param  array<int, array<int, array<int, float|int>>>  $polygonRings
     */
    public function isPointInPolygon(float $lng, float $lat, array $polygonRings): bool
    {
        if (empty($polygonRings) || ! isset($polygonRings[0])) {
            return false;
        }

        // 1. Check outer exterior ring
        $exteriorRing = $polygonRings[0];
        if (! $this->pointInLinearRing($lng, $lat, $exteriorRing)) {
            return false;
        }

        // 2. Check interior rings (holes)
        $ringCount = count($polygonRings);
        for ($i = 1; $i < $ringCount; $i++) {
            if ($this->pointInLinearRing($lng, $lat, $polygonRings[$i])) {
                return false; // Point falls inside a hole
            }
        }

        return true;
    }

    /**
     * Check if a 2D point [longitude, latitude] falls within a GeoJSON MultiPolygon.
     *
     * @param  array<int, array<int, array<int, array<int, float|int>>>>  $multiPolygonCoordinates
     */
    public function isPointInMultiPolygon(float $lng, float $lat, array $multiPolygonCoordinates): bool
    {
        foreach ($multiPolygonCoordinates as $polygonRings) {
            if (is_array($polygonRings) && $this->isPointInPolygon($lng, $lat, $polygonRings)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a point is within any valid GeoJSON Polygon or MultiPolygon geometry.
     *
     * @param  array<string, mixed>  $geometry
     */
    public function isPointInGeometry(float $lng, float $lat, array $geometry): bool
    {
        $type = $geometry['type'] ?? '';
        $coords = $geometry['coordinates'] ?? [];

        if ($type === 'Polygon' && is_array($coords)) {
            return $this->isPointInPolygon($lng, $lat, $coords);
        }

        if ($type === 'MultiPolygon' && is_array($coords)) {
            return $this->isPointInMultiPolygon($lng, $lat, $coords);
        }

        return false;
    }

    /**
     * Ray-casting point-in-polygon algorithm on a closed linear ring.
     *
     * @param  array<int, array<int, float|int>>  $ring
     */
    public function pointInLinearRing(float $x, float $y, array $ring): bool
    {
        $inside = false;
        $numPoints = count($ring);

        if ($numPoints < 3) {
            return false;
        }

        for ($i = 0, $j = $numPoints - 1; $i < $numPoints; $j = $i++) {
            $xi = (float) ($ring[$i][0] ?? 0);
            $yi = (float) ($ring[$i][1] ?? 0);
            $xj = (float) ($ring[$j][0] ?? 0);
            $yj = (float) ($ring[$j][1] ?? 0);

            $intersect = (($yi > $y) !== ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 0.0000000001) + $xi);

            if ($intersect) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    /**
     * Validate and extract coordinates from a GFW event item.
     *
     * @param  array<string, mixed>  $event
     * @return array{lat: float, lon: float}|null
     */
    public function extractValidCoordinates(array $event): ?array
    {
        $lat = $event['position']['lat'] ?? $event['latitude'] ?? null;
        $lon = $event['position']['lon'] ?? $event['longitude'] ?? null;

        if ($lat === null || $lon === null || ! is_numeric($lat) || ! is_numeric($lon)) {
            return null;
        }

        $lat = (float) $lat;
        $lon = (float) $lon;

        if ($lat < -90.0 || $lat > 90.0 || $lon < -180.0 || $lon > 180.0) {
            return null;
        }

        return [
            'lat' => $lat,
            'lon' => $lon,
        ];
    }

    /**
     * Check if a geometry structure is a valid GeoJSON Polygon or MultiPolygon.
     */
    public function isValidPolygonGeometry(mixed $geometry): bool
    {
        if (! is_array($geometry)) {
            return false;
        }

        $type = $geometry['type'] ?? null;
        $coords = $geometry['coordinates'] ?? null;

        if (! in_array($type, ['Polygon', 'MultiPolygon'], true) || ! is_array($coords) || empty($coords)) {
            return false;
        }

        if ($type === 'Polygon') {
            return isset($coords[0]) && is_array($coords[0]) && count($coords[0]) >= 3;
        }

        if ($type === 'MultiPolygon') {
            return isset($coords[0][0]) && is_array($coords[0][0]) && count($coords[0][0]) >= 3;
        }

        return false;
    }

    /**
     * Load RZWP3K Zones from database.
     *
     * @return Collection<int, Rzwp3kZone>
     */
    protected function loadRzwp3kZones(?string $zoneType = null, ?int $zoneId = null)
    {
        $query = Rzwp3kZone::query()->active();

        if ($zoneType) {
            $query->ofZoneType($zoneType);
        }

        if ($zoneId) {
            $query->where('id', $zoneId);
        }

        return $query->get();
    }

    /**
     * Analyze spatial overlap between Fishing Grounds and RZWP3K Zones.
     *
     * @return array<string, mixed>
     */
    public function analyzeFishingGrounds(?int $fishingGroundId = null, ?string $zoneType = null): array
    {
        $zoneQuery = Rzwp3kZone::query()->active()->withGeometry();
        if ($zoneType) {
            $zoneQuery->ofZoneType($zoneType);
        }
        $zones = $zoneQuery->get();

        if ($zones->isEmpty()) {
            return [
                'status' => 'NOT_READY',
                'message' => 'Data geometri resmi RZWP3K belum tersedia untuk analisis spasial komputasional.',
                'target_crs' => 'EPSG:4326',
                'count' => 0,
                'results' => [],
                'disclaimer' => self::DISCLAIMER,
            ];
        }

        $fgQuery = FishingGround::query();
        if ($fishingGroundId) {
            $fgQuery->where('id', $fishingGroundId);
        }
        $fishingGrounds = $fgQuery->get();

        $results = [];

        foreach ($fishingGrounds as $fg) {
            $lat = $fg->latitude ? (float) $fg->latitude : null;
            $lng = $fg->longitude ? (float) $fg->longitude : null;

            $hasValidCoordinates = ($lat !== null && $lng !== null && $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180 && ($lat != 0 || $lng != 0));

            $intersections = [];

            if ($hasValidCoordinates) {
                foreach ($zones as $zone) {
                    if (is_array($zone->geometry) && $this->isPointInGeometry($lng, $lat, $zone->geometry)) {
                        $intersections[] = [
                            'zone_id' => $zone->id,
                            'zone_code' => $zone->code,
                            'zone_name' => $zone->name,
                            'zone_type' => $zone->zone_type,
                            'subzone_type' => $zone->subzone_type,
                            'legal_basis' => $zone->legal_basis,
                            'spatial_relation' => 'observed_point_within_zone',
                        ];
                    }
                }
            }

            $results[] = [
                'fishing_ground_id' => $fg->id,
                'fishing_ground_code' => $fg->code,
                'fishing_ground_name' => $fg->name,
                'has_coordinates' => $hasValidCoordinates,
                'coordinates' => $hasValidCoordinates ? ['longitude' => $lng, 'latitude' => $lat] : null,
                'intersection_count' => count($intersections),
                'has_spatial_intersection' => count($intersections) > 0,
                'intersecting_zones' => $intersections,
            ];
        }

        return [
            'status' => 'SUCCESS',
            'message' => 'Analisis perpotongan spasial Fishing Ground dengan RZWP3K selesai.',
            'target_crs' => 'EPSG:4326',
            'count' => count($results),
            'results' => $results,
            'disclaimer' => self::DISCLAIMER,
        ];
    }

    /**
     * Analyze spatial relationship between GFW Vessel Activities (stored locally) and RZWP3K Zones.
     *
     * @return array<string, mixed>
     */
    public function analyzeGfwActivities(int $limit = 50, ?string $zoneType = null): array
    {
        $zoneQuery = Rzwp3kZone::query()->active()->withGeometry();
        if ($zoneType) {
            $zoneQuery->ofZoneType($zoneType);
        }
        $zones = $zoneQuery->get();

        if ($zones->isEmpty()) {
            return [
                'status' => 'NOT_READY',
                'message' => 'Data geometri resmi RZWP3K belum tersedia untuk analisis spasial komputasional.',
                'target_crs' => 'EPSG:4326',
                'count' => 0,
                'results' => [],
                'disclaimer' => self::DISCLAIMER,
            ];
        }

        $activities = GfwVesselActivity::query()
            ->latest('id')
            ->limit(min(100, max(1, $limit)))
            ->get();

        $results = [];

        foreach ($activities as $act) {
            $lat = $act->latitude !== null ? (float) $act->latitude : ($act->lat !== null ? (float) $act->lat : null);
            $lng = $act->longitude !== null ? (float) $act->longitude : ($act->lon !== null ? (float) $act->lon : null);

            $hasValidCoordinates = ($lat !== null && $lng !== null && $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180);

            $matchedZones = [];

            if ($hasValidCoordinates) {
                foreach ($zones as $zone) {
                    if (is_array($zone->geometry) && $this->isPointInGeometry($lng, $lat, $zone->geometry)) {
                        $matchedZones[] = [
                            'zone_id' => $zone->id,
                            'zone_code' => $zone->code,
                            'zone_name' => $zone->name,
                            'zone_type' => $zone->zone_type,
                            'subzone_type' => $zone->subzone_type,
                            'spatial_relation' => 'observed_activity_within_zone',
                        ];
                    }
                }
            }

            $obsTime = $act->observation_timestamp ?? ($act->timestamp ?? null);

            $results[] = [
                'activity_id' => $act->id,
                'gfw_vessel_id' => $act->gfw_vessel_id,
                'activity_type' => $act->activity_type ?? 'apparent_fishing',
                'observation_timestamp' => $obsTime ? (is_string($obsTime) ? $obsTime : $obsTime->toIso8601String()) : null,
                'coordinates' => $hasValidCoordinates ? ['longitude' => $lng, 'latitude' => $lat] : null,
                'has_spatial_intersection' => count($matchedZones) > 0,
                'intersecting_zones' => $matchedZones,
            ];
        }

        return [
            'status' => 'SUCCESS',
            'message' => 'Analisis observasi spasial aktivitas GFW terhadap zona RZWP3K selesai.',
            'target_crs' => 'EPSG:4326',
            'count' => count($results),
            'results' => $results,
            'disclaimer' => self::DISCLAIMER,
        ];
    }
}

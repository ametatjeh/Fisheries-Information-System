<?php

namespace App\Services\Gfw;

use App\Models\FishingGround;
use App\Services\GFWService;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class GfwFishingGroundSpatialAnalysisService
{
    public const DISCLAIMER = 'Informasi ini merupakan hasil analisis spasial berdasarkan koordinat event dan geometry Fishing Ground yang tersedia. Hasil ini tidak merupakan penetapan status legalitas aktivitas.';

    public function __construct(
        protected GFWService $gfwService,
        protected AoiService $aoiService
    ) {}

    /**
     * Perform spatial overlay analysis between GFW Fishing Events and Master Fishing Grounds in Aceh.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function analyze(string $startDate, string $endDate, array $options = []): array
    {
        $limit = isset($options['limit']) ? max(1, min(100, (int) $options['limit'])) : 50;
        $offset = isset($options['offset']) ? max(0, (int) $options['offset']) : 0;
        $fishingGroundId = isset($options['fishing_ground_id']) && $options['fishing_ground_id'] !== ''
            ? (int) $options['fishing_ground_id']
            : null;

        // 1. Load Fishing Grounds (allow custom array for testing/injection)
        $fishingGrounds = $options['fishing_grounds'] ?? $this->loadFishingGrounds($fishingGroundId);

        // 2. Classify Fishing Ground Geometries
        $classifiedGrounds = [];
        $readyGrounds = [];

        foreach ($fishingGrounds as $fg) {
            $id = is_object($fg) ? $fg->id : ($fg['id'] ?? null);
            $name = is_object($fg) ? $fg->name : ($fg['name'] ?? 'Unknown');
            $code = is_object($fg) ? ($fg->code ?? null) : ($fg['code'] ?? null);
            $geometry = is_object($fg) ? ($fg->geometry ?? null) : ($fg['geometry'] ?? null);

            $isReady = $this->isValidPolygonGeometry($geometry);

            $summary = [
                'id' => $id,
                'name' => $name,
                'code' => $code,
                'geometry_status' => $isReady ? 'READY' : 'NOT_READY',
                'matched_event_count' => 0,
            ];

            $classifiedGrounds[$id] = $summary;

            if ($isReady) {
                $readyGrounds[$id] = [
                    'id' => $id,
                    'name' => $name,
                    'code' => $code,
                    'geometry' => $geometry,
                ];
            }
        }

        // 3. Load AOI Geometry for GFW Events Query
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

            foreach ($readyGrounds as $fgId => $fg) {
                if ($this->isPointInGeometry($lon, $lat, $fg['geometry'])) {
                    $matches[] = [
                        'event_id' => $event['id'] ?? null,
                        'fishing_ground_id' => $fg['id'],
                        'fishing_ground_name' => $fg['name'],
                        'latitude' => $lat,
                        'longitude' => $lon,
                        'event_type' => $event['type'] ?? 'fishing',
                        'spatial_relation' => 'within',
                        'vessel_name' => $event['vessel']['name'] ?? null,
                        'ssvid' => $event['vessel']['ssvid'] ?? null,
                        'flag' => $event['vessel']['flag'] ?? null,
                        'start' => $event['start'] ?? null,
                        'end' => $event['end'] ?? null,
                        'dataset' => $event['dataset'] ?? null,
                    ];

                    $classifiedGrounds[$fgId]['matched_event_count']++;
                }
            }
        }

        return [
            'success' => true,
            'source' => 'Global Fishing Watch',
            'analysis' => 'GFW Event × Master Fishing Ground Aceh',
            'aoi' => 'ZEE Indonesia - Kawasan Aceh',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'event_count' => $gfwResult['event_count'] ?? 0,
            'returned_count' => $gfwResult['returned_count'] ?? count($events),
            'spatial_match_count' => count($matches),
            'pagination' => $gfwResult['pagination'] ?? [
                'limit' => $limit,
                'offset' => $offset,
                'total' => $gfwResult['event_count'] ?? 0,
                'next_offset' => null,
            ],
            'fishing_grounds' => array_values($classifiedGrounds),
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
     * Load Master Fishing Grounds from database.
     *
     * @return Collection<int, FishingGround>
     */
    protected function loadFishingGrounds(?int $fishingGroundId = null)
    {
        $query = FishingGround::query()->where('is_active', true);

        if ($fishingGroundId !== null) {
            $query->where('id', $fishingGroundId);
        }

        return $query->get();
    }
}

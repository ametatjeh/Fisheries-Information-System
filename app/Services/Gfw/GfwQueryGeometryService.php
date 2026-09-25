<?php

namespace App\Services\Gfw;

/**
 * GFW Query AOI — Aceh
 *
 * Purpose:
 * Optimized spatial query boundary for GFW Vessel Observatory
 *
 * CRS:
 * EPSG:4326
 *
 * Type:
 * Polygon
 *
 * Disclaimer:
 * Area ini merupakan geometri teknis untuk query GFW dan bukan representasi batas hukum ZEE.
 */
class GfwQueryGeometryService
{
    /**
     * Canonical 55-vertex optimized Polygon coordinates for GFW queries in Aceh waters.
     * Closed ring: first and last coordinate identical.
     *
     * @var list<array{0: float, 1: float}>
     */
    protected const ACEH_QUERY_COORDINATES = [
        [92.38046401200006, 1.5320931000000542],
        [92.33388670100004, 1.634267881000028],
        [92.28822678800003, 1.7444037620000472],
        [92.25180226200007, 1.8403583600000388],
        [92.21614692200006, 1.9443611960000453],
        [92.18296630700007, 2.052611066000054],
        [92.15523000600007, 2.1548452120000547],
        [92.12992325300007, 2.261475242000074],
        [92.10824798700008, 2.368029027000034],
        [92.08981129100005, 2.476510934000032],
        [92.07425291700008, 2.591308429000037],
        [92.06759009800004, 2.6522840700000643],
        [92.06247199700005, 2.7082770330000585],
        [92.05494775800008, 2.8221826890000443],
        [92.05129201400007, 2.934220372000027],
        [92.03816466300003, 3.7980176640000423],
        [92.25973867500005, 3.8879015650000497],
        [94.26685538900006, 6.112855356000068],
        [94.42602557600009, 6.307628660000034],
        [94.67847196700006, 6.7233308620000685],
        [95.37729065000008, 7.604011870000022],
        [95.67691182200008, 8.055468760000053],
        [95.94275852700008, 8.210225968000032],
        [96.62532056300006, 7.125943729000028],
        [97.76450915600003, 6.629815565000058],
        [98.31738765300008, 5.910873090000052],
        [98.89529280200009, 5.439253976000032],
        [99.78648643400004, 4.510602423000023],
        [100.00909206200004, 4.14832328600005],
        [99.97774332300008, 4.128420445000074],
        [99.95426989700007, 4.107618213000023],
        [99.93629452200008, 4.085557143000074],
        [99.92046662200005, 4.060419825000054],
        [99.90934384000008, 4.03449417500002],
        [99.90212358100007, 4.007989309000038],
        [99.89949477400006, 3.98043741500004],
        [99.89855151000006, 3.9577535350000517],
        [99.90124673800005, 3.9342854000000216],
        [99.90774313600008, 3.9079010060000314],
        [99.91691206600007, 3.884431229000029],
        [99.92745308200006, 3.8625891990000696],
        [99.94520769000007, 3.840038692000064],
        [99.96459733200004, 3.8200105940000526],
        [99.98758496400006, 3.802203359000032],
        [100.01864630500006, 3.785741364000046],
        [100.04652518800003, 3.7756022920000305],
        [100.08112469800005, 3.7695331180000267],
        [100.11214051200005, 3.7702951910000593],
        [100.14541036900005, 3.774439571000073],
        [100.17633211300006, 3.7846723060000613],
        [100.20120033300009, 3.7973332690000348],
        [100.21752789300007, 3.809104762000061],
        [100.84776639200004, 3.1943930700000465],
        [100.93249481100008, 2.9211688380000282],
        [92.38046401200006, 1.5320931000000542],
    ];

    /**
     * Retrieve the canonical GeoJSON Polygon geometry for GFW spatial queries in Aceh.
     *
     * @return array{
     *     type: 'Polygon',
     *     coordinates: list<list<array{0: float, 1: float}>>
     * }
     */
    public function getAcehQueryPolygon(): array
    {
        return [
            'type' => 'Polygon',
            'coordinates' => [
                self::ACEH_QUERY_COORDINATES,
            ],
        ];
    }

    /**
     * Retrieve technical metadata describing the GFW Query AOI for Aceh.
     *
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        $coords = self::ACEH_QUERY_COORDINATES;
        $lons = array_column($coords, 0);
        $lats = array_column($coords, 1);

        return [
            'name' => 'GFW Query AOI — Aceh',
            'purpose' => 'Optimized spatial query boundary for GFW Vessel Observatory',
            'crs' => 'EPSG:4326',
            'geometry_type' => 'Polygon',
            'vertex_count' => count($coords),
            'unique_vertex_count' => count($coords) - 1,
            'is_closed' => true,
            'disclaimer' => 'Area ini merupakan geometri teknis untuk query GFW dan bukan representasi batas hukum ZEE.',
            'bounding_box' => [
                'min_lon' => min($lons),
                'max_lon' => max($lons),
                'min_lat' => min($lats),
                'max_lat' => max($lats),
            ],
        ];
    }

    /**
     * Check if a 2D point (longitude, latitude) falls strictly inside the Aceh GFW Query Polygon
     * using the standard ray-casting algorithm.
     */
    public function isPointInPolygon(float $lon, float $lat, ?array $polygonGeometry = null): bool
    {
        $ring = $polygonGeometry['coordinates'][0] ?? self::ACEH_QUERY_COORDINATES;
        $count = count($ring);
        if ($count < 4) {
            return false;
        }

        $inside = false;
        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = (float) $ring[$i][0];
            $yi = (float) $ring[$i][1];
            $xj = (float) $ring[$j][0];
            $yj = (float) $ring[$j][1];

            $intersect = (($yi > $lat) !== ($yj > $lat))
                && ($lon < ($xj - $xi) * ($lat - $yi) / ($yj - $yi + 1e-12) + $xi);

            if ($intersect) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }
}

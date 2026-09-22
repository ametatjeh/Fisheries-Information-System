<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Public Statistics GIS Endpoint.
 *
 * Menyediakan data spasial agregat/generalized untuk halaman /statistik publik.
 * Field-field operasional sensitif (nama kapal individual, nama nahkoda, nomor trip,
 * MMSI, IMO, dll.) TIDAK dikembalikan oleh controller ini.
 */
class PublicStatisticsController extends Controller
{
    /**
     * Data GIS publik (agregat) untuk peta statistik /statistik.
     *
     * Hanya mengembalikan:
     * - Fishing Ground (nama zona, WPP, jumlah trip agregat)
     * - Fishing Effort Location (koordinat setting dibulatkan ke 2 desimal, tanpa identitas individual)
     * - Landing Sites (nama, tipe, kabupaten — tanpa data nelayan/kapal individual)
     */
    public function gisData(Request $request): JsonResponse
    {
        $year = $request->query('tahun') ?? $request->query('year');
        $month = $request->query('bulan') ?? $request->query('month');
        $wppnriId = $request->query('wppnri_id') ?? $request->query('wilayah');
        $landingSiteId = $request->query('landing_site_id') ?? $request->query('site');
        $fishingGearId = $request->query('fishing_gear_id') ?? $request->query('gear');
        $speciesId = $request->query('species_id') ?? $request->query('species');

        $filters = compact('year', 'month', 'wppnriId', 'landingSiteId', 'fishingGearId', 'speciesId');

        $cacheKey = 'public_gis_data_'.md5(serialize($filters));

        $data = Cache::remember($cacheKey, 600, function () use ($filters) {
            return $this->buildPublicGisDataset($filters);
        });

        return response()->json($data);
    }

    /**
     * Bangun dataset GIS publik dengan field yang aman.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function buildPublicGisDataset(array $filters): array
    {
        // 1. Fishing Effort Location — koordinat dibulatkan, tanpa identitas
        $effortQuery = DB::table('fishing_efforts')
            ->join('fishing_trips', 'fishing_efforts.fishing_trip_id', '=', 'fishing_trips.id')
            ->leftJoin('fishing_gears', 'fishing_efforts.fishing_gear_id', '=', 'fishing_gears.id')
            ->leftJoin('wppnri', 'fishing_trips.wppnri_id', '=', 'wppnri.id')
            ->whereNotNull('fishing_efforts.latitude_setting')
            ->whereNotNull('fishing_efforts.longitude_setting')
            ->whereBetween('fishing_efforts.latitude_setting', [-90, 90])
            ->whereBetween('fishing_efforts.longitude_setting', [-180, 180])
            ->where('fishing_efforts.latitude_setting', '!=', 0)
            ->where('fishing_efforts.longitude_setting', '!=', 0);

        if (! empty($filters['year'])) {
            $effortQuery->whereYear('fishing_trips.departure_date', $filters['year']);
        }
        if (! empty($filters['month'])) {
            $effortQuery->whereMonth('fishing_trips.departure_date', $filters['month']);
        }
        if (! empty($filters['wppnriId'])) {
            $effortQuery->where('fishing_trips.wppnri_id', $filters['wppnriId']);
        }
        if (! empty($filters['landingSiteId'])) {
            $effortQuery->where('fishing_trips.landing_site_id', $filters['landingSiteId']);
        }
        if (! empty($filters['fishingGearId'])) {
            $effortQuery->where('fishing_efforts.fishing_gear_id', $filters['fishingGearId']);
        }
        if (! empty($filters['speciesId'])) {
            $effortQuery->whereExists(function ($sub) use ($filters) {
                $sub->select(DB::raw(1))
                    ->from('catches')
                    ->whereColumn('catches.fishing_effort_id', 'fishing_efforts.id')
                    ->where('catches.fish_species_id', $filters['speciesId']);
            });
        }

        // Hanya ambil field yang aman — koordinat dibulatkan 2 desimal (~1km presisi)
        $effortPoints = $effortQuery
            ->select(
                DB::raw('ROUND(fishing_efforts.latitude_setting, 2) as lat'),
                DB::raw('ROUND(fishing_efforts.longitude_setting, 2) as lng'),
                'fishing_gears.name_id as gear',
                'wppnri.code as wpp_code',
                'wppnri.name as wpp_name',
                DB::raw('SUM(fishing_efforts.duration_hours) as total_hours'),
                DB::raw('COUNT(fishing_efforts.id) as effort_count')
            )
            ->groupBy(
                DB::raw('ROUND(fishing_efforts.latitude_setting, 2)'),
                DB::raw('ROUND(fishing_efforts.longitude_setting, 2)'),
                'fishing_gears.name_id',
                'wppnri.code',
                'wppnri.name'
            )
            ->limit(500)
            ->get()
            ->map(function ($item) {
                return [
                    'lat_setting' => (float) $item->lat,
                    'lng_setting' => (float) $item->lng,
                    'gear' => $item->gear ?? 'Alat Tangkap',
                    'wpp_code' => $item->wpp_code ? ('WPP-'.$item->wpp_code) : '-',
                    'wpp_name' => $item->wpp_name ?? '-',
                    'effort_count' => (int) $item->effort_count,
                    'total_hours' => round((float) $item->total_hours, 1),
                    'layer' => 'efforts',
                    'layer_label' => 'Titik Operasional Fishing Effort (Agregat)',
                    'note' => 'Koordinat dibulatkan ke ~1 km. Identitas operasional tidak ditampilkan.',
                ];
            });

        // 2. Master Fishing Ground — nama zona, WPP, jumlah trip agregat (tanpa detail trip)
        $groundsQuery = DB::table('fishing_grounds')
            ->leftJoin('wppnri', 'fishing_grounds.wppnri_id', '=', 'wppnri.id')
            ->where('fishing_grounds.is_active', true);

        if (! empty($filters['wppnriId'])) {
            $groundsQuery->where('fishing_grounds.wppnri_id', $filters['wppnriId']);
        }

        $fishingGrounds = $groundsQuery
            ->select(
                'fishing_grounds.id',
                'fishing_grounds.name',
                'fishing_grounds.code',
                'fishing_grounds.latitude',
                'fishing_grounds.longitude',
                'fishing_grounds.description',
                'wppnri.code as wpp_code',
                'wppnri.name as wpp_name'
            )
            ->get()
            ->map(function ($fg) use ($filters) {
                $hasCoords = ! is_null($fg->latitude) && ! is_null($fg->longitude)
                    && (float) $fg->latitude !== 0.0 && (float) $fg->longitude !== 0.0;

                // Hitung jumlah trip agregat
                $tripCount = DB::table('fishing_trips')
                    ->where('fishing_ground_id', $fg->id)
                    ->when(! empty($filters['year']), fn ($q) => $q->whereYear('departure_date', $filters['year']))
                    ->when(! empty($filters['month']), fn ($q) => $q->whereMonth('departure_date', $filters['month']))
                    ->count();

                return [
                    'id' => $fg->id,
                    'name' => $fg->name,
                    'code' => $fg->code ?? '-',
                    'wpp_code' => $fg->wpp_code ? ('WPP '.$fg->wpp_code) : '-',
                    'wpp_name' => $fg->wpp_name ?? '-',
                    'lat' => $hasCoords ? (float) $fg->latitude : null,
                    'lng' => $hasCoords ? (float) $fg->longitude : null,
                    'has_coordinates' => $hasCoords,
                    'trips_count' => $tripCount,
                    'description' => $fg->description ?? '-',
                    'layer' => 'fishing_grounds',
                    'layer_label' => 'Master Fishing Ground',
                ];
            });

        // 3. Landing Sites — nama, tipe, kabupaten (aman untuk publik)
        $portsQuery = DB::table('landing_sites')
            ->leftJoin('regencies', 'landing_sites.regency_id', '=', 'regencies.id')
            ->where('landing_sites.is_active', true)
            ->whereNotNull('landing_sites.latitude')
            ->whereNotNull('landing_sites.longitude')
            ->whereBetween('landing_sites.latitude', [-90, 90])
            ->whereBetween('landing_sites.longitude', [-180, 180]);

        if (! empty($filters['landingSiteId'])) {
            $portsQuery->where('landing_sites.id', $filters['landingSiteId']);
        }

        $ports = $portsQuery
            ->select(
                'landing_sites.id',
                'landing_sites.name',
                'landing_sites.code',
                'landing_sites.site_type',
                'landing_sites.latitude as lat',
                'landing_sites.longitude as lng',
                'regencies.name as regency'
            )
            ->get()
            ->map(function ($site) {
                return [
                    'id' => $site->id,
                    'name' => $site->name,
                    'code' => $site->code ?? '-',
                    'type' => $site->site_type ?? 'TPI',
                    'lat' => (float) $site->lat,
                    'lng' => (float) $site->lng,
                    'regency' => $site->regency ?? '-',
                    'province' => 'Aceh',
                    'layer' => 'ports',
                    'layer_label' => 'Pelabuhan / TPI',
                ];
            });

        // Ringkasan agregat
        $effortPointsArr = $effortPoints->values()->all();
        $fishingGroundsArr = $fishingGrounds->values()->all();
        $portsArr = $ports->values()->all();

        $summary = [
            'total_effort_points' => count($effortPointsArr),
            'total_fishing_grounds' => count($fishingGroundsArr),
            'total_ports' => count($portsArr),
        ];

        return [
            'center' => ['lat' => 5.55, 'lng' => 95.32],
            'efforts' => $effortPointsArr,
            'fishing_efforts' => $effortPointsArr, // alias
            'fishing_grounds' => $fishingGroundsArr,
            'master_fishing_grounds' => $fishingGroundsArr,
            'ports' => $portsArr,
            'landing_sites' => $portsArr, // alias
            'vessels' => [],     // tidak ditampilkan di portal publik
            'homeports' => [],   // tidak ditampilkan di portal publik
            'logbooks' => [],    // tidak ditampilkan di portal publik
            'counts' => $summary,
            'meta' => [
                'source' => 'Public Statistics Portal',
                'note' => 'Data ini merupakan statistik agregat untuk kebutuhan informasi publik. '.
                          'Identitas operasional (nama kapal/nahkoda individual, nomor trip, dll.) tidak disertakan.',
                'generated_at' => now()->toISOString(),
            ],
        ];
    }
}

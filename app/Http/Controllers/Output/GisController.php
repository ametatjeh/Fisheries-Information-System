<?php

namespace App\Http\Controllers\Output;

use App\Http\Controllers\Controller;
use App\Models\FishCatch;
use App\Models\FishingEffort;
use App\Models\FishingGear;
use App\Models\FishingGround;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\Logbook;
use App\Models\Regency;
use App\Models\Species;
use App\Models\Vessel;
use App\Models\Wppnri;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GisController extends Controller
{
    /**
     * Tampilkan antarmuka Sistem Informasi Geografis (GIS / Peta).
     */
    public function index(Request $request): View
    {
        $filters = $this->extractFilters($request);

        $landingSites = LandingSite::where('is_active', true)->orderBy('name')->get();
        $gearsList = FishingGear::where('is_active', true)->orderBy('name')->get();
        $wppList = Wppnri::where('is_active', true)->orderBy('code')->get();
        $speciesList = Species::where('is_active', true)
            ->whereHas('catches')
            ->orderBy('local_name_id')
            ->get();
        $regenciesList = Regency::orderBy('name')->get();
        $yearsList = FishingTrip::whereNotNull('departure_date')
            ->pluck('departure_date')
            ->map(fn ($d) => (int) Carbon::parse($d)->year)
            ->unique()
            ->sortDesc()
            ->values();

        if ($yearsList->isEmpty()) {
            $yearsList = collect([2026]);
        }

        $stats = [
            'total_ports' => LandingSite::whereNotNull('latitude')->whereNotNull('longitude')->count(),
            'total_effort_points' => FishingEffort::whereNotNull('latitude_setting')->whereNotNull('longitude_setting')->count(),
            'total_fishing_grounds' => FishingGround::whereNotNull('latitude')->whereNotNull('longitude')->count(),
            'total_vessels' => Vessel::whereNotNull('homeport_site_id')->count(),
            'total_logbook_points' => Logbook::whereNotNull('latitude')->whereNotNull('longitude')->count(),
            'total_fma' => Wppnri::where('is_active', true)->count() ?: 2,
        ];

        $geoData = $this->buildGeoDataset($filters);

        return view('gis.index', compact(
            'filters',
            'landingSites',
            'gearsList',
            'wppList',
            'speciesList',
            'regenciesList',
            'yearsList',
            'stats',
            'geoData'
        ));
    }

    /**
     * Endpoint API JSON data spasial untuk pembaruan peta via Fetch / AJAX.
     */
    public function data(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);

        return response()->json($this->buildGeoDataset($filters));
    }

    /**
     * Ekstraksi filter query parameter dari request secara konsisten.
     *
     * @return array<string, mixed>
     */
    protected function extractFilters(Request $request): array
    {
        return [
            'landing_site_id' => $request->query('landing_site_id') ?? $request->query('site'),
            'gear_id' => $request->query('gear_id') ?? $request->query('fishing_gear_id') ?? $request->query('gear'),
            'wppnri_id' => $request->query('wppnri_id') ?? $request->query('wilayah'),
            'species_id' => $request->query('species_id') ?? $request->query('species'),
            'regency_id' => $request->query('regency_id'),
            'year' => $request->query('year') ?? $request->query('tahun'),
            'month' => $request->query('month') ?? $request->query('bulan'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];
    }

    /**
     * Bangun dataset spasial terstruktur untuk visualisasi peta & analisis analitis WPP.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function buildGeoDataset(array $filters): array
    {
        // 1. Layer Pelabuhan / Tempat Pendaratan Ikan (Landing Sites / TPI)
        $portsQuery = LandingSite::with(['regency', 'province'])
            ->withCount(['vessels', 'landings'])
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [-90, 90])
            ->whereBetween('longitude', [-180, 180]);

        if (! empty($filters['landing_site_id'])) {
            $portsQuery->where('id', $filters['landing_site_id']);
        }
        if (! empty($filters['regency_id'])) {
            $portsQuery->where('regency_id', $filters['regency_id']);
        }

        $ports = $portsQuery->get()->map(function ($site) {
            return [
                'id' => $site->id,
                'name' => $site->name,
                'code' => $site->code ?? '-',
                'type' => $site->site_type ?? 'TPI',
                'lat' => (float) $site->latitude,
                'lng' => (float) $site->longitude,
                'regency' => $site->regency?->name ?? '-',
                'province' => $site->province?->name ?? 'Aceh',
                'address' => $site->address ?? '-',
                'vessels_count' => (int) $site->vessels_count,
                'landings_count' => (int) $site->landings_count,
                'layer' => 'ports',
                'layer_label' => 'Pelabuhan / TPI',
            ];
        });

        // 2. Layer Master Daerah Penangkapan Ikan (Fishing Grounds Reference dengan koordinat valid)
        $groundsQuery = FishingGround::with('wppnri')
            ->withCount('fishingTrips')
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [-90, 90])
            ->whereBetween('longitude', [-180, 180]);

        if (! empty($filters['wppnri_id'])) {
            $groundsQuery->where('wppnri_id', $filters['wppnri_id']);
        }

        $fishingGrounds = $groundsQuery->get()->map(function ($fg) {
            return [
                'id' => $fg->id,
                'name' => $fg->name,
                'code' => $fg->code ?? '-',
                'wpp_code' => $fg->wppnri?->code ? ('WPP '.$fg->wppnri->code) : '-',
                'wpp_name' => $fg->wppnri?->name ?? '-',
                'lat' => (float) $fg->latitude,
                'lng' => (float) $fg->longitude,
                'description' => $fg->description ?? '-',
                'trips_count' => (int) $fg->fishing_trips_count,
                'layer' => 'fishing_grounds',
                'layer_label' => 'Master Fishing Ground',
            ];
        });

        // 2b. Master Daerah Penangkapan Ikan Aceh (Status Geometri Transparan, Tanpa Koordinat Palsu)
        $masterGroundsQuery = FishingGround::with('wppnri')
            ->withCount('fishingTrips')
            ->where('is_active', true);

        if (! empty($filters['wppnri_id'])) {
            $masterGroundsQuery->where('wppnri_id', $filters['wppnri_id']);
        }

        $masterFishingGrounds = $masterGroundsQuery->get()->map(function ($fg) {
            $hasCoords = ! is_null($fg->latitude) && ! is_null($fg->longitude)
                && $fg->latitude >= -90 && $fg->latitude <= 90
                && $fg->longitude >= -180 && $fg->longitude <= 180
                && (float) $fg->latitude != 0 && (float) $fg->longitude != 0;

            return [
                'id' => $fg->id,
                'name' => $fg->name,
                'code' => $fg->code ?? '-',
                'wpp_code' => $fg->wppnri?->code ? ('WPP '.$fg->wppnri->code) : '-',
                'wpp_name' => $fg->wppnri?->name ?? '-',
                'lat' => $hasCoords ? (float) $fg->latitude : null,
                'lng' => $hasCoords ? (float) $fg->longitude : null,
                'has_coordinates' => $hasCoords,
                'geometry_status' => $hasCoords ? 'Valid' : 'Belum Tersedia Geometri Resmi (Tanpa Titik Palsu)',
                'description' => $fg->description ?? '-',
                'trips_count' => (int) $fg->fishing_trips_count,
                'layer' => 'fishing_grounds',
                'layer_label' => 'Master Fishing Ground',
            ];
        });

        // 3. Layer Titik Penangkapan Aktual (Actual Fishing Effort Coordinates)
        $effortsQuery = FishingEffort::with([
            'fishingGear',
            'fishingTrip.vessel',
            'fishingTrip.captain',
            'fishingTrip.landingSite.regency',
            'fishingTrip.wppnri',
            'catches.species',
        ])
            ->whereNotNull('latitude_setting')
            ->whereNotNull('longitude_setting')
            ->whereBetween('latitude_setting', [-90, 90])
            ->whereBetween('longitude_setting', [-180, 180])
            ->where('latitude_setting', '!=', 0)
            ->where('longitude_setting', '!=', 0);

        if (! empty($filters['gear_id'])) {
            $effortsQuery->where('fishing_gear_id', $filters['gear_id']);
        }
        if (! empty($filters['start_date'])) {
            $effortsQuery->whereDate('setting_date', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $effortsQuery->whereDate('setting_date', '<=', $filters['end_date']);
        }
        if (! empty($filters['year'])) {
            $effortsQuery->where(function ($q) use ($filters) {
                $q->whereYear('setting_date', $filters['year'])
                    ->orWhereHas('fishingTrip', fn ($t) => $t->whereYear('departure_date', $filters['year']));
            });
        }
        if (! empty($filters['month'])) {
            $effortsQuery->where(function ($q) use ($filters) {
                $q->whereMonth('setting_date', $filters['month'])
                    ->orWhereHas('fishingTrip', fn ($t) => $t->whereMonth('departure_date', $filters['month']));
            });
        }
        if (! empty($filters['landing_site_id'])) {
            $effortsQuery->whereHas('fishingTrip', fn ($q) => $q->where('landing_site_id', $filters['landing_site_id']));
        }
        if (! empty($filters['wppnri_id'])) {
            $effortsQuery->whereHas('fishingTrip', fn ($q) => $q->where('wppnri_id', $filters['wppnri_id']));
        }
        if (! empty($filters['species_id'])) {
            $effortsQuery->whereHas('catches', fn ($q) => $q->where('fish_species_id', $filters['species_id']));
        }
        if (! empty($filters['regency_id'])) {
            $effortsQuery->whereHas('fishingTrip.landingSite', fn ($q) => $q->where('regency_id', $filters['regency_id']));
        }

        $efforts = $effortsQuery->limit(300)->get()->map(function ($effort) {
            // Hitung tangkapan khusus siklus effort ini (tanpa duplikasi trip catch)
            $effortCatch = (float) $effort->catches->sum('weight_kg');

            // Rincian komposisi spesies yang tertangkap pada setting ini
            $speciesList = $effort->catches->map(function ($c) {
                return [
                    'name' => $c->species?->local_name_id ?: ($c->species?->scientific_name ?: 'Ikan Campuran'),
                    'scientific' => $c->species?->scientific_name ?: '-',
                    'weight_kg' => (float) $c->weight_kg,
                ];
            })->values()->all();

            $wppCode = $effort->fishingTrip?->wppnri?->code
                ?: ($effort->fishingTrip?->fma_code ?: '-');
            $wppName = $effort->fishingTrip?->wppnri?->name
                ?: ($effort->fishingTrip?->fma_code ? ('WPPNRI '.$effort->fishingTrip->fma_code) : 'Tidak Terpetakan');

            return [
                'id' => $effort->id,
                'trip_code' => $effort->fishingTrip?->trip_number ?? '-',
                'vessel' => $effort->fishingTrip?->vessel?->name ?? 'Kapal Tangkap',
                'captain' => $effort->fishingTrip?->captain?->name ?? 'Nahkoda -',
                'gear' => $effort->fishingGear?->name ?? 'Alat Tangkap',
                'port' => $effort->fishingTrip?->landingSite?->name ?? '-',
                'regency' => $effort->fishingTrip?->landingSite?->regency?->name ?? '-',
                'wpp_code' => $wppCode,
                'wpp_name' => $wppName,
                'setting_num' => $effort->setting_number ?? 1,
                'lat_setting' => (float) $effort->latitude_setting,
                'lng_setting' => (float) $effort->longitude_setting,
                'lat_hauling' => $effort->latitude_hauling ? (float) $effort->latitude_hauling : null,
                'lng_hauling' => $effort->longitude_hauling ? (float) $effort->longitude_hauling : null,
                'setting_time' => $effort->setting_date ? $effort->setting_date->format('d M Y, H:i') : '-',
                'duration_hours' => (float) ($effort->duration_hours ?? 0),
                'catch_kg' => $effortCatch,
                'species_list' => $speciesList,
                'layer' => 'efforts',
                'layer_label' => 'Fishing Effort',
                'note' => 'Koordinat Setting Alat Tangkap Aktual',
            ];
        });

        // 4. Layer Armada Kapal & Pangkalan Asal (Vessel Registered Homeport Location)
        $vesselsQuery = Vessel::with(['homeportSite.regency', 'primaryGear', 'vesselType'])
            ->where('is_active', true)
            ->whereHas('homeportSite', function ($s) {
                $s->whereNotNull('latitude')->whereNotNull('longitude')
                    ->whereBetween('latitude', [-90, 90])
                    ->whereBetween('longitude', [-180, 180]);
            });

        if (! empty($filters['landing_site_id'])) {
            $vesselsQuery->where('homeport_site_id', $filters['landing_site_id']);
        }
        if (! empty($filters['gear_id'])) {
            $vesselsQuery->where('primary_gear_id', $filters['gear_id']);
        }
        if (! empty($filters['regency_id'])) {
            $vesselsQuery->whereHas('homeportSite', fn ($s) => $s->where('regency_id', $filters['regency_id']));
        }

        $vessels = $vesselsQuery->limit(300)->get()->map(function ($vessel) {
            return [
                'id' => $vessel->id,
                'name' => $vessel->name,
                'registration' => $vessel->registration_number ?? '-',
                'type' => $vessel->vesselType?->name ?? 'Kapal Motor',
                'gt' => (float) $vessel->gross_tonnage,
                'gear' => $vessel->primaryGear?->name ?? '-',
                'homeport' => $vessel->homeportSite?->name ?? '-',
                'regency' => $vessel->homeportSite?->regency?->name ?? '-',
                'lat' => (float) $vessel->homeportSite->latitude,
                'lng' => (float) $vessel->homeportSite->longitude,
                'layer' => 'vessels',
                'layer_label' => 'Homeport Kapal',
                'note' => 'Pangkalan Kapal Terdaftar (Bukan Live Tracking)',
            ];
        });

        // 5. Layer Posisi Logbook Harian Kapal (Historical Logbooks)
        $logbooksQuery = Logbook::with(['fishingTrip.vessel', 'fishingTrip.landingSite'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [-90, 90])
            ->whereBetween('longitude', [-180, 180])
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0);

        if (! empty($filters['start_date'])) {
            $logbooksQuery->whereDate('log_date', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $logbooksQuery->whereDate('log_date', '<=', $filters['end_date']);
        }
        if (! empty($filters['year'])) {
            $logbooksQuery->whereYear('log_date', $filters['year']);
        }
        if (! empty($filters['month'])) {
            $logbooksQuery->whereMonth('log_date', $filters['month']);
        }
        if (! empty($filters['landing_site_id'])) {
            $logbooksQuery->whereHas('fishingTrip', fn ($q) => $q->where('landing_site_id', $filters['landing_site_id']));
        }

        $logbooks = $logbooksQuery->limit(250)->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'trip_code' => $log->fishingTrip?->trip_number ?? '-',
                'vessel' => $log->fishingTrip?->vessel?->name ?? 'Kapal -',
                'port' => $log->fishingTrip?->landingSite?->name ?? '-',
                'lat' => (float) $log->latitude,
                'lng' => (float) $log->longitude,
                'date' => $log->log_date ? $log->log_date->format('d M Y') : '-',
                'time' => $log->log_time ?? '-',
                'weather' => $log->weather_condition ?? 'Cerah',
                'wave_height' => $log->wave_height_meters ? (float) $log->wave_height_meters : 0.5,
                'activity' => $log->activity_description ?? 'Operasi Penangkapan',
                'layer' => 'logbooks',
                'layer_label' => 'Titik Logbook Historis',
                'note' => 'Catatan Operasional Historis (Bukan Posisi Real-time)',
            ];
        });

        // 6. Analisis Spasial WPP-NRI (Catch, Trips, Effort, Species, Gear per WPP)
        $wppAnalysis = $this->buildWppSpatialAnalysis($filters);

        // Tentukan titik tengah peta (Center map)
        $centerLat = 5.55; // Banda Aceh / Perairan Aceh
        $centerLng = 95.32;
        if ($ports->isNotEmpty()) {
            $centerLat = $ports->first()['lat'];
            $centerLng = $ports->first()['lng'];
        } elseif ($efforts->isNotEmpty()) {
            $centerLat = $efforts->first()['lat_setting'];
            $centerLng = $efforts->first()['lng_setting'];
        }

        return [
            'center' => ['lat' => $centerLat, 'lng' => $centerLng],
            // Canonical keys
            'ports' => $ports,
            'fishing_grounds' => $fishingGrounds,
            'master_fishing_grounds' => $masterFishingGrounds,
            'efforts' => $efforts,
            'vessels' => $vessels,
            'logbooks' => $logbooks,
            'wpp_analysis' => $wppAnalysis,
            // Layer aliases
            'landing_sites' => $ports,
            'fishing_efforts' => $efforts,
            'homeports' => $vessels,
            'logbook_points' => $logbooks,
            'wpp' => $wppAnalysis,
            'counts' => [
                'ports' => $ports->count(),
                'landing_sites' => $ports->count(),
                'fishing_grounds' => $fishingGrounds->count(),
                'master_fishing_grounds' => $masterFishingGrounds->count(),
                'fishing_grounds_total' => $masterFishingGrounds->count(),
                'fishing_grounds_unmapped' => $masterFishingGrounds->where('has_coordinates', false)->count(),
                'efforts' => $efforts->count(),
                'fishing_efforts' => $efforts->count(),
                'vessels' => $vessels->count(),
                'homeports' => $vessels->count(),
                'logbooks' => $logbooks->count(),
                'logbook_points' => $logbooks->count(),
            ],
        ];
    }

    /**
     * Kalkulasi agregasi WPP bebas double counting dengan penanganan transparan NULL WPP.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    protected function buildWppSpatialAnalysis(array $filters): array
    {
        $wppList = Wppnri::where('is_active', true)->orderBy('code')->get();
        $analysis = [];

        foreach ($wppList as $wpp) {
            // Agregasi Catch mandiri
            $catchQuery = FishCatch::whereHas('fishingTrip', function ($t) use ($wpp, $filters) {
                $t->where('wppnri_id', $wpp->id);
                $this->applyTripFilters($t, $filters);
            });
            if (! empty($filters['species_id'])) {
                $catchQuery->where('fish_species_id', $filters['species_id']);
            }
            $totalCatchKg = (float) $catchQuery->sum('weight_kg');

            // Agregasi Trips mandiri
            $tripQuery = FishingTrip::where('wppnri_id', $wpp->id);
            $this->applyTripFilters($tripQuery, $filters);
            $totalTrips = $tripQuery->count();

            // Agregasi Effort mandiri
            $effortQuery = FishingEffort::whereHas('fishingTrip', function ($t) use ($wpp, $filters) {
                $t->where('wppnri_id', $wpp->id);
                $this->applyTripFilters($t, $filters);
            });
            if (! empty($filters['gear_id'])) {
                $effortQuery->where('fishing_gear_id', $filters['gear_id']);
            }
            $totalEffortPoints = $effortQuery->whereNotNull('latitude_setting')->whereNotNull('longitude_setting')->count();
            $totalDurationHours = (float) $effortQuery->sum('duration_hours');

            // Top Spesies pada WPP ini
            $topSpecies = FishCatch::with('species')
                ->whereHas('fishingTrip', function ($t) use ($wpp, $filters) {
                    $t->where('wppnri_id', $wpp->id);
                    $this->applyTripFilters($t, $filters);
                })
                ->select('fish_species_id', DB::raw('SUM(weight_kg) as total_kg'))
                ->groupBy('fish_species_id')
                ->orderByDesc('total_kg')
                ->limit(3)
                ->get()
                ->map(fn ($c) => [
                    'name' => $c->species?->local_name_id ?: ($c->species?->scientific_name ?: 'Lainnya'),
                    'weight_kg' => (float) $c->total_kg,
                ])->all();

            $analysis[] = [
                'id' => $wpp->id,
                'name' => $wpp->name,
                'code' => $wpp->code,
                'catch_kg' => $totalCatchKg,
                'catch_ton' => round($totalCatchKg / 1000, 2),
                'trips' => $totalTrips,
                'efforts' => $totalEffortPoints,
                'duration_hours' => $totalDurationHours,
                'cpue' => $totalTrips > 0 ? round($totalCatchKg / $totalTrips, 1) : 0,
                'cpue_trip' => $totalTrips > 0 ? round($totalCatchKg / $totalTrips, 1) : 0,
                'cpue_hourly' => $totalDurationHours > 0 ? round($totalCatchKg / $totalDurationHours, 2) : 0,
                'top_species' => $topSpecies,
            ];
        }

        // Kategori "Tidak Terpetakan" untuk wppnri_id IS NULL
        $unmappedCatchQuery = FishCatch::whereHas('fishingTrip', function ($t) use ($filters) {
            $t->whereNull('wppnri_id');
            $this->applyTripFilters($t, $filters);
        });
        if (! empty($filters['species_id'])) {
            $unmappedCatchQuery->where('fish_species_id', $filters['species_id']);
        }
        $unmappedCatchKg = (float) $unmappedCatchQuery->sum('weight_kg');

        $unmappedTripQuery = FishingTrip::whereNull('wppnri_id');
        $this->applyTripFilters($unmappedTripQuery, $filters);
        $unmappedTrips = $unmappedTripQuery->count();

        $unmappedEffortQuery = FishingEffort::whereHas('fishingTrip', function ($t) use ($filters) {
            $t->whereNull('wppnri_id');
            $this->applyTripFilters($t, $filters);
        });
        if (! empty($filters['gear_id'])) {
            $unmappedEffortQuery->where('fishing_gear_id', $filters['gear_id']);
        }
        $unmappedEfforts = $unmappedEffortQuery->whereNotNull('latitude_setting')->whereNotNull('longitude_setting')->count();
        $unmappedDuration = (float) $unmappedEffortQuery->sum('duration_hours');

        $unmappedTopSpecies = FishCatch::with('species')
            ->whereHas('fishingTrip', function ($t) use ($filters) {
                $t->whereNull('wppnri_id');
                $this->applyTripFilters($t, $filters);
            })
            ->select('fish_species_id', DB::raw('SUM(weight_kg) as total_kg'))
            ->groupBy('fish_species_id')
            ->orderByDesc('total_kg')
            ->limit(3)
            ->get()
            ->map(fn ($c) => [
                'name' => $c->species?->local_name_id ?: ($c->species?->scientific_name ?: 'Lainnya'),
                'weight_kg' => (float) $c->total_kg,
            ])->all();

        if ($unmappedCatchKg > 0 || $unmappedTrips > 0 || $unmappedEfforts > 0) {
            $analysis[] = [
                'id' => null,
                'name' => 'Tidak Terpetakan (WPP Belum Diisi)',
                'code' => '-',
                'catch_kg' => $unmappedCatchKg,
                'catch_ton' => round($unmappedCatchKg / 1000, 2),
                'trips' => $unmappedTrips,
                'efforts' => $unmappedEfforts,
                'duration_hours' => $unmappedDuration,
                'cpue' => $unmappedTrips > 0 ? round($unmappedCatchKg / $unmappedTrips, 1) : 0,
                'cpue_trip' => $unmappedTrips > 0 ? round($unmappedCatchKg / $unmappedTrips, 1) : 0,
                'cpue_hourly' => $unmappedDuration > 0 ? round($unmappedCatchKg / $unmappedDuration, 2) : 0,
                'top_species' => $unmappedTopSpecies,
            ];
        }

        return $analysis;
    }

    /**
     * Menerapkan filter pelayaran pada builder query trip.
     *
     * @param  Builder|\Illuminate\Database\Query\Builder  $query
     * @param  array<string, mixed>  $filters
     */
    protected function applyTripFilters($query, array $filters): void
    {
        if (! empty($filters['year'])) {
            $query->whereYear('departure_date', $filters['year']);
        }
        if (! empty($filters['month'])) {
            $query->whereMonth('departure_date', $filters['month']);
        }
        if (! empty($filters['landing_site_id'])) {
            $query->where('landing_site_id', $filters['landing_site_id']);
        }
        if (! empty($filters['gear_id'])) {
            $query->where('primary_gear_id', $filters['gear_id']);
        }
        if (! empty($filters['start_date'])) {
            $query->whereDate('departure_date', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $query->whereDate('departure_date', '<=', $filters['end_date']);
        }
    }
}

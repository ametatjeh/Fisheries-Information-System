<?php

namespace App\Services\Gfw;

use App\Models\Gfw\GfwVesselPresence;
use App\Models\GfwVesselActivity;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class GfwActivityService
{
    protected string $defaultActivityDataset;

    protected int $activityCacheTtl;

    public const LATENCY_NOTICE = 'Data observasi satelit AIS/VMS memiliki latensi (delay berkala 24-72 jam) dan bukan merupakan posisi langsung (real-time live stream).';

    public function __construct(
        protected GfwApiService $apiService,
        protected GfwRegionService $regionService
    ) {
        $this->defaultActivityDataset = (string) config('gfw.activity_dataset', 'public-global-vessel-tracks:latest');
        $this->activityCacheTtl = (int) config('gfw.activity_cache_ttl', 3600);
    }

    /**
     * Retrieve activity tracks / positions for a specific GFW vessel.
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, source: string, latency_notice: string, query_period: array{start_date: string, end_date: string}, total: int, cached: bool, data: list<array<string, mixed>>, error?: string}
     */
    public function getVesselActivity(string $vesselId, array $options = []): array
    {
        $cleanId = trim($vesselId);
        if ($cleanId === '') {
            return [
                'success' => false,
                'source' => 'global_fishing_watch',
                'latency_notice' => self::LATENCY_NOTICE,
                'query_period' => ['start_date' => '', 'end_date' => ''],
                'total' => 0,
                'cached' => false,
                'data' => [],
                'error' => 'Vessel ID cannot be empty.',
            ];
        }

        $dateResult = $this->parseDateRange(
            $options['start_date'] ?? null,
            $options['end_date'] ?? null,
            7
        );

        if (! $dateResult['valid']) {
            return [
                'success' => false,
                'source' => 'global_fishing_watch',
                'latency_notice' => self::LATENCY_NOTICE,
                'query_period' => ['start_date' => '', 'end_date' => ''],
                'total' => 0,
                'cached' => false,
                'data' => [],
                'error' => $dateResult['error'] ?? 'Format tanggal tidak valid.',
            ];
        }

        $startDate = $dateResult['start_date'];
        $endDate = $dateResult['end_date'];
        $dataset = (string) ($options['dataset'] ?? $this->defaultActivityDataset);
        $forceRefresh = (bool) ($options['refresh'] ?? false);

        $cacheKey = 'gfw:activity:vessel:'.$cleanId.':'.md5($startDate.':'.$endDate.':'.$dataset);

        if (! $forceRefresh && Cache::has($cacheKey)) {
            /** @var list<array<string, mixed>> $cachedData */
            $cachedData = Cache::get($cacheKey, []);

            return [
                'success' => true,
                'source' => 'global_fishing_watch',
                'latency_notice' => self::LATENCY_NOTICE,
                'query_period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'total' => count($cachedData),
                'cached' => true,
                'data' => $cachedData,
            ];
        }

        $queryParams = [
            'datasets[0]' => $dataset,
            'start-date' => $startDate,
            'end-date' => $endDate,
        ];

        $response = $this->apiService->get("/vessels/{$cleanId}/tracks", $queryParams);

        if (! $response['success']) {
            // Check database fallback from sistem_gfw.gfw_vessel_presence
            try {
                $localPresences = GfwVesselPresence::where('gfw_vessel_id', $cleanId)
                    ->orderBy('observed_at', 'asc')
                    ->get();

                if ($localPresences->isNotEmpty()) {
                    $normalizedList = $localPresences->map(function ($p) use ($cleanId) {
                        return [
                            'gfw_vessel_id' => $cleanId,
                            'timestamp' => $p->observed_at?->toIso8601String(),
                            'date' => $p->observed_at?->toIso8601String(),
                            'observed_at' => $p->observed_at?->toIso8601String(),
                            'latitude' => $p->latitude,
                            'longitude' => $p->longitude,
                            'lat' => $p->latitude,
                            'lon' => $p->longitude,
                            'speed_knots' => $p->speed,
                            'speed' => $p->speed,
                            'course' => $p->course,
                            'source' => 'sistem_gfw_presence',
                        ];
                    })->values()->all();

                    return [
                        'success' => true,
                        'source' => 'sistem_gfw',
                        'latency_notice' => self::LATENCY_NOTICE,
                        'query_period' => [
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                        ],
                        'total' => count($normalizedList),
                        'cached' => true,
                        'data' => $normalizedList,
                    ];
                }
            } catch (Throwable) {
                // Ignore and proceed to standard error response
            }

            return [
                'success' => false,
                'source' => 'global_fishing_watch',
                'latency_notice' => self::LATENCY_NOTICE,
                'query_period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'total' => 0,
                'cached' => false,
                'data' => [],
                'error' => $response['error'] ?? 'Gagal mengambil data track aktivitas kapal dari GFW.',
            ];
        }

        $rawEntries = $response['data']['entries'] ?? $response['data']['data'] ?? $response['data'] ?? [];
        if (! is_array($rawEntries)) {
            $rawEntries = [];
        }

        $normalizedList = [];
        foreach ($rawEntries as $entry) {
            if (is_array($entry)) {
                $normalized = $this->normalizeActivityPoint($entry, $cleanId, $startDate, $endDate);
                $this->persistActivity($normalized);
                $normalizedList[] = $normalized;
            }
        }

        Cache::put($cacheKey, $normalizedList, $this->activityCacheTtl);

        return [
            'success' => true,
            'source' => 'global_fishing_watch',
            'latency_notice' => self::LATENCY_NOTICE,
            'query_period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'total' => count($normalizedList),
            'cached' => false,
            'data' => $normalizedList,
        ];
    }

    /**
     * Retrieve vessel presence across geographic regions (e.g. indonesia_eez, aceh_waters, wppnri_571).
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, source: string, latency_notice: string, region: array<string, mixed>, query_period: array{start_date: string, end_date: string}, total: int, cached: bool, data: list<array<string, mixed>>, error?: string}
     */
    public function getVesselPresence(string $regionKey = 'indonesia_eez', array $options = []): array
    {
        $region = $this->regionService->getRegion($regionKey);
        if (! $region) {
            return [
                'success' => false,
                'source' => 'global_fishing_watch',
                'latency_notice' => self::LATENCY_NOTICE,
                'region' => ['key' => $regionKey, 'name' => 'Unknown', 'type' => 'unknown'],
                'query_period' => ['start_date' => '', 'end_date' => ''],
                'total' => 0,
                'cached' => false,
                'data' => [],
                'error' => "Region [{$regionKey}] tidak ditemukan atau belum didukung.",
            ];
        }

        $dateResult = $this->parseDateRange(
            $options['start_date'] ?? null,
            $options['end_date'] ?? null,
            14
        );

        if (! $dateResult['valid']) {
            return [
                'success' => false,
                'source' => 'global_fishing_watch',
                'latency_notice' => self::LATENCY_NOTICE,
                'region' => $region,
                'query_period' => ['start_date' => '', 'end_date' => ''],
                'total' => 0,
                'cached' => false,
                'data' => [],
                'error' => $dateResult['error'] ?? 'Format tanggal tidak valid.',
            ];
        }

        $startDate = $dateResult['start_date'];
        $endDate = $dateResult['end_date'];
        $limit = max(1, min((int) ($options['limit'] ?? 20), 100));
        $forceRefresh = (bool) ($options['refresh'] ?? false);

        $cacheKey = 'gfw:activity:presence:'.$region['key'].':'.md5($startDate.':'.$endDate.':'.$limit);

        if (! $forceRefresh && Cache::has($cacheKey)) {
            /** @var list<array<string, mixed>> $cachedData */
            $cachedData = Cache::get($cacheKey, []);

            return [
                'success' => true,
                'source' => 'global_fishing_watch',
                'latency_notice' => self::LATENCY_NOTICE,
                'region' => $region,
                'query_period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'total' => count($cachedData),
                'cached' => true,
                'data' => $cachedData,
            ];
        }

        $geoQuery = $this->regionService->buildQueryParams($region['key'], [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $apiParams = array_merge($geoQuery['query_parameters'], [
            'limit' => $limit,
        ]);

        $response = $this->apiService->get('/vessels/activity', $apiParams);

        if (! $response['success']) {
            return [
                'success' => false,
                'source' => 'global_fishing_watch',
                'latency_notice' => self::LATENCY_NOTICE,
                'region' => $region,
                'query_period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'total' => 0,
                'cached' => false,
                'data' => [],
                'error' => $response['error'] ?? 'Gagal mengambil data vessel presence dari GFW API.',
            ];
        }

        $rawEntries = $response['data']['entries'] ?? $response['data']['data'] ?? $response['data'] ?? [];
        if (! is_array($rawEntries)) {
            $rawEntries = [];
        }

        $normalizedList = [];
        foreach ($rawEntries as $entry) {
            if (is_array($entry)) {
                $normalized = $this->normalizePresenceItem($entry, $region['key'], $startDate, $endDate);
                $this->persistActivity($normalized);
                $normalizedList[] = $normalized;
            }
        }

        Cache::put($cacheKey, $normalizedList, $this->activityCacheTtl);

        return [
            'success' => true,
            'source' => 'global_fishing_watch',
            'latency_notice' => self::LATENCY_NOTICE,
            'region' => $region,
            'query_period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'total' => count($normalizedList),
            'cached' => false,
            'data' => $normalizedList,
        ];
    }

    /**
     * Parse and validate start and end date range.
     *
     * @return array{valid: bool, start_date?: string, end_date?: string, error?: string}
     */
    public function parseDateRange(?string $startDate, ?string $endDate, int $defaultDays = 7): array
    {
        $timezone = config('app.timezone', 'Asia/Jakarta');

        try {
            $end = $endDate ? Carbon::parse($endDate, $timezone)->endOfDay() : Carbon::now($timezone)->subDays(3)->endOfDay();
            $start = $startDate ? Carbon::parse($startDate, $timezone)->startOfDay() : (clone $end)->subDays($defaultDays)->startOfDay();

            if ($start->gt($end)) {
                return [
                    'valid' => false,
                    'error' => 'start_date tidak boleh lebih besar daripada end_date.',
                ];
            }

            // Max query window limit (90 days)
            if ($start->diffInDays($end) > 90) {
                return [
                    'valid' => false,
                    'error' => 'Rentang tanggal maksimal query adalah 90 hari.',
                ];
            }

            return [
                'valid' => true,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ];
        } catch (InvalidFormatException|Throwable $e) {
            return [
                'valid' => false,
                'error' => 'Format tanggal tidak valid. Gunakan format YYYY-MM-DD.',
            ];
        }
    }

    /**
     * Normalize a single vessel activity / track point.
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    public function normalizeActivityPoint(array $raw, string $vesselId, string $startDate, string $endDate): array
    {
        $lat = $raw['lat'] ?? $raw['latitude'] ?? ($raw['position']['lat'] ?? null);
        $lon = $raw['lon'] ?? $raw['longitude'] ?? ($raw['position']['lon'] ?? null);
        $timestamp = $raw['timestamp'] ?? $raw['datetime'] ?? $raw['time'] ?? null;
        $speed = $raw['speedKnots'] ?? $raw['speed'] ?? $raw['speed_knots'] ?? null;
        $distance = $raw['distanceKm'] ?? $raw['distance_km'] ?? null;
        $hours = $raw['hours'] ?? $raw['apparentFishingHours'] ?? null;

        return [
            'gfw_vessel_id' => $vesselId,
            'activity_type' => 'track_point',
            'region_key' => $raw['region'] ?? null,
            'latitude' => is_numeric($lat) ? round((float) $lat, 6) : null,
            'longitude' => is_numeric($lon) ? round((float) $lon, 6) : null,
            'observation_timestamp' => $timestamp ? Carbon::parse($timestamp)->toIso8601String() : null,
            'period_start' => $startDate,
            'period_end' => $endDate,
            'speed_knots' => is_numeric($speed) ? round((float) $speed, 2) : null,
            'distance_km' => is_numeric($distance) ? round((float) $distance, 2) : null,
            'hours' => is_numeric($hours) ? round((float) $hours, 2) : null,
            'raw_data' => $raw,
            'last_synced_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Normalize a regional vessel presence observation entry.
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    public function normalizePresenceItem(array $raw, string $regionKey, string $startDate, string $endDate): array
    {
        $vesselId = $raw['vesselId'] ?? $raw['id'] ?? ($raw['vessel']['id'] ?? null);
        $lat = $raw['lat'] ?? $raw['latitude'] ?? ($raw['position']['lat'] ?? null);
        $lon = $raw['lon'] ?? $raw['longitude'] ?? ($raw['position']['lon'] ?? null);
        $timestamp = $raw['timestamp'] ?? $raw['datetime'] ?? $raw['lastObservation'] ?? null;
        $hours = $raw['hours'] ?? $raw['presenceHours'] ?? $raw['apparentFishingHours'] ?? null;
        $activityType = $raw['activityType'] ?? $raw['type'] ?? 'presence';

        return [
            'gfw_vessel_id' => $vesselId !== null ? (string) $vesselId : null,
            'activity_type' => (string) $activityType,
            'region_key' => $regionKey,
            'latitude' => is_numeric($lat) ? round((float) $lat, 6) : null,
            'longitude' => is_numeric($lon) ? round((float) $lon, 6) : null,
            'observation_timestamp' => $timestamp ? Carbon::parse($timestamp)->toIso8601String() : null,
            'period_start' => $startDate,
            'period_end' => $endDate,
            'hours' => is_numeric($hours) ? round((float) $hours, 2) : null,
            'speed_knots' => isset($raw['speedKnots']) && is_numeric($raw['speedKnots']) ? round((float) $raw['speedKnots'], 2) : null,
            'distance_km' => null,
            'raw_data' => $raw,
            'last_synced_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Persist normalized activity / presence snapshot to gfw_vessel_activities table safely.
     *
     * @param  array<string, mixed>  $data
     */
    protected function persistActivity(array $data): ?GfwVesselActivity
    {
        try {
            return GfwVesselActivity::create([
                'gfw_vessel_id' => $data['gfw_vessel_id'] ?? null,
                'activity_type' => $data['activity_type'] ?? 'presence',
                'region_key' => $data['region_key'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'observation_timestamp' => $data['observation_timestamp'] ?? null,
                'period_start' => $data['period_start'] ?? null,
                'period_end' => $data['period_end'] ?? null,
                'hours' => $data['hours'] ?? null,
                'distance_km' => $data['distance_km'] ?? null,
                'speed_knots' => $data['speed_knots'] ?? null,
                'raw_data' => $data['raw_data'] ?? null,
                'last_synced_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed persisting GFW vessel activity snapshot', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}

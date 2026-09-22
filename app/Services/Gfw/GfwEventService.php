<?php

namespace App\Services\Gfw;

use App\Models\GfwEvent;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class GfwEventService
{
    public const TYPE_FISHING = 'apparent_fishing';

    public const TYPE_ENCOUNTER = 'potential_encounter';

    public const TYPE_LOITERING = 'loitering';

    public const TYPE_PORT_VISIT = 'port_visit';

    public const SEMANTIC_LABELS = [
        self::TYPE_FISHING => 'Apparent Fishing Event',
        self::TYPE_ENCOUNTER => 'Potential Encounter',
        self::TYPE_LOITERING => 'Loitering Event',
        self::TYPE_PORT_VISIT => 'Port Visit',
    ];

    public const SEMANTIC_DISCLAIMERS = [
        self::TYPE_FISHING => 'Peristiwa ini merupakan indikasi penangkapan ikan berdasarkan model analitik algoritma pergerakan AIS/VMS (Apparent Fishing Event) dan bukan merupakan verifikasi penangkapan faktual atau kesimpulan penangkapan ikan ilegal.',
        self::TYPE_ENCOUNTER => 'Peristiwa ini mendeteksi kedekatan posisi dua kapal (Potential Encounter) secara algoritmik dan tidak dapat disimpulkan sebagai alih muatan (transshipment) atau aktivitas ilegal tanpa bukti investigasi lapangan.',
        self::TYPE_LOITERING => 'Peristiwa perlambatan atau pola menunggu kapal di perairan (Loitering Event) teridentifikasi secara analitik dan bukan merupakan kesimpulan aktivitas terlarang.',
        self::TYPE_PORT_VISIT => 'Peristiwa persinggahan pelabuhan (Port Visit) didasarkan pada perlintasan geofence pelabuhan dan bukan merupakan bukti langsung pendaratan atau pembongkaran hasil tangkapan lokal.',
    ];

    protected int $eventCacheTtl;

    public function __construct(
        protected GfwApiService $apiService,
        protected GfwRegionService $regionService
    ) {
        $this->eventCacheTtl = (int) config('gfw.event_cache_ttl', 3600);
    }

    /**
     * Query Apparent Fishing Events.
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, source: string, event_type: string, semantic_label: string, semantic_disclaimer: string, region: array<string, mixed>, query_period: array{start_date: string, end_date: string}, total: int, cached: bool, data: list<array<string, mixed>>, error?: string}
     */
    public function fishingEvents(array $options = []): array
    {
        return $this->queryEvents(
            self::TYPE_FISHING,
            (string) config('gfw.fishing_events_dataset', 'public-global-fishing-events:latest'),
            $options
        );
    }

    /**
     * Query Potential Encounters between vessels.
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, source: string, event_type: string, semantic_label: string, semantic_disclaimer: string, region: array<string, mixed>, query_period: array{start_date: string, end_date: string}, total: int, cached: bool, data: list<array<string, mixed>>, error?: string}
     */
    public function encounters(array $options = []): array
    {
        return $this->queryEvents(
            self::TYPE_ENCOUNTER,
            (string) config('gfw.encounters_dataset', 'public-global-encounters:latest'),
            $options
        );
    }

    /**
     * Query Loitering Events.
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, source: string, event_type: string, semantic_label: string, semantic_disclaimer: string, region: array<string, mixed>, query_period: array{start_date: string, end_date: string}, total: int, cached: bool, data: list<array<string, mixed>>, error?: string}
     */
    public function loitering(array $options = []): array
    {
        return $this->queryEvents(
            self::TYPE_LOITERING,
            (string) config('gfw.loitering_dataset', 'public-global-loitering-events:latest'),
            $options
        );
    }

    /**
     * Query Port Visits.
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, source: string, event_type: string, semantic_label: string, semantic_disclaimer: string, region: array<string, mixed>, query_period: array{start_date: string, end_date: string}, total: int, cached: bool, data: list<array<string, mixed>>, error?: string}
     */
    public function portVisits(array $options = []): array
    {
        return $this->queryEvents(
            self::TYPE_PORT_VISIT,
            (string) config('gfw.port_visits_dataset', 'public-global-port-visits-c2:latest'),
            $options
        );
    }

    /**
     * Look up a single GFW event by its ID.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>|null
     */
    public function getEventById(string $eventId, array $options = []): ?array
    {
        $cleanId = trim($eventId);
        if ($cleanId === '') {
            return null;
        }

        $forceRefresh = (bool) ($options['refresh'] ?? false);
        $cacheKey = 'gfw:events:id:'.$cleanId;

        if (! $forceRefresh && Cache::has($cacheKey)) {
            /** @var array<string, mixed>|null $cached */
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        // Check local GFW table snapshot first if not force refreshed
        if (! $forceRefresh) {
            $existing = GfwEvent::where('gfw_event_id', $cleanId)->first();
            if ($existing) {
                $normalized = $this->modelToNormalized($existing);
                Cache::put($cacheKey, $normalized, $this->eventCacheTtl);

                return $normalized;
            }
        }

        $response = $this->apiService->get("/events/{$cleanId}");

        if (! $response['success'] || empty($response['data'])) {
            return null;
        }

        $raw = $response['data'];
        $type = $this->mapRawEventType((string) ($raw['type'] ?? $raw['eventType'] ?? self::TYPE_FISHING));
        $normalized = $this->normalizeEvent($raw, $type, 'custom');

        $this->persistEvent($normalized);
        Cache::put($cacheKey, $normalized, $this->eventCacheTtl);

        return $normalized;
    }

    /**
     * Internal event query pipeline with regional filtering, date validation, and caching.
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, source: string, event_type: string, semantic_label: string, semantic_disclaimer: string, region: array<string, mixed>, query_period: array{start_date: string, end_date: string}, total: int, cached: bool, data: list<array<string, mixed>>, error?: string}
     */
    protected function queryEvents(string $eventType, string $dataset, array $options = []): array
    {
        $regionKey = (string) ($options['region'] ?? 'indonesia_eez');
        $region = $this->regionService->getRegion($regionKey);

        $semanticLabel = self::SEMANTIC_LABELS[$eventType] ?? 'GFW Event';
        $semanticDisclaimer = self::SEMANTIC_DISCLAIMERS[$eventType] ?? 'Data peristiwa berdasarkan inferensi analitik satelit.';

        if (! $region) {
            return [
                'success' => false,
                'source' => 'global_fishing_watch',
                'event_type' => $eventType,
                'semantic_label' => $semanticLabel,
                'semantic_disclaimer' => $semanticDisclaimer,
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
                'event_type' => $eventType,
                'semantic_label' => $semanticLabel,
                'semantic_disclaimer' => $semanticDisclaimer,
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
        $vesselId = ! empty($options['vessel_id']) ? trim((string) $options['vessel_id']) : null;
        $limit = max(1, min((int) ($options['limit'] ?? 20), 100));
        $forceRefresh = (bool) ($options['refresh'] ?? false);

        $cacheKey = 'gfw:events:'.$eventType.':'.$region['key'].':'.md5($startDate.':'.$endDate.':'.($vesselId ?? 'all').':'.$limit);

        if (! $forceRefresh && Cache::has($cacheKey)) {
            /** @var list<array<string, mixed>> $cachedData */
            $cachedData = Cache::get($cacheKey, []);

            return [
                'success' => true,
                'source' => 'global_fishing_watch',
                'event_type' => $eventType,
                'semantic_label' => $semanticLabel,
                'semantic_disclaimer' => $semanticDisclaimer,
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
            'datasets[0]' => $dataset,
            'start-date' => $startDate,
            'end-date' => $endDate,
            'limit' => $limit,
            'offset' => (int) ($options['offset'] ?? 0),
        ]);

        if ($vesselId) {
            $apiParams['vessels[0]'] = $vesselId;
        }

        unset($apiParams['start_date'], $apiParams['end_date']);

        $response = $this->apiService->get('/events', $apiParams);

        if (! $response['success']) {
            return [
                'success' => false,
                'source' => 'global_fishing_watch',
                'event_type' => $eventType,
                'semantic_label' => $semanticLabel,
                'semantic_disclaimer' => $semanticDisclaimer,
                'region' => $region,
                'query_period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'total' => 0,
                'cached' => false,
                'data' => [],
                'error' => $response['error'] ?? "Gagal mengambil data {$semanticLabel} dari GFW API.",
            ];
        }

        $rawEntries = $response['data']['entries'] ?? $response['data']['data'] ?? $response['data'] ?? [];
        if (! is_array($rawEntries)) {
            $rawEntries = [];
        }

        $normalizedList = [];
        foreach ($rawEntries as $entry) {
            if (is_array($entry)) {
                $normalized = $this->normalizeEvent($entry, $eventType, $region['key']);
                $this->persistEvent($normalized);
                $normalizedList[] = $normalized;
            }
        }

        Cache::put($cacheKey, $normalizedList, $this->eventCacheTtl);

        return [
            'success' => true,
            'source' => 'global_fishing_watch',
            'event_type' => $eventType,
            'semantic_label' => $semanticLabel,
            'semantic_disclaimer' => $semanticDisclaimer,
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
    public function parseDateRange(?string $startDate, ?string $endDate, int $defaultDays = 14): array
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
     * Normalize heterogeneous GFW API event item.
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    public function normalizeEvent(array $raw, string $eventType, string $regionKey): array
    {
        $eventId = (string) ($raw['id'] ?? $raw['eventId'] ?? '');
        $vesselId = $raw['vessel']['id'] ?? $raw['vesselId'] ?? ($raw['vessels'][0]['id'] ?? null);
        $secondaryVesselId = $raw['vessels'][1]['id'] ?? ($raw['encounterVessel']['id'] ?? null);

        $lat = $raw['position']['lat'] ?? $raw['lat'] ?? $raw['latitude'] ?? ($raw['meanPosition']['lat'] ?? null);
        $lon = $raw['position']['lon'] ?? $raw['lon'] ?? $raw['longitude'] ?? ($raw['meanPosition']['lon'] ?? null);

        $startTime = $raw['start'] ?? $raw['startTime'] ?? $raw['datetime'] ?? null;
        $endTime = $raw['end'] ?? $raw['endTime'] ?? null;

        $durationHours = null;
        if (isset($raw['durationHours']) && is_numeric($raw['durationHours'])) {
            $durationHours = (float) $raw['durationHours'];
        } elseif ($startTime && $endTime) {
            try {
                $startC = Carbon::parse($startTime);
                $endC = Carbon::parse($endTime);
                $durationHours = round($startC->diffInMinutes($endC) / 60, 2);
            } catch (Throwable) {
                $durationHours = null;
            }
        }

        $confidence = $raw['confidence'] ?? ($raw['apparentFishing']['confidence'] ?? null);
        $portName = $raw['port']['name'] ?? $raw['portName'] ?? null;

        return [
            'gfw_event_id' => $eventId !== '' ? $eventId : null,
            'event_type' => $eventType,
            'semantic_label' => self::SEMANTIC_LABELS[$eventType] ?? 'GFW Event',
            'semantic_disclaimer' => self::SEMANTIC_DISCLAIMERS[$eventType] ?? '',
            'gfw_vessel_id' => $vesselId !== null ? (string) $vesselId : null,
            'secondary_vessel_id' => $secondaryVesselId !== null ? (string) $secondaryVesselId : null,
            'region_key' => $regionKey,
            'latitude' => is_numeric($lat) ? round((float) $lat, 6) : null,
            'longitude' => is_numeric($lon) ? round((float) $lon, 6) : null,
            'start_time' => $startTime ? Carbon::parse($startTime)->toIso8601String() : null,
            'end_time' => $endTime ? Carbon::parse($endTime)->toIso8601String() : null,
            'duration_hours' => $durationHours !== null ? round((float) $durationHours, 2) : null,
            'confidence' => $confidence !== null ? (string) $confidence : null,
            'port_name' => $portName !== null ? trim((string) $portName) : null,
            'raw_data' => $raw,
            'last_synced_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Persist normalized event to gfw_events table safely.
     *
     * @param  array<string, mixed>  $data
     */
    protected function persistEvent(array $data): ?GfwEvent
    {
        if (empty($data['gfw_event_id'])) {
            return null;
        }

        try {
            return GfwEvent::updateOrCreate(
                ['gfw_event_id' => $data['gfw_event_id']],
                [
                    'event_type' => $data['event_type'],
                    'gfw_vessel_id' => $data['gfw_vessel_id'],
                    'secondary_vessel_id' => $data['secondary_vessel_id'],
                    'region_key' => $data['region_key'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'duration_hours' => $data['duration_hours'],
                    'confidence' => $data['confidence'],
                    'port_name' => $data['port_name'],
                    'raw_data' => $data['raw_data'],
                    'last_synced_at' => now(),
                ]
            );
        } catch (Throwable $e) {
            Log::warning('Failed persisting GFW event snapshot', [
                'gfw_event_id' => $data['gfw_event_id'],
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Map raw GFW API event type string to standard application enum key.
     */
    protected function mapRawEventType(string $rawType): string
    {
        $normalized = strtolower(trim($rawType));

        return match ($normalized) {
            'fishing', 'apparent_fishing', 'apparentfishing' => self::TYPE_FISHING,
            'encounter', 'potential_encounter', 'potentialencounter' => self::TYPE_ENCOUNTER,
            'loitering', 'loitering_event' => self::TYPE_LOITERING,
            'port_visit', 'portvisit', 'port' => self::TYPE_PORT_VISIT,
            default => self::TYPE_FISHING,
        };
    }

    /**
     * Convert GfwEvent Eloquent model to normalized shape.
     *
     * @return array<string, mixed>
     */
    protected function modelToNormalized(GfwEvent $event): array
    {
        return [
            'gfw_event_id' => $event->gfw_event_id,
            'event_type' => $event->event_type,
            'semantic_label' => self::SEMANTIC_LABELS[$event->event_type] ?? 'GFW Event',
            'semantic_disclaimer' => self::SEMANTIC_DISCLAIMERS[$event->event_type] ?? '',
            'gfw_vessel_id' => $event->gfw_vessel_id,
            'secondary_vessel_id' => $event->secondary_vessel_id,
            'region_key' => $event->region_key,
            'latitude' => $event->latitude,
            'longitude' => $event->longitude,
            'start_time' => $event->start_time?->toIso8601String(),
            'end_time' => $event->end_time?->toIso8601String(),
            'duration_hours' => $event->duration_hours,
            'confidence' => $event->confidence,
            'port_name' => $event->port_name,
            'raw_data' => $event->raw_data,
            'last_synced_at' => $event->last_synced_at?->toIso8601String(),
        ];
    }
}

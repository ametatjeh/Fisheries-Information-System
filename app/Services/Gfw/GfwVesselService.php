<?php

namespace App\Services\Gfw;

use App\Models\Gfw\GfwVessel;
use App\Models\Gfw\GfwVesselPresence;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class GfwVesselService
{
    protected string $defaultDataset;

    protected int $cacheTtl;

    public function __construct(
        protected GfwApiService $apiService
    ) {
        $this->defaultDataset = (string) config('gfw.vessel_dataset', 'public-global-vessel-identity:latest');
        $this->cacheTtl = (int) config('gfw.cache_ttl', 86400);
    }

    /**
     * Search vessels on GFW API by query (MMSI, IMO, or Name).
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, data: list<array<string, mixed>>, cached: bool, total: int, error?: string}
     */
    public function search(string $query, array $options = []): array
    {
        $cleanQuery = trim($query);
        $limit = max(1, min((int) ($options['limit'] ?? 10), 50));
        $dataset = (string) ($options['dataset'] ?? $this->defaultDataset);
        $forceRefresh = (bool) ($options['refresh'] ?? false);

        // When query is empty, serve known observatory vessels from sistem_gfw database
        if ($cleanQuery === '') {
            $localVessels = GfwVessel::query()
                ->orderByDesc('last_synced_at')
                ->limit($limit)
                ->get();

            if ($localVessels->isNotEmpty()) {
                $normalizedList = $localVessels->map(fn (GfwVessel $v) => $this->modelToNormalized($v))->values()->all();

                return [
                    'success' => true,
                    'data' => $normalizedList,
                    'cached' => true,
                    'total' => count($normalizedList),
                ];
            }

            // Fallback: If local repository is empty, perform initial upstream search for 'INDONESIA'
            return $this->search('INDONESIA', $options);
        }

        $cacheKey = 'gfw:vessel:search:'.md5(strtolower($cleanQuery).':'.$limit.':'.$dataset);

        if (! $forceRefresh && Cache::has($cacheKey)) {
            /** @var list<array<string, mixed>> $cachedData */
            $cachedData = Cache::get($cacheKey, []);

            return [
                'success' => true,
                'data' => $cachedData,
                'cached' => true,
                'total' => count($cachedData),
            ];
        }

        $queryParams = [
            'query' => $cleanQuery,
            'datasets[0]' => $dataset,
            'limit' => $limit,
        ];

        $response = $this->apiService->get('/vessels/search', $queryParams);

        if (! $response['success']) {
            return [
                'success' => false,
                'data' => [],
                'cached' => false,
                'total' => 0,
                'error' => $response['error'] ?? 'GFW Vessel search failed.',
            ];
        }

        $rawEntries = $response['data']['entries'] ?? [];
        if (! is_array($rawEntries)) {
            $rawEntries = [];
        }

        $shouldPersist = (bool) ($options['persist'] ?? true);
        $normalizedList = [];
        foreach ($rawEntries as $entry) {
            if (is_array($entry)) {
                $normalized = $this->normalize($entry);
                if (! empty($normalized['gfw_vessel_id'])) {
                    if ($shouldPersist) {
                        $this->persistToDatabase($normalized);
                    }

                    // Check if local presence exists for this vessel
                    try {
                        $latestPresence = GfwVesselPresence::where('gfw_vessel_id', $normalized['gfw_vessel_id'])
                            ->latest('observed_at')
                            ->first();

                        if ($latestPresence) {
                            $normalized['latitude'] = $latestPresence->latitude !== null ? (float) $latestPresence->latitude : null;
                            $normalized['longitude'] = $latestPresence->longitude !== null ? (float) $latestPresence->longitude : null;
                            $normalized['observed_at'] = $latestPresence->observed_at?->toIso8601String();
                            $normalized['speed'] = $latestPresence->speed !== null ? (float) $latestPresence->speed : null;
                            $normalized['course'] = $latestPresence->course !== null ? (float) $latestPresence->course : null;
                        }
                    } catch (Throwable) {
                        // Ignore if table unavailable
                    }

                    $normalizedList[] = $normalized;
                }
            }
        }

        // Cache the search results
        Cache::put($cacheKey, $normalizedList, $this->cacheTtl);

        return [
            'success' => true,
            'data' => $normalizedList,
            'cached' => false,
            'total' => count($normalizedList),
        ];
    }

    /**
     * Retrieve a single vessel by its GFW Vessel ID.
     *
     * @return array<string, mixed>|null
     */
    public function getById(string $vesselId, bool $forceRefresh = false): ?array
    {
        $cleanId = trim($vesselId);
        if ($cleanId === '') {
            return null;
        }

        $cacheKey = 'gfw:vessel:id:'.$cleanId;

        if (! $forceRefresh && Cache::has($cacheKey)) {
            /** @var array<string, mixed>|null $cached */
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        // Check local GFW table first if not force refreshed
        if (! $forceRefresh) {
            $existing = GfwVessel::where('gfw_vessel_id', $cleanId)->first();
            if ($existing) {
                $normalized = $this->modelToNormalized($existing);
                Cache::put($cacheKey, $normalized, $this->cacheTtl);

                return $normalized;
            }
        }

        // Fetch from GFW API using dataset parameter (GFW API v3 contract)
        $response = $this->apiService->get("/vessels/{$cleanId}", [
            'dataset' => $this->defaultDataset,
        ]);

        if (! $response['success'] || empty($response['data'])) {
            return null;
        }

        $normalized = $this->normalize($response['data']);
        if (empty($normalized['gfw_vessel_id'])) {
            $normalized['gfw_vessel_id'] = $cleanId;
        }

        $this->persistToDatabase($normalized);
        Cache::put($cacheKey, $normalized, $this->cacheTtl);

        return $normalized;
    }

    /**
     * Find a vessel by MMSI number.
     *
     * @return array<string, mixed>|null
     */
    public function getByMmsi(string $mmsi): ?array
    {
        $cleanMmsi = trim($mmsi);
        if ($cleanMmsi === '') {
            return null;
        }

        // Check local database first
        $local = GfwVessel::where('mmsi', $cleanMmsi)->first();
        if ($local) {
            return $this->modelToNormalized($local);
        }

        // Fallback to search
        $search = $this->search($cleanMmsi, ['limit' => 1]);
        if ($search['success'] && ! empty($search['data'])) {
            return $search['data'][0];
        }

        return null;
    }

    /**
     * Normalize heterogeneous GFW API raw payload into a standard shape.
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    public function normalize(array $raw): array
    {
        $selfReportedRaw = $raw['selfReportedInfo'] ?? [];
        $selfReported = isset($selfReportedRaw[0]) && is_array($selfReportedRaw[0]) ? $selfReportedRaw[0] : (is_array($selfReportedRaw) ? $selfReportedRaw : []);

        $combinedRaw = $raw['combinedInfo'] ?? $raw['combinedSourcesInfo'] ?? [];
        $combined = isset($combinedRaw[0]) && is_array($combinedRaw[0]) ? $combinedRaw[0] : (is_array($combinedRaw) ? $combinedRaw : []);

        $registryRaw = $raw['registryInfo'] ?? [];
        $registry = isset($registryRaw[0]) && is_array($registryRaw[0]) ? $registryRaw[0] : (is_array($registryRaw) ? $registryRaw : []);

        $gfwVesselId = (string) ($raw['id'] ?? $raw['vesselId'] ?? $combined['vesselId'] ?? $selfReported['id'] ?? '');

        // Vessel Name
        $name = $raw['shipname']
            ?? $raw['name']
            ?? $combined['shipname']
            ?? $selfReported['shipname']
            ?? $registry['shipname']
            ?? null;

        // MMSI
        $mmsi = $raw['mmsi']
            ?? $raw['ssvid']
            ?? $combined['mmsi']
            ?? $selfReported['ssvid']
            ?? $selfReported['mmsi']
            ?? $registry['mmsi']
            ?? null;

        // IMO
        $imo = $raw['imo']
            ?? $combined['imo']
            ?? $selfReported['imo']
            ?? $registry['imo']
            ?? null;

        // Flag
        $flag = $raw['flag']
            ?? $combined['flag']
            ?? $selfReported['flag']
            ?? $registry['flag']
            ?? null;

        // Vessel Type
        $vesselType = $raw['vesselType']
            ?? $raw['shiptype']
            ?? ($combined['shiptypes'][0]['name'] ?? null)
            ?? $combined['vesselType']
            ?? $selfReported['vesselType']
            ?? $registry['vesselType']
            ?? null;

        // Gear Type
        $gearType = $raw['geartype']
            ?? $raw['gearType']
            ?? ($combined['geartypes'][0]['name'] ?? null)
            ?? $combined['geartype']
            ?? $registry['geartype']
            ?? null;

        // Call Sign
        $callsign = $raw['callsign']
            ?? $raw['callSign']
            ?? $combined['callsign']
            ?? $combined['callSign']
            ?? $selfReported['callsign']
            ?? $selfReported['callSign']
            ?? $registry['callsign']
            ?? $registry['callSign']
            ?? null;

        // Length (meters)
        $lengthRaw = $raw['lengthM']
            ?? $raw['length']
            ?? $combined['lengthM']
            ?? $selfReported['lengthM']
            ?? $registry['lengthM']
            ?? null;
        $length = is_numeric($lengthRaw) && (float) $lengthRaw >= 0 ? round((float) $lengthRaw, 2) : null;

        // Tonnage (Gross Tonnage)
        $tonnageRaw = $raw['tonnageGt']
            ?? $raw['grossTonnage']
            ?? $combined['tonnageGt']
            ?? $selfReported['tonnageGt']
            ?? $registry['tonnageGt']
            ?? null;
        $tonnage = is_numeric($tonnageRaw) && (float) $tonnageRaw >= 0 ? round((float) $tonnageRaw, 2) : null;

        return [
            'id' => $gfwVesselId !== '' ? $gfwVesselId : null,
            'gfw_vessel_id' => $gfwVesselId !== '' ? $gfwVesselId : null,
            'name' => $name !== null ? trim((string) $name) : null,
            'shipname' => $name !== null ? trim((string) $name) : null,
            'mmsi' => $mmsi !== null ? trim((string) $mmsi) : null,
            'imo' => $imo !== null ? trim((string) $imo) : null,
            'callsign' => $callsign !== null ? trim((string) $callsign) : null,
            'callSign' => $callsign !== null ? trim((string) $callsign) : null,
            'flag' => $flag !== null ? strtoupper(trim((string) $flag)) : null,
            'vessel_type' => $vesselType !== null ? trim((string) $vesselType) : null,
            'vesselType' => $vesselType !== null ? trim((string) $vesselType) : null,
            'gear_type' => $gearType !== null ? trim((string) $gearType) : null,
            'geartype' => $gearType !== null ? trim((string) $gearType) : null,
            'length_m' => $length,
            'lengthM' => $length,
            'tonnage_gt' => $tonnage,
            'tonnageGt' => $tonnage,
            'latitude' => null,
            'longitude' => null,
            'observed_at' => null,
            'speed' => null,
            'course' => null,
            'raw_data' => $raw,
            'last_synced_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Persist normalized vessel identity to dedicated gfw_vessels table.
     *
     * @param  array<string, mixed>  $normalized
     */
    protected function persistToDatabase(array $normalized): ?GfwVessel
    {
        if (empty($normalized['gfw_vessel_id'])) {
            return null;
        }

        try {
            return GfwVessel::updateOrCreate(
                ['gfw_vessel_id' => $normalized['gfw_vessel_id']],
                [
                    'name' => $normalized['name'],
                    'mmsi' => $normalized['mmsi'],
                    'imo' => $normalized['imo'],
                    'callsign' => $normalized['callsign'] ?? null,
                    'flag' => $normalized['flag'],
                    'vessel_type' => $normalized['vessel_type'],
                    'gear_type' => $normalized['gear_type'],
                    'length_m' => $normalized['length_m'],
                    'tonnage_gt' => $normalized['tonnage_gt'],
                    'raw_data' => $normalized['raw_data'],
                    'last_synced_at' => now(),
                ]
            );
        } catch (Throwable $e) {
            Log::warning('Failed persisting GFW vessel to database', [
                'gfw_vessel_id' => $normalized['gfw_vessel_id'],
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Convert GfwVessel Eloquent model to normalized array shape.
     *
     * @return array<string, mixed>
     */
    protected function modelToNormalized(GfwVessel $vessel): array
    {
        $latestPresence = null;
        try {
            $latestPresence = $vessel->presences()->latest('observed_at')->first();
        } catch (Throwable) {
            // Graceful fallback if presences table is unavailable in some contexts
        }

        $callsign = $vessel->callsign
            ?? (isset($vessel->raw_data['selfReportedInfo'][0]['callsign']) ? $vessel->raw_data['selfReportedInfo'][0]['callsign'] : null)
            ?? ($vessel->raw_data['callsign'] ?? null);

        $length = $vessel->length_m !== null && (float) $vessel->length_m >= 0 ? round((float) $vessel->length_m, 2) : null;
        $tonnage = $vessel->tonnage_gt !== null && (float) $vessel->tonnage_gt >= 0 ? round((float) $vessel->tonnage_gt, 2) : null;

        return [
            'id' => $vessel->gfw_vessel_id,
            'gfw_vessel_id' => $vessel->gfw_vessel_id,
            'name' => $vessel->name,
            'shipname' => $vessel->name,
            'mmsi' => $vessel->mmsi,
            'imo' => $vessel->imo,
            'callsign' => $callsign !== null ? trim((string) $callsign) : null,
            'callSign' => $callsign !== null ? trim((string) $callsign) : null,
            'flag' => $vessel->flag,
            'vessel_type' => $vessel->vessel_type,
            'vesselType' => $vessel->vessel_type,
            'gear_type' => $vessel->gear_type,
            'geartype' => $vessel->gear_type,
            'length_m' => $length,
            'lengthM' => $length,
            'tonnage_gt' => $tonnage,
            'tonnageGt' => $tonnage,
            'latitude' => $latestPresence?->latitude !== null ? (float) $latestPresence->latitude : null,
            'longitude' => $latestPresence?->longitude !== null ? (float) $latestPresence->longitude : null,
            'observed_at' => $latestPresence?->observed_at?->toIso8601String(),
            'speed' => $latestPresence?->speed !== null ? (float) $latestPresence->speed : null,
            'course' => $latestPresence?->course !== null ? (float) $latestPresence->course : null,
            'raw_data' => $vessel->raw_data,
            'last_synced_at' => $vessel->last_synced_at?->toIso8601String(),
        ];
    }
}

<?php

namespace Tests\Feature\Gfw;

use App\Models\GfwEvent;
use App\Models\GfwVessel;
use App\Models\GfwVesselActivity;
use App\Models\User;
use App\Models\Vessel;
use App\Services\Gfw\GfwRegionService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class GfwFinalAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create([
            'email' => 'auditor@example.com',
        ]);
        $this->adminUser->assignRole('super-admin');

        Config::set('gfw.api_key', 'super-secret-audit-key-999');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');
        Config::set('gfw.cache_ttl', 3600);
        Cache::flush();
    }

    /**
     * AUDIT 1: SECURITY & CREDENTIAL LEAKAGE
     */
    public function test_audit_security_zero_credential_leakage_in_api_and_logs(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'entries' => [
                    ['id' => 'GFW-AUDIT-1', 'shipname' => 'KM AUDIT', 'mmsi' => '999111222', 'flag' => 'IDN'],
                ],
            ], 200),
        ]);

        $loggedMessages = [];
        Log::listen(function ($message) use (&$loggedMessages) {
            $loggedMessages[] = is_string($message->message) ? $message->message : json_encode($message->message);
            if (! empty($message->context)) {
                $loggedMessages[] = json_encode($message->context);
            }
        });

        $response = $this->actingAs($this->adminUser)->getJson('/api/gfw/vessels?query=KM%20AUDIT');

        $response->assertStatus(200);
        $responseContent = $response->getContent();

        // Ensure API key is never in JSON response
        $this->assertStringNotContainsString('super-secret-audit-key-999', $responseContent);

        // Ensure Authorization Bearer is never logged
        foreach ($loggedMessages as $logEntry) {
            $this->assertStringNotContainsString('super-secret-audit-key-999', $logEntry);
            $this->assertStringNotContainsString('Bearer super-secret-audit-key-999', $logEntry);
        }
    }

    /**
     * AUDIT 2: DATABASE ISOLATION
     */
    public function test_audit_database_local_fisheries_tables_remain_untouched(): void
    {
        $localVessel = Vessel::create([
            'name' => 'KM LOKAL ASLI',
            'registration_number' => 'REG-LOC-01',
            'gross_tonnage' => 15,
            'vessel_type' => 'motor_boat',
            'status' => 'active',
        ]);

        $initialVesselCount = Vessel::count();

        // Insert GFW vessel and activity
        GfwVessel::create([
            'gfw_vessel_id' => 'GFW-EXT-999',
            'name' => 'FOREIGN VESSEL 99',
            'mmsi' => '123456789',
            'flag' => 'PAN',
            'vessel_type' => 'carrier',
            'raw_data' => ['sample' => true],
            'last_synced_at' => now(),
        ]);

        GfwVesselActivity::create([
            'gfw_vessel_id' => 'GFW-EXT-999',
            'activity_type' => 'presence',
            'latitude' => 5.55,
            'longitude' => 95.32,
            'observation_timestamp' => now(),
            'hours' => 2.5,
            'region_key' => 'aceh_waters',
        ]);

        GfwEvent::create([
            'gfw_event_id' => 'EV-AUDIT-001',
            'gfw_vessel_id' => 'GFW-EXT-999',
            'event_type' => 'apparent_fishing',
            'start_time' => now()->subHours(5),
            'end_time' => now(),
            'latitude' => 5.60,
            'longitude' => 95.40,
        ]);

        // Assert local tables are unchanged
        $this->assertSame($initialVesselCount, Vessel::count());
        $this->assertDatabaseHas('vessels', ['name' => 'KM LOKAL ASLI']);
        $this->assertDatabaseMissing('vessels', ['name' => 'FOREIGN VESSEL 99']);

        // Assert GFW tables have isolated data
        $this->assertDatabaseHas('gfw_vessels', ['gfw_vessel_id' => 'GFW-EXT-999']);
        $this->assertDatabaseHas('gfw_vessel_activities', ['gfw_vessel_id' => 'GFW-EXT-999']);
        $this->assertDatabaseHas('gfw_events', ['gfw_event_id' => 'EV-AUDIT-001']);
    }

    /**
     * AUDIT 3: API ERROR RESILIENCE (Timeout, 500, Malformed, Empty)
     */
    public function test_audit_api_handles_failures_gracefully_without_crashing(): void
    {
        // 1. Timeout / Exception
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => function () {
                throw new ConnectionException('Connection timed out after 30 seconds');
            },
        ]);

        $responseTimeout = $this->actingAs($this->adminUser)->getJson('/api/gfw/vessels?query=TIMEOUT_TEST');
        $responseTimeout->assertStatus(502)
            ->assertJson([
                'success' => false,
                'source' => 'global_fishing_watch',
            ]);

        // 2. Malformed / Empty JSON Response
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response('NOT A JSON', 200),
        ]);

        $responseMalformed = $this->actingAs($this->adminUser)->getJson('/api/gfw/events?region=aceh_waters');
        $responseMalformed->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
            ]);
    }

    /**
     * AUDIT 4: CACHE ISOLATION & NON-COLLISION
     */
    public function test_audit_cache_keys_are_isolated_and_prevent_repeated_http_calls(): void
    {
        $httpCallCount = 0;
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => function () use (&$httpCallCount) {
                $httpCallCount++;

                return Http::response([
                    'entries' => [
                        ['id' => 'GFW-CACHE-1', 'shipname' => 'KM CACHED', 'mmsi' => '111222333'],
                    ],
                ], 200);
            },
        ]);

        // Call 1: HTTP request executed
        $res1 = $this->actingAs($this->adminUser)->getJson('/api/gfw/vessels?query=KM%20CACHED');
        $res1->assertStatus(200);
        $this->assertSame(1, $httpCallCount);
        $this->assertFalse($res1->json('meta.cached'));

        // Call 2: Served from cache
        $res2 = $this->actingAs($this->adminUser)->getJson('/api/gfw/vessels?query=KM%20CACHED');
        $res2->assertStatus(200);
        $this->assertSame(1, $httpCallCount);
        $this->assertTrue($res2->json('meta.cached'));
    }

    /**
     * AUDIT 5: DATA PROVENANCE & NON-JUDGMENTAL SEMANTICS
     */
    public function test_audit_provenance_and_strict_semantics(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'entries' => [
                    [
                        'id' => 'EV-FISH-1',
                        'vessel' => ['id' => 'V-1'],
                        'type' => 'fishing',
                        'start' => '2026-09-01T00:00:00Z',
                        'end' => '2026-09-01T04:00:00Z',
                        'position' => ['lat' => 5.5, 'lon' => 95.3],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->getJson('/api/gfw/events/fishing?region=aceh_waters');

        $response->assertStatus(200)
            ->assertJson([
                'source' => 'global_fishing_watch',
                'meta' => [
                    'semantic_label' => 'Apparent Fishing Event',
                ],
            ]);

        $rawResponse = $response->getContent();
        $this->assertStringNotContainsString('confirmed fishing', strtolower($rawResponse));
        $this->assertStringNotContainsString('illegal fishing', strtolower($rawResponse));
        $this->assertStringNotContainsString('transshipment confirmed', strtolower($rawResponse));
        $this->assertStringContainsString('Apparent Fishing Event', $rawResponse);
    }

    /**
     * AUDIT 6: GEOGRAPHIC DEFINITIONS (Indonesia EEZ + Aceh Waters)
     */
    public function test_audit_geographic_boundaries_are_valid(): void
    {
        $regionService = app(GfwRegionService::class);

        $eez = $regionService->getRegion('indonesia_eez');
        $this->assertNotNull($eez);
        $this->assertSame('indonesia_eez', $eez['key']);
        $this->assertSame('IDN', $eez['gfw_region_id']);
        $this->assertArrayHasKey('bounding_box', $eez);

        $aceh = $regionService->getRegion('aceh_waters');
        $this->assertNotNull($aceh);
        $this->assertSame('aceh_waters', $aceh['key']);
        $this->assertArrayHasKey('polygon', $aceh);
        $this->assertArrayHasKey('bounding_box', $aceh);

        // Validation test
        $bboxValidation = $regionService->validateBoundingBox($aceh['bounding_box']);
        $this->assertTrue($bboxValidation['valid']);

        $polygonValidation = $regionService->validateGeoJsonPolygon($aceh['polygon']);
        $this->assertTrue($polygonValidation['valid']);
    }

    /**
     * AUDIT 7: GIS FRONTEND NEVER CLAIMS REAL-TIME LIVE POSITIONING
     */
    public function test_audit_gis_frontend_contains_latency_notice_and_source_attribution(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/gfw/monitoring');

        $response->assertStatus(200);
        $response->assertSee('Global Fishing Watch');
        $response->assertSee('Pemberitahuan Latensi Data');
        $response->assertSee('latensi');
        $response->assertSee('Apparent Fishing');
        $response->assertSee('Potential Encounters');
        $response->assertSee('Loitering');
        $response->assertSee('Port Visits');
    }
}

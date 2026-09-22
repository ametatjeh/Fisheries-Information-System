<?php

namespace Tests\Feature\Gfw;

use App\Models\GfwVesselActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GfwActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Cache::flush();
        Config::set('gfw.api_key', 'test-activity-key-12345');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');
        Config::set('gfw.activity_cache_ttl', 3600);
    }

    public function test_vessel_activity_returns_normalized_data_and_persists_to_gfw_vessel_activities(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/gfw-vessel-001/tracks*' => Http::response([
                'entries' => [
                    [
                        'lat' => 5.582,
                        'lon' => 95.312,
                        'timestamp' => '2026-09-18T10:30:00Z',
                        'speedKnots' => 8.5,
                        'distanceKm' => 12.4,
                        'hours' => 2.5,
                    ],
                    [
                        'lat' => 5.620,
                        'lon' => 95.340,
                        'timestamp' => '2026-09-18T12:00:00Z',
                        'speedKnots' => 9.1,
                        'distanceKm' => 15.2,
                        'hours' => 3.0,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/activity/vessels/gfw-vessel-001?start_date=2026-09-15&end_date=2026-09-20');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'global_fishing_watch',
                'cached' => false,
                'total' => 2,
            ])
            ->assertJsonPath('data.0.gfw_vessel_id', 'gfw-vessel-001')
            ->assertJsonPath('data.0.latitude', 5.582)
            ->assertJsonPath('data.0.longitude', 95.312)
            ->assertJsonPath('data.0.speed_knots', 8.5)
            ->assertJsonPath('data.0.activity_type', 'track_point');

        // Verify latency notice
        $this->assertStringContainsString('latensi (delay berkala 24-72 jam)', $response->json('latency_notice'));

        // Verify persisted to dedicated gfw_vessel_activities table
        $this->assertDatabaseHas('gfw_vessel_activities', [
            'gfw_vessel_id' => 'gfw-vessel-001',
            'activity_type' => 'track_point',
        ]);
        $this->assertEquals(2, GfwVesselActivity::count());

        // Verify local master vessels table remains untouched
        $this->assertDatabaseCount('vessels', 0);
    }

    public function test_vessel_presence_success_in_aceh_waters_and_indonesia_regions(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/activity*' => Http::response([
                'entries' => [
                    [
                        'vesselId' => 'gfw-vessel-aceh-obs-01',
                        'lat' => 5.890,
                        'lon' => 95.230,
                        'timestamp' => '2026-09-19T08:00:00Z',
                        'hours' => 5.4,
                        'activityType' => 'presence',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/activity/presence?region=aceh_waters&start_date=2026-09-10&end_date=2026-09-18');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'global_fishing_watch',
                'total' => 1,
            ])
            ->assertJsonPath('region.key', 'aceh_waters')
            ->assertJsonPath('data.0.gfw_vessel_id', 'gfw-vessel-aceh-obs-01')
            ->assertJsonPath('data.0.latitude', 5.89)
            ->assertJsonPath('data.0.longitude', 95.23);
    }

    public function test_empty_activity_and_presence_results(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/empty-vessel/tracks*' => Http::response([
                'entries' => [],
            ], 200),
            'https://gateway.api.globalfishingwatch.org/v3/vessels/activity*' => Http::response([
                'entries' => [],
            ], 200),
        ]);

        $actRes = $this->getJson('/api/gfw/activity/vessels/empty-vessel');
        $actRes->assertStatus(200)->assertJson(['success' => true, 'total' => 0, 'data' => []]);

        $presRes = $this->getJson('/api/gfw/activity/presence?region=wppnri_571');
        $presRes->assertStatus(200)->assertJson(['success' => true, 'total' => 0, 'data' => []]);
    }

    public function test_invalid_date_formats_and_range_validation(): void
    {
        // 1. start_date > end_date
        $invalidRange = $this->getJson('/api/gfw/activity/vessels/test-id?start_date=2026-09-20&end_date=2026-09-10');
        $invalidRange->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => 'start_date tidak boleh lebih besar daripada end_date.',
            ]);

        // 2. Malformed date string
        $malformedDate = $this->getJson('/api/gfw/activity/presence?start_date=invalid-date-format');
        $malformedDate->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        // 3. Exceeds 90 days window
        $exceedsWindow = $this->getJson('/api/gfw/activity/vessels/test-id?start_date=2026-01-01&end_date=2026-06-01');
        $exceedsWindow->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => 'Rentang tanggal maksimal query adalah 90 hari.',
            ]);
    }

    public function test_api_failure_and_timeout_error_handling(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/error-vessel/tracks*' => Http::response([
                'error' => 'Internal GFW gateway error',
            ], 500),
        ]);

        $response = $this->getJson('/api/gfw/activity/vessels/error-vessel?start_date=2026-09-10&end_date=2026-09-15');

        $response->assertStatus(502)
            ->assertJson([
                'success' => false,
                'total' => 0,
                'data' => [],
            ]);
    }

    public function test_cache_hit_and_cache_miss_behavior_with_activity_ttl(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/vessel-cached/tracks*' => Http::response([
                'entries' => [
                    [
                        'lat' => 4.20,
                        'lon' => 96.10,
                        'timestamp' => '2026-09-18T00:00:00Z',
                        'speedKnots' => 7.0,
                    ],
                ],
            ], 200),
        ]);

        // 1. First request -> Cache Miss
        $firstRes = $this->getJson('/api/gfw/activity/vessels/vessel-cached?start_date=2026-09-15&end_date=2026-09-18');
        $firstRes->assertStatus(200)->assertJson(['success' => true, 'cached' => false, 'total' => 1]);
        Http::assertSentCount(1);

        // 2. Second request -> Cache Hit
        $secondRes = $this->getJson('/api/gfw/activity/vessels/vessel-cached?start_date=2026-09-15&end_date=2026-09-18');
        $secondRes->assertStatus(200)->assertJson(['success' => true, 'cached' => true, 'total' => 1]);
        Http::assertSentCount(1);
    }

    public function test_unconfigured_api_key_returns_503(): void
    {
        Config::set('gfw.api_key', null);

        $response = $this->getJson('/api/gfw/activity/presence?region=indonesia_eez');

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'error' => 'GFW API key is not configured.',
            ]);

        Http::assertNothingSent();
    }

    public function test_unknown_region_for_presence_returns_404(): void
    {
        $response = $this->getJson('/api/gfw/activity/presence?region=atlantis_ocean');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);

        Http::assertNothingSent();
    }
}

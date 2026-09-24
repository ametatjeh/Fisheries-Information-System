<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use App\Services\GFWService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GfwUpstreamPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected GFWService $gfwService;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();

        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-secret-pagination-token-123');
        Config::set('gfw.fishing_events_dataset', 'public-global-fishing-events:latest');

        $permission = Permission::firstOrCreate(['name' => 'access.gis', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo($permission);

        $this->gfwService = app(GFWService::class);
    }

    /**
     * Helper to create a fake event entry inside BIG ZEE Aceh (near Sabang/Banda Aceh: 95.3, 5.5).
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function makeEvent(string $id, string $vesselId, float $lat = 5.55, float $lon = 95.32, array $overrides = []): array
    {
        return array_merge([
            'id' => $id,
            'type' => 'fishing',
            'start' => '2026-09-20T02:00:00Z',
            'end' => '2026-09-20T03:00:00Z',
            'position' => ['lat' => $lat, 'lon' => $lon],
            'vessel' => [
                'id' => $vesselId,
                'name' => "VESSEL {$vesselId}",
                'ssvid' => "52500{$vesselId}",
                'flag' => 'IDN',
                'type' => 'fishing',
            ],
        ], $overrides);
    }

    /**
     * TEST A — SINGLE PAGE:
     * 100 events, nextOffset = null -> 1 request, 100 events, pagination_complete = true.
     */
    public function test_a_single_page_upstream_pagination_completes_in_one_request(): void
    {
        $events = [];
        for ($i = 1; $i <= 100; $i++) {
            $events[] = $this->makeEvent("evt-{$i}", "vessel-{$i}");
        }

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 100,
                'entries' => $events,
                'nextOffset' => null,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?limit=100');

        $response->assertStatus(200);
        $response->assertJsonPath('pagination.pagination_complete', true);
        $response->assertJsonPath('pagination.pagination_truncated', false);
        $response->assertJsonPath('pagination.upstream_events_count', 100);

        Http::assertSentCount(1);
    }

    /**
     * TEST B — TWO PAGES:
     * page 1 = 100, nextOffset = 100; page 2 = 50, nextOffset = null -> 2 requests, 150 events processed.
     */
    public function test_b_two_pages_upstream_pagination_fetches_both_pages(): void
    {
        $page1Events = [];
        for ($i = 1; $i <= 100; $i++) {
            $page1Events[] = $this->makeEvent("evt-{$i}", "vessel-{$i}");
        }

        $page2Events = [];
        for ($i = 101; $i <= 150; $i++) {
            $page2Events[] = $this->makeEvent("evt-{$i}", "vessel-{$i}");
        }

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events?limit=100&offset=0' => Http::response([
                'total' => 150,
                'entries' => $page1Events,
                'nextOffset' => 100,
            ], 200),
            'https://gateway.api.globalfishingwatch.org/v3/events?limit=100&offset=100' => Http::response([
                'total' => 150,
                'entries' => $page2Events,
                'nextOffset' => null,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?limit=100');

        $response->assertStatus(200);
        $response->assertJsonPath('pagination.pagination_complete', true);
        $response->assertJsonPath('pagination.pagination_truncated', false);
        $response->assertJsonPath('pagination.upstream_events_count', 150);
        $response->assertJsonPath('summary.total_vessels', 150);

        Http::assertSentCount(2);
    }

    /**
     * TEST C — MULTIPLE PAGES:
     * 100 + 100 + 25 = 225 events processed.
     */
    public function test_c_multiple_pages_upstream_pagination_accumulates_events(): void
    {
        $page1 = [];
        for ($i = 1; $i <= 100; $i++) {
            $page1[] = $this->makeEvent("evt-{$i}", "vessel-{$i}");
        }

        $page2 = [];
        for ($i = 101; $i <= 200; $i++) {
            $page2[] = $this->makeEvent("evt-{$i}", "vessel-{$i}");
        }

        $page3 = [];
        for ($i = 201; $i <= 225; $i++) {
            $page3[] = $this->makeEvent("evt-{$i}", "vessel-{$i}");
        }

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events?limit=100&offset=0' => Http::response([
                'total' => 225,
                'entries' => $page1,
                'nextOffset' => 100,
            ], 200),
            'https://gateway.api.globalfishingwatch.org/v3/events?limit=100&offset=100' => Http::response([
                'total' => 225,
                'entries' => $page2,
                'nextOffset' => 200,
            ], 200),
            'https://gateway.api.globalfishingwatch.org/v3/events?limit=100&offset=200' => Http::response([
                'total' => 225,
                'entries' => $page3,
                'nextOffset' => null,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?limit=100');

        $response->assertStatus(200);
        $response->assertJsonPath('pagination.upstream_events_count', 225);
        $response->assertJsonPath('summary.total_vessels', 225);

        Http::assertSentCount(3);
    }

    /**
     * TEST D — DUPLICATE VESSEL:
     * Page 1: Vessel A, Vessel B. Page 2: Vessel A, Vessel C.
     * Expected: exactly 3 distinct vessels (A, B, C) with latest timestamps updated.
     */
    public function test_d_duplicate_vessel_across_pages_is_deduplicated(): void
    {
        $page1 = [
            $this->makeEvent('evt-1', 'vessel-A', 5.51, 95.31, ['start' => '2026-09-20T02:00:00Z', 'end' => '2026-09-20T03:00:00Z']),
            $this->makeEvent('evt-2', 'vessel-B', 5.52, 95.32, ['start' => '2026-09-20T02:30:00Z', 'end' => '2026-09-20T03:30:00Z']),
        ];

        $page2 = [
            $this->makeEvent('evt-3', 'vessel-A', 5.60, 95.40, ['start' => '2026-09-20T06:00:00Z', 'end' => '2026-09-20T07:00:00Z']),
            $this->makeEvent('evt-4', 'vessel-C', 5.53, 95.33, ['start' => '2026-09-20T04:00:00Z', 'end' => '2026-09-20T05:00:00Z']),
        ];

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events?limit=100&offset=0' => Http::response([
                'total' => 4,
                'entries' => $page1,
                'nextOffset' => 2,
            ], 200),
            'https://gateway.api.globalfishingwatch.org/v3/events?limit=100&offset=2' => Http::response([
                'total' => 4,
                'entries' => $page2,
                'nextOffset' => null,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?limit=100');

        $response->assertStatus(200);
        $response->assertJsonPath('summary.total_vessels', 3);

        $vessels = $response->json('vessels');
        $this->assertCount(3, $vessels);

        $vesselIds = array_column($vessels, 'id');
        $this->assertContains('vessel-A', $vesselIds);
        $this->assertContains('vessel-B', $vesselIds);
        $this->assertContains('vessel-C', $vesselIds);

        // Vessel A should have latest seen timestamp and position from page 2
        $vesselA = collect($vessels)->firstWhere('id', 'vessel-A');
        $this->assertSame('2026-09-20T07:00:00Z', $vesselA['last_seen']);
        $this->assertEquals(5.60, $vesselA['lat']);
        $this->assertEquals(95.40, $vesselA['lon']);
    }

    /**
     * TEST E — BIG PIP ON ALL PAGES:
     * Inside BIG accepted, outside BIG rejected on every page.
     */
    public function test_e_big_pip_applies_to_all_pages(): void
    {
        // Page 1: evt-1 inside (95.80, 5.25), evt-2 outside (102.00, 5.25)
        $page1 = [
            $this->makeEvent('evt-1', 'vessel-in-1', 5.25, 95.80),
            $this->makeEvent('evt-2', 'vessel-out-1', 5.25, 102.00),
        ];

        // Page 2: evt-3 inside (97.20, 4.80), evt-4 outside (90.00, 4.00)
        $page2 = [
            $this->makeEvent('evt-3', 'vessel-in-2', 4.80, 97.20),
            $this->makeEvent('evt-4', 'vessel-out-2', 4.00, 90.00),
        ];

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events?limit=100&offset=0' => Http::response([
                'total' => 4,
                'entries' => $page1,
                'nextOffset' => 2,
            ], 200),
            'https://gateway.api.globalfishingwatch.org/v3/events?limit=100&offset=2' => Http::response([
                'total' => 4,
                'entries' => $page2,
                'nextOffset' => null,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?limit=100');

        $response->assertStatus(200);
        $response->assertJsonPath('summary.total_vessels', 2);

        $vessels = $response->json('vessels');
        $vesselIds = array_column($vessels, 'id');
        $this->assertContains('vessel-in-1', $vesselIds);
        $this->assertContains('vessel-in-2', $vesselIds);
        $this->assertNotContains('vessel-out-1', $vesselIds);
        $this->assertNotContains('vessel-out-2', $vesselIds);
    }

    /**
     * TEST F — SAFETY LIMIT:
     * When upstream returns endless pages, safety limit terminates loop and marks pagination_truncated.
     */
    public function test_f_safety_limit_prevents_infinite_loop_and_marks_truncated(): void
    {
        // Fake 10 pages possible, each returning 100 events
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => function (Request $request) {
                parse_str(parse_url($request->url(), PHP_URL_QUERY) ?? '', $queryParams);
                $offset = (int) ($queryParams['offset'] ?? 0);

                $events = [];
                for ($i = 1; $i <= 100; $i++) {
                    $id = $offset + $i;
                    $events[] = [
                        'id' => "evt-{$id}",
                        'type' => 'fishing',
                        'position' => ['lat' => 5.55, 'lon' => 95.32],
                        'vessel' => [
                            'id' => "vessel-{$id}",
                            'name' => "VESSEL {$id}",
                            'flag' => 'IDN',
                        ],
                    ];
                }

                return Http::response([
                    'total' => 1000,
                    'entries' => $events,
                    'nextOffset' => $offset + 100,
                ], 200);
            },
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?limit=100');

        $response->assertStatus(200);
        $response->assertJsonPath('pagination.pagination_truncated', true);
        $response->assertJsonPath('pagination.pagination_complete', false);
        $response->assertJsonPath('pagination.upstream_events_count', GFWService::MAX_UPSTREAM_EVENTS);

        // Maximum requests should be MAX_UPSTREAM_PAGES (5)
        Http::assertSentCount(GFWService::MAX_UPSTREAM_PAGES);
    }

    /**
     * TEST G — INVALID NEXT OFFSET:
     * When nextOffset does not advance (e.g. offset = 100, nextOffset = 100), pagination stops.
     */
    public function test_g_invalid_or_non_advancing_next_offset_stops_pagination(): void
    {
        $page1 = [];
        for ($i = 1; $i <= 50; $i++) {
            $page1[] = $this->makeEvent("evt-{$i}", "vessel-{$i}");
        }

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events?limit=100&offset=0' => Http::response([
                'total' => 100,
                'entries' => $page1,
                'nextOffset' => 0, // Non-advancing (<= currentOffset 0)
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?limit=100');

        $response->assertStatus(200);
        $response->assertJsonPath('summary.total_vessels', 50);

        // Only 1 request sent because nextOffset was non-advancing
        Http::assertSentCount(1);
    }

    /**
     * TEST H — API ERROR ON PAGE 2:
     * Page 1 succeeds, Page 2 encounters 500 error.
     * Expected: graceful preservation of page 1 events without crashing.
     */
    public function test_h_api_error_on_page_2_gracefully_preserves_page_1_data(): void
    {
        $page1 = [];
        for ($i = 1; $i <= 50; $i++) {
            $page1[] = $this->makeEvent("evt-{$i}", "vessel-{$i}");
        }

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events?limit=100&offset=0' => Http::response([
                'total' => 100,
                'entries' => $page1,
                'nextOffset' => 50,
            ], 200),
            'https://gateway.api.globalfishingwatch.org/v3/events?limit=100&offset=50' => Http::response([
                'error' => 'Internal server error on upstream page 2',
            ], 500),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?limit=100');

        $response->assertStatus(200);
        $response->assertJsonPath('summary.total_vessels', 50);
        $response->assertJsonPath('pagination.pagination_complete', false);

        Http::assertSentCount(2);
    }

    /**
     * TEST I — LOCAL DB INDEPENDENCE:
     * A vessel from GFW that has no entry in sistem_perikanan.vessels is processed and displayed.
     */
    public function test_i_local_db_independence_processes_vessel_not_in_sistem_perikanan(): void
    {
        $foreignEvent = $this->makeEvent('evt-foreign-1', 'gfw-foreign-tanker-999', 5.55, 95.32, [
            'vessel' => [
                'id' => 'gfw-foreign-tanker-999',
                'name' => 'MT MALACCA PASSAGE',
                'ssvid' => '352009999',
                'flag' => 'PAN',
                'type' => 'cargo',
            ],
        ]);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 1,
                'entries' => [$foreignEvent],
                'nextOffset' => null,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(200);
        $response->assertJsonPath('summary.total_vessels', 1);

        $vessels = $response->json('vessels');
        $this->assertSame('gfw-foreign-tanker-999', $vessels[0]['id']);
        $this->assertSame('MT MALACCA PASSAGE', $vessels[0]['name']);
        $this->assertSame('PAN', $vessels[0]['flag']);
    }

    /**
     * TEST J — DATASET SCOPE:
     * Upstream request strictly uses public-global-fishing-events:latest.
     */
    public function test_j_dataset_scope_remains_public_global_fishing_events(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 1,
                'entries' => [$this->makeEvent('evt-1', 'vessel-1')],
                'nextOffset' => null,
            ], 200),
        ]);

        $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        Http::assertSent(function (Request $request) {
            $data = $request->data();
            $datasets = $data['datasets'] ?? [];

            return count($datasets) === 1
                && $datasets[0] === 'public-global-fishing-events:latest'
                && isset($data['geometry'])
                && isset($data['startDate'])
                && isset($data['endDate']);
        });
    }
}

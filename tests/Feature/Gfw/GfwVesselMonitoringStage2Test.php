<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use App\Services\GFWService;
use App\Services\Gis\BigMaritimeBoundaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GfwVesselMonitoringStage2Test extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected GFWService $gfwService;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();

        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-secret-stage2-token-456');
        Config::set('gfw.fishing_events_dataset', 'public-global-fishing-events:latest');

        $permission = Permission::firstOrCreate(['name' => 'access.gis', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo($permission);

        $this->gfwService = app(GFWService::class);
    }

    /**
     * Helper to create a fake event inside BIG ZEE Aceh (near Sabang/Banda Aceh: 95.3, 5.5).
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
                'name' => "KM SAMUDRA {$vesselId}",
                'ssvid' => "52500{$vesselId}",
                'mmsi' => "52500{$vesselId}",
                'imo' => "90000{$vesselId}",
                'flag' => 'IDN',
                'type' => 'fishing',
            ],
        ], $overrides);
    }

    /**
     * TEST 1 — Filter tanggal valid (<= 7 hari kalender).
     */
    public function test_1_valid_date_range_succeeds(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 1,
                'entries' => [$this->makeEvent('evt-1', '101')],
                'nextOffset' => null,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-10&end_date=2026-09-16');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('period.start', '2026-09-10');
        $response->assertJsonPath('period.end', '2026-09-16');
    }

    /**
     * TEST 2 — Tanggal >7 hari ditolak dengan HTTP 422.
     */
    public function test_2_date_range_exceeding_7_days_is_rejected(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-01&end_date=2026-09-10');

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $this->assertStringContainsString('cannot exceed 7 days', $response->json('message'));
    }

    /**
     * TEST 3 — Search vessel berdasarkan name, MMSI, SSVID, IMO, dan GFW vessel ID.
     */
    public function test_3_search_vessel_by_multiple_attributes(): void
    {
        $v1 = $this->makeEvent('evt-1', 'vessel-alpha', 5.55, 95.32, [
            'vessel' => [
                'id' => 'vessel-alpha',
                'name' => 'BINTANG LAUT',
                'ssvid' => '525001111',
                'mmsi' => '525001111',
                'imo' => '9111111',
                'flag' => 'IDN',
                'type' => 'fishing',
            ],
        ]);
        $v2 = $this->makeEvent('evt-2', 'vessel-beta', 5.60, 95.40, [
            'vessel' => [
                'id' => 'vessel-beta-guid-999',
                'name' => 'PACIFIC GLORY',
                'ssvid' => '352002222',
                'mmsi' => '352002222',
                'imo' => '9222222',
                'flag' => 'PAN',
                'type' => 'cargo',
            ],
        ]);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 2,
                'entries' => [$v1, $v2],
                'nextOffset' => null,
            ], 200),
        ]);

        // Search by name substring
        $resName = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?search=bintang');
        $resName->assertStatus(200);
        $resName->assertJsonPath('summary.total_vessels', 1);
        $this->assertSame('vessel-alpha', $resName->json('vessels.0.id'));

        // Search by IMO
        $resImo = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?search=9222222');
        $resImo->assertStatus(200);
        $resImo->assertJsonPath('summary.total_vessels', 1);
        $this->assertSame('vessel-beta-guid-999', $resImo->json('vessels.0.id'));

        // Search by GFW Vessel ID
        $resId = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?search=beta-guid');
        $resId->assertStatus(200);
        $resId->assertJsonPath('summary.total_vessels', 1);
        $this->assertSame('vessel-beta-guid-999', $resId->json('vessels.0.id'));

        // Search by SSVID
        $resSsvid = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?search=352002222');
        $resSsvid->assertStatus(200);
        $resSsvid->assertJsonPath('summary.total_vessels', 1);
        $this->assertSame('vessel-beta-guid-999', $resSsvid->json('vessels.0.id'));
    }

    /**
     * TEST 4 — Filter flag.
     */
    public function test_4_filter_by_flag(): void
    {
        $vIdn = $this->makeEvent('evt-1', 'v-idn', 5.55, 95.32, ['vessel' => ['id' => 'v-idn', 'name' => 'KAPAL INDO', 'flag' => 'IDN', 'type' => 'fishing']]);
        $vTha = $this->makeEvent('evt-2', 'v-tha', 5.60, 95.40, ['vessel' => ['id' => 'v-tha', 'name' => 'THAI STAR', 'flag' => 'THA', 'type' => 'carrier']]);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 2,
                'entries' => [$vIdn, $vTha],
                'nextOffset' => null,
            ], 200),
        ]);

        $resTha = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?flag=THA');
        $resTha->assertStatus(200);
        $resTha->assertJsonPath('summary.total_vessels', 1);
        $this->assertSame('v-tha', $resTha->json('vessels.0.id'));
        $this->assertSame('THA', $resTha->json('vessels.0.flag'));
    }

    /**
     * TEST 5 — Filter vessel type.
     */
    public function test_5_filter_by_vessel_type(): void
    {
        $vFishing = $this->makeEvent('evt-1', 'v-fish', 5.55, 95.32, ['vessel' => ['id' => 'v-fish', 'name' => 'NELAYAN 1', 'type' => 'fishing']]);
        $vTanker = $this->makeEvent('evt-2', 'v-tank', 5.60, 95.40, ['vessel' => ['id' => 'v-tank', 'name' => 'OCEAN TANKER', 'type' => 'tanker']]);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 2,
                'entries' => [$vFishing, $vTanker],
                'nextOffset' => null,
            ], 200),
        ]);

        $resTank = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?vessel_type=Tanker');
        $resTank->assertStatus(200);
        $resTank->assertJsonPath('summary.total_vessels', 1);
        $this->assertSame('v-tank', $resTank->json('vessels.0.id'));
    }

    /**
     * TEST 6 — Filter activity.
     */
    public function test_6_filter_by_activity(): void
    {
        $vFish = $this->makeEvent('evt-1', 'v-act-1', 5.55, 95.32, ['type' => 'fishing']);
        $vEncounter = $this->makeEvent('evt-2', 'v-act-2', 5.60, 95.40, ['type' => 'encounter']);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 2,
                'entries' => [$vFish, $vEncounter],
                'nextOffset' => null,
            ], 200),
        ]);

        $resEnc = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?activity=Encounter');
        $resEnc->assertStatus(200);
        $resEnc->assertJsonPath('summary.total_vessels', 1);
        $this->assertSame('v-act-2', $resEnc->json('vessels.0.id'));
    }

    /**
     * TEST 7 — Pagination client (limit & offset).
     */
    public function test_7_client_pagination(): void
    {
        $events = [];
        for ($i = 1; $i <= 15; $i++) {
            $events[] = $this->makeEvent("evt-{$i}", "v-{$i}");
        }

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 15,
                'entries' => $events,
                'nextOffset' => null,
            ], 200),
        ]);

        // Request page 1 with limit 5
        $resP1 = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?limit=5&page=1');
        $resP1->assertStatus(200);
        $resP1->assertJsonPath('summary.total_vessels', 15);
        $this->assertCount(5, $resP1->json('vessels'));
        $this->assertSame('v-1', $resP1->json('vessels.0.id'));
        $resP1->assertJsonPath('pagination.has_more', true);

        // Request page 2 with limit 5
        $resP2 = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?limit=5&page=2');
        $resP2->assertStatus(200);
        $this->assertCount(5, $resP2->json('vessels'));
        $this->assertSame('v-6', $resP2->json('vessels.0.id'));
    }

    /**
     * TEST 8 — pagination_truncated=true saat safety limit tercapai.
     */
    public function test_8_pagination_truncated_when_safety_limit_reached(): void
    {
        // 5 pages of 100 events each
        $responses = [];
        for ($p = 1; $p <= 5; $p++) {
            $pageEntries = [];
            for ($i = 1; $i <= 100; $i++) {
                $idx = (($p - 1) * 100) + $i;
                $pageEntries[] = $this->makeEvent("evt-{$idx}", "vessel-{$idx}");
            }
            $offset = ($p - 1) * 100;
            $nextOffset = $p * 100;
            $responses["https://gateway.api.globalfishingwatch.org/v3/events?limit=100&offset={$offset}"] = Http::response([
                'total' => 800,
                'entries' => $pageEntries,
                'nextOffset' => $nextOffset,
            ], 200);
        }

        Http::fake($responses);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(200);
        $response->assertJsonPath('pagination.pagination_truncated', true);
        $response->assertJsonPath('pagination.pagination_complete', false);
        $response->assertJsonPath('pagination.upstream_events_count', 500);
    }

    /**
     * TEST 9 — Empty result saat tidak ada vessel yang sesuai filter.
     */
    public function test_9_empty_result_when_no_match(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 1,
                'entries' => [$this->makeEvent('evt-1', 'v-1', 5.55, 95.32, ['vessel' => ['name' => 'KM LAUTAN']])],
                'nextOffset' => null,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?search=nonexistentvesselname12345');

        $response->assertStatus(200);
        $response->assertJsonPath('summary.total_vessels', 0);
        $this->assertCount(0, $response->json('vessels'));
        $this->assertSame('No vessel detected in BIG ZEE Aceh for selected period.', $response->json('message'));
    }

    /**
     * TEST 10 — GFW API failure pada halaman 1 menghasilkan respons error terstruktur.
     */
    public function test_10_gfw_api_failure_handled_gracefully(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'error' => 'Bad Gateway from upstream GFW',
            ], 502),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(502);
        $response->assertJsonPath('success', false);
    }

    /**
     * TEST 11 — BIG geometry failure mengembalikan fail-safe HTTP 502.
     */
    public function test_11_big_geometry_failure_returns_fail_safe_502(): void
    {
        $mockBig = $this->mock(BigMaritimeBoundaryService::class);
        $mockBig->shouldReceive('getAcehZeeGeometry')->andReturn([
            'success' => false,
            'source' => 'BIG',
            'layer' => 'Peta Batas ZEE',
            'layer_id' => 10,
            'error' => 'BIG Layer 10 unavailable or upstream timeout',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(502);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('boundary_source', 'BIG');
        $response->assertJsonPath('boundary_layer', 10);
    }

    /**
     * TEST 12 — GFW vessel yang tidak ada di sistem_perikanan.vessels tetap muncul.
     */
    public function test_12_vessel_not_in_sistem_perikanan_appears(): void
    {
        $foreignVessel = $this->makeEvent('evt-foreign', 'foreign-cargo-001', 5.55, 95.32, [
            'vessel' => [
                'id' => 'foreign-cargo-001',
                'name' => 'MV STRAIT EXPRESS',
                'ssvid' => '636009999',
                'flag' => 'LBR',
                'type' => 'cargo',
            ],
        ]);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 1,
                'entries' => [$foreignVessel],
                'nextOffset' => null,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(200);
        $response->assertJsonPath('summary.total_vessels', 1);
        $this->assertSame('foreign-cargo-001', $response->json('vessels.0.id'));
        $this->assertSame('MV STRAIT EXPRESS', $response->json('vessels.0.name'));
    }

    /**
     * TEST 13 — Duplicate events menghasilkan satu vessel unik.
     */
    public function test_13_duplicate_events_deduplicate_to_single_vessel(): void
    {
        $e1 = $this->makeEvent('evt-1', 'same-vessel', 5.50, 95.30, ['start' => '2026-09-20T01:00:00Z', 'end' => '2026-09-20T02:00:00Z']);
        $e2 = $this->makeEvent('evt-2', 'same-vessel', 5.55, 95.35, ['start' => '2026-09-20T04:00:00Z', 'end' => '2026-09-20T05:00:00Z']);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 2,
                'entries' => [$e1, $e2],
                'nextOffset' => null,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(200);
        $response->assertJsonPath('summary.total_vessels', 1);
        $this->assertCount(1, $response->json('vessels'));
        // Verify last_seen is updated to latest event
        $this->assertSame('2026-09-20T05:00:00Z', $response->json('vessels.0.last_seen'));
        $this->assertEquals(5.55, $response->json('vessels.0.lat'));
    }

    /**
     * TEST 14 — BIG Server-side PIP tetap aktif (menolak event di luar ZEE Aceh).
     */
    public function test_14_big_pip_rejects_point_outside_zee(): void
    {
        // Inside BIG ZEE Aceh (5.55, 95.32)
        $inside = $this->makeEvent('evt-in', 'v-inside', 5.55, 95.32);
        // Outside ZEE Aceh: South of Java (-9.5, 110.0)
        $outside = $this->makeEvent('evt-out', 'v-outside', -9.5, 110.0);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 2,
                'entries' => [$inside, $outside],
                'nextOffset' => null,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(200);
        $response->assertJsonPath('summary.total_vessels', 1);
        $this->assertSame('v-inside', $response->json('vessels.0.id'));
    }

    /**
     * TEST 15 — Dataset request tetap public-global-fishing-events:latest.
     */
    public function test_15_dataset_request_remains_public_global_fishing_events(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 1,
                'entries' => [$this->makeEvent('evt-1', 'v-1')],
                'nextOffset' => null,
            ], 200),
        ]);

        $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        Http::assertSent(function (Request $request) {
            $datasets = $request->data()['datasets'] ?? [];

            return count($datasets) === 1 && $datasets[0] === 'public-global-fishing-events:latest';
        });
    }
}

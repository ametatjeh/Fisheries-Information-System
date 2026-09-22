<?php

namespace Tests\Feature\Gfw;

use App\Models\GfwEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GfwEventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Cache::flush();
        Config::set('gfw.api_key', 'test-events-key-12345');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');
        Config::set('gfw.event_cache_ttl', 3600);
    }

    public function test_apparent_fishing_events_success_and_persists(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'entries' => [
                    [
                        'id' => 'fishing-event-aceh-001',
                        'type' => 'fishing',
                        'vesselId' => 'gfw-vessel-aceh-01',
                        'lat' => 5.750,
                        'lon' => 95.120,
                        'start' => '2026-09-15T04:00:00Z',
                        'end' => '2026-09-15T09:30:00Z',
                        'durationHours' => 5.5,
                        'confidence' => 'high',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/events/fishing?region=aceh_waters&start_date=2026-09-10&end_date=2026-09-18');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'global_fishing_watch',
                'event_type' => 'apparent_fishing',
                'semantic_label' => 'Apparent Fishing Event',
                'total' => 1,
            ])
            ->assertJsonPath('data.0.gfw_event_id', 'fishing-event-aceh-001')
            ->assertJsonPath('data.0.gfw_vessel_id', 'gfw-vessel-aceh-01')
            ->assertJsonPath('data.0.confidence', 'high')
            ->assertJsonPath('data.0.duration_hours', 5.5);

        // Verify semantic disclaimer
        $disclaimer = $response->json('semantic_disclaimer');
        $this->assertStringContainsString('bukan merupakan verifikasi penangkapan faktual', $disclaimer);

        // Verify persisted to dedicated gfw_events table
        $this->assertDatabaseHas('gfw_events', [
            'gfw_event_id' => 'fishing-event-aceh-001',
            'event_type' => 'apparent_fishing',
            'region_key' => 'aceh_waters',
        ]);
        $this->assertEquals(1, GfwEvent::count());
    }

    public function test_potential_encounters_success_with_vessel_pairs(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'entries' => [
                    [
                        'id' => 'encounter-event-001',
                        'type' => 'encounter',
                        'vessels' => [
                            ['id' => 'vessel-carrier-01'],
                            ['id' => 'vessel-catcher-02'],
                        ],
                        'lat' => 4.500,
                        'lon' => 96.200,
                        'start' => '2026-09-16T12:00:00Z',
                        'end' => '2026-09-16T14:30:00Z',
                        'durationHours' => 2.5,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/events/encounters?region=indonesia_eez');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'event_type' => 'potential_encounter',
                'semantic_label' => 'Potential Encounter',
                'total' => 1,
            ])
            ->assertJsonPath('data.0.gfw_vessel_id', 'vessel-carrier-01')
            ->assertJsonPath('data.0.secondary_vessel_id', 'vessel-catcher-02');

        $disclaimer = $response->json('semantic_disclaimer');
        $this->assertStringContainsString('tidak dapat disimpulkan sebagai alih muatan', $disclaimer);
    }

    public function test_loitering_events_success_with_duration(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'entries' => [
                    [
                        'id' => 'loitering-event-001',
                        'type' => 'loitering',
                        'vesselId' => 'vessel-tanker-01',
                        'lat' => 3.200,
                        'lon' => 97.500,
                        'start' => '2026-09-17T01:00:00Z',
                        'end' => '2026-09-17T10:30:00Z',
                        'durationHours' => 9.5,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/events/loitering?region=wppnri_572');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'event_type' => 'loitering',
                'semantic_label' => 'Loitering Event',
                'total' => 1,
            ])
            ->assertJsonPath('data.0.duration_hours', 9.5);

        $disclaimer = $response->json('semantic_disclaimer');
        $this->assertStringContainsString('Loitering Event', $disclaimer);
    }

    public function test_port_visits_success_with_port_metadata(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'entries' => [
                    [
                        'id' => 'port-visit-001',
                        'type' => 'port_visit',
                        'vesselId' => 'vessel-cargo-01',
                        'port' => [
                            'name' => 'Banda Aceh Port',
                        ],
                        'lat' => 5.560,
                        'lon' => 95.320,
                        'start' => '2026-09-18T06:00:00Z',
                        'end' => '2026-09-19T06:00:00Z',
                        'durationHours' => 24.0,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/events/port-visits?region=aceh_waters');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'event_type' => 'port_visit',
                'semantic_label' => 'Port Visit',
                'total' => 1,
            ])
            ->assertJsonPath('data.0.port_name', 'Banda Aceh Port');

        $disclaimer = $response->json('semantic_disclaimer');
        $this->assertStringContainsString('bukan merupakan bukti langsung pendaratan atau pembongkaran hasil tangkapan', $disclaimer);
    }

    public function test_single_event_show_by_id(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events/event-single-xyz*' => Http::response([
                'id' => 'event-single-xyz',
                'type' => 'fishing',
                'vesselId' => 'vessel-single-01',
                'lat' => 5.100,
                'lon' => 95.800,
                'start' => '2026-09-18T00:00:00Z',
                'end' => '2026-09-18T05:00:00Z',
                'durationHours' => 5.0,
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/events/event-single-xyz');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'gfw_event_id' => 'event-single-xyz',
                    'event_type' => 'apparent_fishing',
                    'semantic_label' => 'Apparent Fishing Event',
                ],
            ]);
    }

    public function test_empty_events_results_handled_gracefully(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'entries' => [],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/events/fishing?region=wppnri_571');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'total' => 0,
                'data' => [],
            ]);
    }

    public function test_invalid_date_range_returns_422(): void
    {
        $response = $this->getJson('/api/gfw/events/encounters?start_date=2026-09-20&end_date=2026-09-10');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => 'start_date tidak boleh lebih besar daripada end_date.',
            ]);

        Http::assertNothingSent();
    }

    public function test_cache_hit_and_miss_per_event_type(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'entries' => [
                    [
                        'id' => 'cached-event-01',
                        'type' => 'loitering',
                        'vesselId' => 'vessel-cache',
                    ],
                ],
            ], 200),
        ]);

        // 1. Miss
        $res1 = $this->getJson('/api/gfw/events/loitering?region=aceh_waters');
        $res1->assertStatus(200)->assertJson(['success' => true, 'cached' => false, 'total' => 1]);
        Http::assertSentCount(1);

        // 2. Hit
        $res2 = $this->getJson('/api/gfw/events/loitering?region=aceh_waters');
        $res2->assertStatus(200)->assertJson(['success' => true, 'cached' => true, 'total' => 1]);
        Http::assertSentCount(1);
    }

    public function test_events_do_not_mutate_local_fisheries_tables(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'entries' => [
                    [
                        'id' => 'event-no-leak',
                        'type' => 'fishing',
                        'vesselId' => 'gfw-vessel-isolated',
                    ],
                ],
            ], 200),
        ]);

        $this->getJson('/api/gfw/events/fishing?region=indonesia_eez');

        // Local master and transactional tables must remain completely unmutated
        $this->assertDatabaseCount('catches', 0);
        $this->assertDatabaseCount('landings', 0);
        $this->assertDatabaseCount('fishing_trips', 0);
        $this->assertDatabaseCount('vessels', 0);
    }
}

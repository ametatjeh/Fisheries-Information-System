<?php

namespace Tests\Feature\Gfw;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GfwEventsZeeIndonesiaAcehTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function test_default_request_returns_normalized_events_and_data_contract(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-valid-token-gfw-04');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 40,
                'entries' => [
                    [
                        'id' => 'event-aceh-001',
                        'type' => 'fishing',
                        'start' => '2026-09-01T04:08:15.000Z',
                        'end' => '2026-09-01T12:17:14.000Z',
                        'position' => ['lat' => 5.1987, 'lon' => 98.107],
                        'vessel' => [
                            'id' => 'vessel-001',
                            'name' => 'ISMARINE898 A',
                            'ssvid' => '525137044',
                            'flag' => 'IDN',
                            'type' => 'fishing',
                        ],
                        'regions' => ['eez' => ['8492']],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/events/zee-indonesia-aceh');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'Global Fishing Watch',
                'aoi' => 'ZEE Indonesia - Kawasan Aceh',
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-07',
                'event_count' => 40,
                'returned_count' => 1,
                'pagination' => [
                    'limit' => 50,
                    'offset' => 0,
                    'total' => 40,
                ],
            ])
            ->assertJsonStructure([
                'success',
                'source',
                'aoi',
                'start_date',
                'end_date',
                'event_count',
                'returned_count',
                'pagination' => ['limit', 'offset', 'total', 'next_offset'],
                'events' => [
                    '*' => [
                        'id',
                        'type',
                        'start',
                        'end',
                        'position' => ['lat', 'lon'],
                        'vessel',
                        'regions',
                        'dataset',
                    ],
                ],
            ]);
    }

    public function test_limit_one_returns_maximum_one_event(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-valid-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 40,
                'entries' => [
                    [
                        'id' => 'event-001',
                        'type' => 'fishing',
                        'start' => '2026-09-01T04:00:00.000Z',
                        'end' => '2026-09-01T12:00:00.000Z',
                        'position' => ['lat' => 5.2, 'lon' => 98.1],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/events/zee-indonesia-aceh?limit=1');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'event_count' => 40,
                'returned_count' => 1,
                'pagination' => [
                    'limit' => 1,
                    'offset' => 0,
                    'total' => 40,
                ],
            ]);

        $this->assertCount(1, $response->json('events'));

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'limit=1');
        });
    }

    public function test_limit_two_returns_maximum_two_events(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-valid-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 40,
                'entries' => [
                    ['id' => 'event-001', 'type' => 'fishing'],
                    ['id' => 'event-002', 'type' => 'fishing'],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/events/zee-indonesia-aceh?limit=2');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'event_count' => 40,
                'returned_count' => 2,
                'pagination' => [
                    'limit' => 2,
                    'offset' => 0,
                    'total' => 40,
                ],
            ]);

        $this->assertCount(2, $response->json('events'));
    }

    public function test_maximum_limit_is_accepted(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-valid-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 100,
                'entries' => [],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/events/zee-indonesia-aceh?limit=100');

        $response->assertStatus(200)
            ->assertJson([
                'pagination' => [
                    'limit' => 100,
                ],
            ]);
    }

    public function test_invalid_limit_values_return_422(): void
    {
        $invalidLimits = ['0', '-1', 'abc', '101', '1.5'];

        foreach ($invalidLimits as $invalidLimit) {
            $response = $this->getJson("/api/gfw/events/zee-indonesia-aceh?limit={$invalidLimit}");

            $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Parameter limit harus berupa bilangan bulat antara 1 dan 100.',
                ]);
        }

        Http::assertNothingSent();
    }

    public function test_one_day_range_passes_validation(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 0,
                'entries' => [],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/events/zee-indonesia-aceh?start_date=2026-09-01&end_date=2026-09-01');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-01',
            ]);
    }

    public function test_seven_day_range_passes_validation(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 0,
                'entries' => [],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/events/zee-indonesia-aceh?start_date=2026-09-01&end_date=2026-09-07');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-07',
            ]);
    }

    public function test_eight_day_range_fails_validation(): void
    {
        $response = $this->getJson('/api/gfw/events/zee-indonesia-aceh?start_date=2026-09-01&end_date=2026-09-08');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Date range cannot exceed 7 days during GFW-03/GFW-04 testing.',
            ]);

        Http::assertNothingSent();
    }

    public function test_invalid_calendar_date_returns_422(): void
    {
        $invalidDates = [
            '2026-99-99',
            '2026-02-30',
            'not-a-date',
            '2026/09/01',
        ];

        foreach ($invalidDates as $invalidDate) {
            $response = $this->getJson("/api/gfw/events/zee-indonesia-aceh?start_date={$invalidDate}&end_date=2026-09-07");
            $response->assertStatus(422);
        }

        Http::assertNothingSent();
    }

    public function test_reversed_dates_return_422(): void
    {
        $response = $this->getJson('/api/gfw/events/zee-indonesia-aceh?start_date=2026-09-07&end_date=2026-09-01');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'start_date harus lebih kecil atau sama dengan end_date.',
            ]);

        Http::assertNothingSent();
    }

    public function test_duplicate_upstream_events_are_deduplicated_by_id(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 2,
                'entries' => [
                    ['id' => 'dup-event-1', 'type' => 'fishing'],
                    ['id' => 'dup-event-1', 'type' => 'fishing'],
                    ['id' => 'dup-event-2', 'type' => 'fishing'],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/events/zee-indonesia-aceh?limit=10');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('returned_count'));
        $this->assertCount(2, $response->json('events'));
    }

    public function test_safe_error_handling_for_gfw_401(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'invalid-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'error' => 'Unauthorized',
            ], 401),
        ]);

        $response = $this->getJson('/api/gfw/events/zee-indonesia-aceh');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'GFW API authentication failed',
            ]);
    }

    public function test_safe_error_handling_for_gfw_429(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'error' => 'Rate limit exceeded',
            ], 429),
        ]);

        $response = $this->getJson('/api/gfw/events/zee-indonesia-aceh');

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'message' => 'GFW API rate limit reached',
            ]);
    }

    public function test_safe_error_handling_for_gfw_503_and_502(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'error' => 'Service Unavailable',
            ], 503),
        ]);

        $response = $this->getJson('/api/gfw/events/zee-indonesia-aceh');

        $response->assertStatus(502)
            ->assertJson([
                'success' => false,
                'message' => 'GFW API server error',
            ]);
    }

    public function test_safe_error_handling_for_timeout_connection_exception(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => function () {
                throw new ConnectionException('Timeout connecting to GFW gateway');
            },
        ]);

        $response = $this->getJson('/api/gfw/events/zee-indonesia-aceh');

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'message' => 'Unable to connect to GFW API',
            ]);
    }

    public function test_token_is_never_leaked_in_response_body(): void
    {
        $secretToken = 'my-ultra-secret-gfw-token-999';
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', $secretToken);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'error' => 'Header contains '.$secretToken,
            ], 400),
        ]);

        $response = $this->getJson('/api/gfw/events/zee-indonesia-aceh');

        $content = $response->getContent();
        $this->assertStringNotContainsString($secretToken, $content);
        $this->assertStringNotContainsString('authorization', strtolower($content));
        $this->assertStringNotContainsString('bearer', strtolower($content));
    }

    public function test_limit_twenty_five_and_fifty_are_accepted(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-valid-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 100,
                'entries' => [],
            ], 200),
        ]);

        $response25 = $this->getJson('/api/gfw/events/zee-indonesia-aceh?limit=25');
        $response25->assertStatus(200)
            ->assertJson([
                'pagination' => ['limit' => 25, 'offset' => 0],
            ]);

        $response50 = $this->getJson('/api/gfw/events/zee-indonesia-aceh?limit=50');
        $response50->assertStatus(200)
            ->assertJson([
                'pagination' => ['limit' => 50, 'offset' => 0],
            ]);
    }

    public function test_valid_offset_and_page_parameters(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-valid-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 100,
                'entries' => [
                    ['id' => 'ev-50', 'type' => 'fishing'],
                ],
            ], 200),
        ]);

        $responseOffset = $this->getJson('/api/gfw/events/zee-indonesia-aceh?limit=25&offset=50');
        $responseOffset->assertStatus(200)
            ->assertJson([
                'pagination' => [
                    'limit' => 25,
                    'offset' => 50,
                    'total' => 100,
                ],
            ]);

        // page 3 with limit 25 => offset 50
        $responsePage = $this->getJson('/api/gfw/events/zee-indonesia-aceh?limit=25&page=3');
        $responsePage->assertStatus(200)
            ->assertJson([
                'pagination' => [
                    'limit' => 25,
                    'offset' => 50,
                    'total' => 100,
                ],
            ]);
    }

    public function test_negative_offset_and_invalid_page_return_422(): void
    {
        $responseNegativeOffset = $this->getJson('/api/gfw/events/zee-indonesia-aceh?offset=-1');
        $responseNegativeOffset->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Parameter offset harus berupa bilangan bulat >= 0.',
            ]);

        $responseInvalidPage = $this->getJson('/api/gfw/events/zee-indonesia-aceh?page=0');
        $responseInvalidPage->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Parameter page harus berupa bilangan bulat positif >= 1.',
            ]);
    }

    public function test_pagination_next_offset_null_when_on_last_batch(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-valid-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 40,
                'entries' => array_map(fn ($i) => ['id' => 'ev-'.$i, 'type' => 'fishing'], range(1, 10)),
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/events/zee-indonesia-aceh?limit=50&offset=30');
        $response->assertStatus(200)
            ->assertJson([
                'pagination' => [
                    'limit' => 50,
                    'offset' => 30,
                    'total' => 40,
                    'next_offset' => null,
                ],
            ]);
    }
}

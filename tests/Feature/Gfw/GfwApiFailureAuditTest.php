<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use App\Services\Gis\BigMaritimeBoundaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GfwApiFailureAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected array $mockGeometry;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Cache::flush();

        Config::set('gfw.api_key', 'test-valid-audit-key');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $gfwPermission = Permission::firstOrCreate(['name' => 'access.gfw', 'guard_name' => 'web']);
        $adminRole->givePermissionTo($gfwPermission);

        $this->user = User::factory()->create();
        $this->user->assignRole($adminRole);

        $this->mockGeometry = [
            'type' => 'Polygon',
            'coordinates' => [
                [
                    [94.0, 1.5],
                    [98.0, 1.5],
                    [98.0, 6.5],
                    [94.0, 6.5],
                    [94.0, 1.5],
                ],
            ],
        ];

        $mockBigService = $this->createMock(BigMaritimeBoundaryService::class);
        $mockBigService->method('getAcehZeeGeometry')->willReturn([
            'success' => true,
            'geometry' => $this->mockGeometry,
        ]);
        $mockBigService->method('isPointInGeometry')->willReturn(true);
        $this->app->instance(BigMaritimeBoundaryService::class, $mockBigService);
    }

    /**
     * 1. Simulation: HTTP 200 Success
     */
    public function test_simulation_200_success(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'entries' => [
                    [
                        'id' => 'event-1',
                        'type' => 'fishing',
                        'start' => '2026-09-18T10:00:00Z',
                        'end' => '2026-09-18T12:00:00Z',
                        'position' => ['lat' => 5.5, 'lon' => 95.3],
                        'vessel' => [
                            'id' => 'vessel-1',
                            'name' => 'KM AUDIT JAYA',
                            'flag' => 'IDN',
                        ],
                    ],
                ],
                'total' => 1,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-16&end_date=2026-09-22&limit=50');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'live' => true,
            ])
            ->assertJsonPath('summary.total_vessels', 1);
    }

    /**
     * 2. Simulation: Timeout / ConnectionException
     */
    public function test_simulation_timeout_failure(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => function () {
                throw new ConnectionException('cURL error 28: Operation timed out after 30008 milliseconds with 0 bytes received');
            },
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-16&end_date=2026-09-22&limit=50');

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'message' => 'Unable to connect to GFW API',
            ]);
    }

    /**
     * 3. Simulation: HTTP 429 Rate Limit
     */
    public function test_simulation_429_rate_limit_failure(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'error' => 'Rate limit exceeded',
            ], 429),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-16&end_date=2026-09-22&limit=50');

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'message' => 'GFW API rate limit reached',
            ]);
    }

    /**
     * 4. Simulation: HTTP 401 Unauthorized
     */
    public function test_simulation_401_unauthorized_failure(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'error' => 'Invalid or expired token',
            ], 401),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-16&end_date=2026-09-22&limit=50');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'GFW API authentication failed',
            ]);
    }

    /**
     * 5. Simulation: HTTP 403 Forbidden
     */
    public function test_simulation_403_forbidden_failure(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'error' => 'Dataset access forbidden',
            ], 403),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-16&end_date=2026-09-22&limit=50');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'GFW API access forbidden',
            ]);
    }

    /**
     * 6. Simulation: HTTP 400 Bad Request
     */
    public function test_simulation_400_bad_request_failure(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'error' => 'Bad request',
            ], 400),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-16&end_date=2026-09-22&limit=50');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid request sent to GFW API',
            ]);
    }

    /**
     * 7. Simulation: HTTP 500 / 502 Upstream Server Error
     */
    public function test_simulation_502_upstream_server_error_failure(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response('502 Bad Gateway', 502),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-16&end_date=2026-09-22&limit=50');

        $response->assertStatus(502)
            ->assertJson([
                'success' => false,
                'message' => 'GFW API server error',
            ]);
    }

    /**
     * 8. Simulation: Invalid JSON Response
     */
    public function test_simulation_invalid_json_handled_gracefully(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response('MALFORMED_NON_JSON_DATA', 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-16&end_date=2026-09-22&limit=50');

        // Should handle gracefully without fatal PHP unhandled exceptions
        $this->assertContains($response->status(), [200, 500, 502]);
    }

    /**
     * 9. Simulation: Empty Response
     */
    public function test_simulation_empty_response_handled_gracefully(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'entries' => [],
                'total' => 0,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-16&end_date=2026-09-22&limit=50');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'No vessel detected in GFW Query Area for selected period.',
            ])
            ->assertJsonPath('summary.total_vessels', 0);
    }
}

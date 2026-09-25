<?php

namespace Tests\Feature\Gfw;

use App\Models\Gfw\GfwSyncRun;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GfwRelativeTimeFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Cache::flush();
        Config::set('gfw.api_key', 'test-token');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $gfwPermission = Permission::firstOrCreate(['name' => 'access.gfw', 'guard_name' => 'web']);
        $adminRole->givePermissionTo($gfwPermission);

        $this->user = User::factory()->create();
        $this->user->assignRole($adminRole);
    }

    public function test_vessels_endpoint_passes_explicit_zulu_timestamp_to_view(): void
    {
        // Seed a successful GfwSyncRun matching the test case
        GfwSyncRun::create([
            'aoi' => 'zee-indonesia-aceh',
            'date_from' => '2026-09-15',
            'date_to' => '2026-09-22',
            'dataset' => 'public-global-vessel-tracks:latest',
            'endpoint' => '/vessels/{id}/tracks',
            'records_found' => 10,
            'records_saved' => 10,
            'status' => 'success',
            'started_at' => Carbon::parse('2026-09-22 16:53:07', 'UTC'),
            'finished_at' => Carbon::parse('2026-09-22 16:53:15', 'UTC'),
        ]);

        $response = $this->actingAs($this->user)->get('/gfw/vessels?start_date=2026-09-16&end_date=2026-09-22&limit=50');

        $response->assertStatus(200);

        // Verify explicit Zulu ISO-8601 timestamp in view data and HTML
        $response->assertViewHas('lastSuccessfulSync', '2026-09-22T16:53:15Z');
        $response->assertSee('2026-09-22T16:53:15Z', false);

        // Verify raw timestamp debug console log exists
        $response->assertSee('[GFW Vessels Debug] Raw timestamp received from server:', false);

        // Verify dynamic age calculation function exists and uses Date.now()
        $response->assertSee('calcAgeFromTimestamp(lastSuccessfulTimestamp)', false);
        $response->assertSee('Date.now() - parsed', false);

        // Verify formatting logic handles days and hours
        $response->assertSee('${days} hari ${remainingHours} jam yang lalu', false);
    }
}

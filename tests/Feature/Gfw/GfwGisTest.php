<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GfwGisTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create([
            'email' => 'admin_gis_test@example.com',
        ]);
        $this->adminUser->assignRole('super-admin');
    }

    public function test_gfw_monitoring_page_requires_authentication(): void
    {
        $response = $this->get('/gfw/monitoring');
        $response->assertRedirect('/login');
    }

    public function test_gfw_monitoring_page_renders_successfully_for_authorized_user(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/gfw/monitoring');

        $response->assertStatus(200)
            ->assertSee('GFW Vessel Monitoring')
            ->assertSee('Pemantauan Aktivitas Kapal')
            ->assertSee('Perairan Aceh (Aceh Maritime Observation Zone)')
            ->assertSee('Indonesia EEZ');
    }

    public function test_gfw_monitoring_page_contains_leaflet_and_all_six_layers(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/gfw/monitoring');

        $response->assertStatus(200)
            ->assertSee('leaflet.js')
            ->assertSee('leaflet.css')
            ->assertSee('gfw-map')
            ->assertSee('GFW Vessel Presence')
            ->assertSee('Apparent Fishing Events')
            ->assertSee('Potential Encounters')
            ->assertSee('Loitering Events')
            ->assertSee('Port Visits')
            ->assertSee('Lintasan Track Kapal');
    }

    public function test_gfw_monitoring_page_contains_latency_notice_and_never_claims_live(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/gfw/monitoring');

        $response->assertStatus(200)
            ->assertSee('latensi (delay berkala 24-72 jam)')
            ->assertSee('bukan merupakan posisi langsung')
            ->assertDontSee('LIVE REAL-TIME STREAM');
    }

    public function test_gfw_monitoring_page_calls_only_internal_api_endpoints(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/gfw/monitoring');

        $content = $response->getContent();

        // Must fetch only via /api/gfw/
        $this->assertStringContainsString('/api/gfw/activity/presence', $content);
        $this->assertStringContainsString('/api/gfw/events/fishing', $content);
        $this->assertStringContainsString('/api/gfw/events/encounters', $content);
        $this->assertStringContainsString('/api/gfw/events/loitering', $content);
        $this->assertStringContainsString('/api/gfw/events/port-visits', $content);
        $this->assertStringContainsString('/api/gfw/vessels', $content);

        // Must NOT call gateway.api.globalfishingwatch.org directly from frontend JS
        $this->assertStringNotContainsString('fetch(\'https://gateway.api.globalfishingwatch.org', $content);
        $this->assertStringNotContainsString('fetch("https://gateway.api.globalfishingwatch.org', $content);
    }

    public function test_existing_local_gis_page_continues_to_function_unaltered(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/gis');

        $response->assertStatus(200)
            ->assertSee('Peta Geospasial Perikanan Tangkap')
            ->assertSee('fisheries-map')
            ->assertSee('Pelabuhan/TPI');
    }
}

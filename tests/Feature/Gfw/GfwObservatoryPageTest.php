<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GfwObservatoryPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $userWithGis;

    protected User $userWithoutGis;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gfw.api_key', 'test-secret-gfw-token-observatory');

        $gisPermission = Permission::firstOrCreate(['name' => 'access.gis', 'guard_name' => 'web']);

        $this->userWithGis = User::factory()->create();
        $this->userWithGis->givePermissionTo($gisPermission);

        $this->userWithoutGis = User::factory()->create();
    }

    public function test_unauthenticated_user_redirected_from_observatory_page(): void
    {
        $response = $this->get(route('gfw.observatory'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_gis_permission_forbidden(): void
    {
        $response = $this->actingAs($this->userWithoutGis)->get(route('gfw.observatory'));

        $response->assertStatus(403);
    }

    public function test_user_with_gis_permission_can_view_observatory_page(): void
    {
        $response = $this->actingAs($this->userWithGis)->get(route('gfw.observatory'));

        $response->assertStatus(200);
        $response->assertViewIs('gfw.observatory');
        $response->assertViewHasAll([
            'stats',
            'syncStatus',
            'aoiSummary',
            'latencyNotice',
        ]);

        // Key UI element checks
        $response->assertSee('GFW Vessel Observatory');
        $response->assertSee('observatory-map');
        $response->assertSee('vessels-table-body');
        $response->assertSee('vessel-detail-drawer');
    }

    public function test_observatory_page_does_not_leak_gfw_api_token(): void
    {
        $rawToken = (string) config('gfw.api_key');

        $response = $this->actingAs($this->userWithGis)->get(route('gfw.observatory'));

        $response->assertStatus(200);

        if (! empty($rawToken)) {
            $response->assertDontSee($rawToken);
        }

        $response->assertDontSee('GFW_API_TOKEN');
        $response->assertDontSee('Bearer ey');
    }
}

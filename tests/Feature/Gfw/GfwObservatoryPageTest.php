<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GfwObservatoryPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $userWithGis;

    protected User $userWithoutGis;

    protected User $adminUser;

    protected User $superAdminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gfw.api_key', 'test-secret-gfw-token-observatory');

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $gisPermission = Permission::firstOrCreate(['name' => 'access.gis', 'guard_name' => 'web']);
        $gfwPermission = Permission::firstOrCreate(['name' => 'access.gfw', 'guard_name' => 'web']);
        $adminRole->givePermissionTo($gfwPermission);

        $this->userWithGis = User::factory()->create();
        $this->userWithGis->givePermissionTo($gisPermission);

        $this->userWithoutGis = User::factory()->create();

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);

        $this->superAdminUser = User::factory()->create();
        $this->superAdminUser->assignRole($superAdminRole);
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

    public function test_user_with_only_gis_permission_forbidden_from_gfw(): void
    {
        $response = $this->actingAs($this->userWithGis)->get(route('gfw.observatory'));

        $response->assertStatus(403);
    }

    public function test_administrator_data_role_can_view_observatory_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('gfw.observatory'));

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

    public function test_super_admin_role_can_view_observatory_page(): void
    {
        $response = $this->actingAs($this->superAdminUser)->get(route('gfw.observatory'));

        $response->assertStatus(200);
        $response->assertViewIs('gfw.observatory');
    }

    public function test_observatory_page_does_not_leak_gfw_api_token(): void
    {
        $rawToken = (string) config('gfw.api_key');

        $response = $this->actingAs($this->adminUser)->get(route('gfw.observatory'));

        $response->assertStatus(200);

        if (! empty($rawToken)) {
            $response->assertDontSee($rawToken);
        }

        $response->assertDontSee('GFW_API_TOKEN');
        $response->assertDontSee('Bearer ey');
    }
}

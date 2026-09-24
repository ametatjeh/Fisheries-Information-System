<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GfwNavigationRestorationTest extends TestCase
{
    use RefreshDatabase;

    protected User $userWithGis;

    protected User $userWithoutGis;

    protected function setUp(): void
    {
        parent::setUp();

        $gisPermission = Permission::firstOrCreate(['name' => 'access.gis', 'guard_name' => 'web']);
        $masterPermission = Permission::firstOrCreate(['name' => 'access.master', 'guard_name' => 'web']);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo([$gisPermission, $masterPermission]);

        $this->userWithGis = User::factory()->create();
        $this->userWithGis->assignRole($adminRole);

        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $this->userWithoutGis = User::factory()->create();
        $this->userWithoutGis->assignRole($viewerRole);
    }

    public function test_sidebar_displays_dedicated_gfw_section_with_clean_labels(): void
    {
        $response = $this->actingAs($this->userWithGis)->get(route('dashboard'));

        $response->assertStatus(200);

        // Verify Output section has Peta Terpadu GIS
        $response->assertSee('Peta Terpadu GIS');
        $response->assertSee(route('dashboard.gis'));

        // Verify GFW Satellite section and links
        $response->assertSee('GFW Satellite');
        $response->assertSee('GFW Monitoring');
        $response->assertSee(route('gfw.monitoring'));
        $response->assertSee('GFW Vessel Observatory');
        $response->assertSee(route('gfw.vessels'));

        // Verify distinction from local vessel master data
        $response->assertSee('Master Data');
        $response->assertSee(route('master.vessels.index'));
        $response->assertSee('Kapal');
    }

    public function test_sidebar_hides_gfw_section_from_unauthorized_users(): void
    {
        $response = $this->actingAs($this->userWithoutGis)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('GFW Satellite');
        $response->assertDontSee(route('gfw.monitoring'));
        $response->assertDontSee('GFW Vessel Observatory');
    }

    public function test_active_menu_state_correct_on_gfw_pages(): void
    {
        // On GFW monitoring page
        $responseMon = $this->actingAs($this->userWithGis)->get(route('gfw.monitoring'));
        $responseMon->assertStatus(200);

        // On GFW observatory page
        $responseObs = $this->actingAs($this->userWithGis)->get(route('gfw.observatory'));
        $responseObs->assertStatus(200);

        // On GFW vessels page
        $responseVessels = $this->actingAs($this->userWithGis)->get(route('gfw.vessels'));
        $responseVessels->assertStatus(200);
    }
}

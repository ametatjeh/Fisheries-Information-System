<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_super_admin_has_full_access_to_all_modules(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin)->get('/dashboard')->assertOk();
        $this->actingAs($superAdmin)->get('/master/wilayah')->assertOk();
        $this->actingAs($superAdmin)->get('/master/fishermen')->assertOk();
        $this->actingAs($superAdmin)->get('/trips')->assertOk();
        $this->actingAs($superAdmin)->get('/logbooks')->assertOk();
        $this->actingAs($superAdmin)->get('/catches')->assertOk();
        $this->actingAs($superAdmin)->get('/analysis/validation')->assertOk();
        $this->actingAs($superAdmin)->get('/analysis/statistics')->assertOk();
        $this->actingAs($superAdmin)->get('/reports')->assertOk();
    }

    public function test_admin_access_matrix(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Boleh akses: Dashboard, Master (Wilayah, Nelayan), Statistik, Laporan
        $this->actingAs($admin)->get('/dashboard')->assertOk();
        $this->actingAs($admin)->get('/master/wilayah')->assertOk();
        $this->actingAs($admin)->get('/master/fishermen')->assertOk();
        $this->actingAs($admin)->get('/analysis/statistics')->assertOk();
        $this->actingAs($admin)->get('/reports')->assertOk();

        // Ditolak (403 Forbidden): Validasi, Trip, Logbook, Catch
        $this->actingAs($admin)->get('/analysis/validation')->assertForbidden();
        $this->actingAs($admin)->get('/trips')->assertForbidden();
        $this->actingAs($admin)->get('/logbooks')->assertForbidden();
        $this->actingAs($admin)->get('/catches')->assertForbidden();
    }

    public function test_verifikator_access_matrix(): void
    {
        $verifikator = User::factory()->create();
        $verifikator->assignRole('verifikator');

        // Boleh akses: Dashboard, Validasi Data
        $this->actingAs($verifikator)->get('/dashboard')->assertOk();
        $this->actingAs($verifikator)->get('/analysis/validation')->assertOk();

        // Ditolak (403 Forbidden): Master, Nelayan, Trip, Logbook, Statistik, Laporan
        $this->actingAs($verifikator)->get('/master/wilayah')->assertForbidden();
        $this->actingAs($verifikator)->get('/master/fishermen')->assertForbidden();
        $this->actingAs($verifikator)->get('/trips')->assertForbidden();
        $this->actingAs($verifikator)->get('/analysis/statistics')->assertForbidden();
        $this->actingAs($verifikator)->get('/reports')->assertForbidden();
    }

    public function test_petugas_lapangan_access_matrix(): void
    {
        $petugas = User::factory()->create();
        $petugas->assignRole('petugas-lapangan');

        // Boleh akses: Dashboard, Nelayan, Trip, Logbook, Catch
        $this->actingAs($petugas)->get('/dashboard')->assertOk();
        $this->actingAs($petugas)->get('/master/fishermen')->assertOk();
        $this->actingAs($petugas)->get('/trips')->assertOk();
        $this->actingAs($petugas)->get('/logbooks')->assertOk();
        $this->actingAs($petugas)->get('/catches')->assertOk();

        // Ditolak (403 Forbidden): Master Wilayah, Validasi, Statistik, Laporan
        $this->actingAs($petugas)->get('/master/wilayah')->assertForbidden();
        $this->actingAs($petugas)->get('/analysis/validation')->assertForbidden();
        $this->actingAs($petugas)->get('/analysis/statistics')->assertForbidden();
        $this->actingAs($petugas)->get('/reports')->assertForbidden();
    }

    public function test_viewer_access_matrix(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        // Boleh akses: Dashboard, Laporan
        $this->actingAs($viewer)->get('/dashboard')->assertOk();
        $this->actingAs($viewer)->get('/reports')->assertOk();

        // Ditolak (403 Forbidden): Master, Nelayan, Trip, Logbook, Validasi, Statistik
        $this->actingAs($viewer)->get('/master/wilayah')->assertForbidden();
        $this->actingAs($viewer)->get('/master/fishermen')->assertForbidden();
        $this->actingAs($viewer)->get('/trips')->assertForbidden();
        $this->actingAs($viewer)->get('/analysis/validation')->assertForbidden();
        $this->actingAs($viewer)->get('/analysis/statistics')->assertForbidden();
    }

    public function test_sidebar_renders_appropriate_menu_items_for_roles(): void
    {
        // 1. Petugas Lapangan melihat Nelayan dan Trip, tidak melihat Statistik atau Laporan
        $petugas = User::factory()->create();
        $petugas->assignRole('petugas-lapangan');
        $responsePetugas = $this->actingAs($petugas)->get('/dashboard');
        $responsePetugas->assertSee(route('trips.index'));
        $responsePetugas->assertSee(route('master.fishermen.index'));
        $responsePetugas->assertDontSee(route('analysis.statistics.index'));
        $responsePetugas->assertDontSee(route('reports.index'));

        // 2. Viewer melihat Laporan, tidak melihat Master Data atau Trip
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');
        $responseViewer = $this->actingAs($viewer)->get('/dashboard');
        $responseViewer->assertSee(route('reports.index'));
        $responseViewer->assertDontSee(route('trips.index'));
        $responseViewer->assertDontSee(route('master.wilayah.index'));
        $responseViewer->assertDontSee(route('master.fishermen.index'));
        $responseViewer->assertDontSee(route('analysis.validation.index'));
    }
}

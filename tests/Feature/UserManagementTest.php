<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_super_admin_can_view_user_management_index(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Manajemen Akun & Hak Akses Pengguna');
        $response->assertSee($superAdmin->name);
    }

    public function test_super_admin_can_create_new_user_with_role(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)->post('/admin/users', [
            'name' => 'Petugas Verifikasi Baru',
            'email' => 'verifikator.baru@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'verifikator',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $newUser = User::where('email', 'verifikator.baru@gmail.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('Petugas Verifikasi Baru', $newUser->name);
        $this->assertTrue(Hash::check('password123', $newUser->password));
        $this->assertNotNull($newUser->email_verified_at);
        $this->assertTrue($newUser->hasRole('verifikator'));
    }

    public function test_super_admin_can_update_user_details_and_role(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $user = User::factory()->create([
            'name' => 'Nama Lama',
            'email' => 'lama@gmail.com',
        ]);
        $user->assignRole('viewer');

        $response = $this->actingAs($superAdmin)->put("/admin/users/{$user->id}", [
            'name' => 'Nama Baru Diperbarui',
            'email' => 'baru@gmail.com',
            'role' => 'petugas-lapangan',
        ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('Nama Baru Diperbarui', $user->name);
        $this->assertEquals('baru@gmail.com', $user->email);
        $this->assertTrue($user->hasRole('petugas-lapangan'));
        $this->assertFalse($user->hasRole('viewer'));
    }

    public function test_super_admin_can_update_user_password(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $user = User::factory()->create(['password' => Hash::make('oldpassword')]);
        $user->assignRole('admin');

        $response = $this->actingAs($superAdmin)->put("/admin/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'admin',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect('/admin/users');
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_super_admin_can_delete_other_user(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $user = User::factory()->create();
        $user->assignRole('viewer');

        $response = $this->actingAs($superAdmin)->delete("/admin/users/{$user->id}");

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_super_admin_cannot_delete_own_account(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)->delete("/admin/users/{$superAdmin->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    public function test_non_super_admin_is_forbidden_from_user_management(): void
    {
        // 1. Admin
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin)->get('/admin/users')->assertForbidden();
        $this->actingAs($admin)->post('/admin/users', [])->assertForbidden();

        // 2. Verifikator
        $verifikator = User::factory()->create();
        $verifikator->assignRole('verifikator');
        $this->actingAs($verifikator)->get('/admin/users')->assertForbidden();

        // 3. Petugas Lapangan
        $petugas = User::factory()->create();
        $petugas->assignRole('petugas-lapangan');
        $this->actingAs($petugas)->get('/admin/users')->assertForbidden();

        // 4. Viewer
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');
        $this->actingAs($viewer)->get('/admin/users')->assertForbidden();

        // 5. Guest (belum login)
        $this->post('/logout');
        $this->get('/admin/users')->assertRedirect('/login');
    }
}

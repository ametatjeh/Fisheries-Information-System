<?php

namespace Tests\Feature\Settings;

use App\Models\OrganizationSetting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrganizationSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super-admin');

        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole('viewer');
    }

    /**
     * 1. Administrator dapat mengakses halaman pengaturan identitas organisasi.
     */
    public function test_administrator_can_view_organization_settings_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('settings.organization.edit'));

        $response->assertOk();
        $response->assertSee('IDENTITAS ORGANISASI');
        $response->assertSee('Nama Organisasi/Pemilik');
        $response->assertSee('Jenis Organisasi');
        $response->assertSee('placeholder="Instansi, Lembaga, Forum"', false);
        $response->assertSee('SIMPAN PERUBAHAN');
    }

    /**
     * 2. User biasa/non-admin ditolak saat mengakses halaman pengaturan.
     */
    public function test_regular_user_cannot_access_or_update_organization_settings(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('settings.organization.edit'));

        $response->assertForbidden();

        $updateResponse = $this->actingAs($this->regularUser)
            ->put(route('settings.organization.update'), [
                'organization_name' => 'Organisasi Hacking',
            ]);

        $updateResponse->assertForbidden();
    }

    /**
     * 3. Administrator dapat memperbarui Nama Organisasi/Pemilik.
     */
    public function test_administrator_can_update_organization_name(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->put(route('settings.organization.update'), [
                'organization_name' => 'Forum Nelayan Aceh',
                'organization_type' => 'Forum',
            ]);

        $response->assertRedirect(route('settings.organization.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('application_settings', [
            'organization_name' => 'Forum Nelayan Aceh',
            'organization_type' => 'Forum',
        ]);
    }

    /**
     * 4. Uji beragam jenis organisasi yang harus dapat disimpan secara bebas (bukan enum).
     */
    public function test_various_organization_types_can_be_saved(): void
    {
        $types = [
            'Instansi',
            'Lembaga',
            'Forum',
            'LSM/NGO',
            'Yayasan',
            'Asosiasi',
            'Universitas',
            'Organisasi Masyarakat',
        ];

        foreach ($types as $type) {
            $response = $this->actingAs($this->adminUser)
                ->put(route('settings.organization.update'), [
                    'organization_name' => 'Entitas Bersama '.$type,
                    'organization_type' => $type,
                ]);

            $response->assertRedirect(route('settings.organization.edit'));

            $this->assertDatabaseHas('application_settings', [
                'organization_name' => 'Entitas Bersama '.$type,
                'organization_type' => $type,
            ]);
        }
    }

    /**
     * 5. Placeholder 'Instansi, Lembaga, Forum' tidak otomatis tersimpan jika input kosong.
     */
    public function test_placeholder_is_not_automatically_saved_as_database_value(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->put(route('settings.organization.update'), [
                'organization_name' => 'Organisasi Tanpa Jenis',
                'organization_type' => '',
            ]);

        $response->assertRedirect(route('settings.organization.edit'));

        $this->assertDatabaseHas('application_settings', [
            'organization_name' => 'Organisasi Tanpa Jenis',
            'organization_type' => null,
        ]);

        $this->assertDatabaseMissing('application_settings', [
            'organization_type' => 'Instansi, Lembaga, Forum',
        ]);
    }

    /**
     * 6. Validasi server-side: nama wajib, email/url valid jika diisi.
     */
    public function test_validation_rules_enforce_required_and_format_constraints(): void
    {
        // Nama kosong
        $response1 = $this->actingAs($this->adminUser)
            ->put(route('settings.organization.update'), [
                'organization_name' => '',
            ]);
        $response1->assertSessionHasErrors('organization_name');

        // Email invalid
        $response2 = $this->actingAs($this->adminUser)
            ->put(route('settings.organization.update'), [
                'organization_name' => 'Nama Valid',
                'email' => 'bukan-email-valid',
            ]);
        $response2->assertSessionHasErrors('email');

        // URL invalid
        $response3 = $this->actingAs($this->adminUser)
            ->put(route('settings.organization.update'), [
                'organization_name' => 'Nama Valid',
                'website' => 'bukan-url-valid',
            ]);
        $response3->assertSessionHasErrors('website');

        // File bukan gambar (misal file text/executable)
        Storage::fake('public');
        $fakeFile = UploadedFile::fake()->create('malicious.sh', 10, 'text/plain');
        $response4 = $this->actingAs($this->adminUser)
            ->put(route('settings.organization.update'), [
                'organization_name' => 'Nama Valid',
                'logo' => $fakeFile,
            ]);
        $response4->assertSessionHasErrors('logo');
    }

    /**
     * 7. Upload logo berhasil dan logo lama terhapus jika diganti.
     */
    public function test_logo_can_be_uploaded_and_replaced_safely(): void
    {
        Storage::fake('public');

        $logo1 = UploadedFile::fake()->image('logo1.png', 100, 100);

        $response = $this->actingAs($this->adminUser)
            ->put(route('settings.organization.update'), [
                'organization_name' => 'Organisasi Berlogo',
                'logo' => $logo1,
            ]);

        $response->assertRedirect(route('settings.organization.edit'));

        $setting = OrganizationSetting::getSettings();
        $this->assertNotNull($setting->logo_path);
        Storage::disk('public')->assertExists($setting->logo_path);
        $oldPath = $setting->logo_path;

        // Ganti dengan logo baru
        $logo2 = UploadedFile::fake()->image('logo2.webp', 120, 120);

        $this->actingAs($this->adminUser)
            ->put(route('settings.organization.update'), [
                'organization_name' => 'Organisasi Berlogo Baru',
                'logo' => $logo2,
            ]);

        $newSetting = OrganizationSetting::getSettings();
        $this->assertNotEquals($oldPath, $newSetting->logo_path);
        Storage::disk('public')->assertExists($newSetting->logo_path);
        Storage::disk('public')->assertMissing($oldPath);
    }

    /**
     * 8. Nilai yang disimpan tetap persist setelah reload dan cache invalidasi berjalan langsung.
     */
    public function test_settings_persist_and_reflect_dynamically(): void
    {
        $this->actingAs($this->adminUser)
            ->put(route('settings.organization.update'), [
                'organization_name' => 'Aliansi Maritim Nusantara',
                'organization_type' => 'Asosiasi',
                'address' => 'Jl. Pelabuhan Samudra No. 12',
                'website' => 'https://aliansimaritim.org',
                'email' => 'sekretariat@aliansimaritim.org',
                'phone' => '+62 651 888999',
            ]);

        // Muat ulang halaman edit
        $response = $this->actingAs($this->adminUser)
            ->get(route('settings.organization.edit'));

        $response->assertOk();
        $response->assertSee('Aliansi Maritim Nusantara');
        $response->assertSee('Asosiasi');
        $response->assertSee('Jl. Pelabuhan Samudra No. 12');
        $response->assertSee('https://aliansimaritim.org');
        $response->assertSee('sekretariat@aliansimaritim.org');
        $response->assertSee('+62 651 888999');

        // Buka dashboard dan periksa apakah nama organisasi tampil secara dinamis
        $dashboardResponse = $this->actingAs($this->adminUser)
            ->get(route('dashboard'));

        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Aliansi Maritim Nusantara');
    }

    /**
     * 9. Menjalankan alur verifikasi browser (Test A, Test B, Test C, dan restore default).
     */
    public function test_browser_verification_workflow_tests_a_b_c_and_restore_default(): void
    {
        // 1. Login administrator & akses halaman pengaturan
        $response = $this->actingAs($this->adminUser)
            ->get(route('settings.organization.edit'));
        $response->assertOk();
        $response->assertSee('IDENTITAS ORGANISASI');
        $response->assertSee('placeholder="Instansi, Lembaga, Forum"', false);

        // Test A: Nama: Dinas Kelautan dan Perikanan Aceh, Jenis: Instansi
        $this->actingAs($this->adminUser)
            ->put(route('settings.organization.update'), [
                'organization_name' => 'Dinas Kelautan dan Perikanan Aceh',
                'organization_type' => 'Instansi',
            ])->assertRedirect(route('settings.organization.edit'));

        $this->actingAs($this->adminUser)
            ->get(route('dashboard'))
            ->assertSee('Dinas Kelautan dan Perikanan Aceh');

        // Test B: Nama: Forum Nelayan Aceh, Jenis: Forum
        $this->actingAs($this->adminUser)
            ->put(route('settings.organization.update'), [
                'organization_name' => 'Forum Nelayan Aceh',
                'organization_type' => 'Forum',
            ])->assertRedirect(route('settings.organization.edit'));

        $this->actingAs($this->adminUser)
            ->get(route('dashboard'))
            ->assertSee('Forum Nelayan Aceh');

        // Test C: Nama: Organisasi XYZ, Jenis: LSM/NGO
        $this->actingAs($this->adminUser)
            ->put(route('settings.organization.update'), [
                'organization_name' => 'Organisasi XYZ',
                'organization_type' => 'LSM/NGO',
            ])->assertRedirect(route('settings.organization.edit'));

        $this->actingAs($this->adminUser)
            ->get(route('dashboard'))
            ->assertSee('Organisasi XYZ');

        // Restore Default: Nama: Dinas Kelautan dan Perikanan Aceh, Jenis: Instansi
        $this->actingAs($this->adminUser)
            ->put(route('settings.organization.update'), [
                'organization_name' => 'Dinas Kelautan dan Perikanan Aceh',
                'organization_type' => 'Instansi',
            ])->assertRedirect(route('settings.organization.edit'));

        $this->actingAs($this->adminUser)
            ->get(route('dashboard'))
            ->assertSee('Dinas Kelautan dan Perikanan Aceh');

        $this->assertDatabaseHas('application_settings', [
            'organization_name' => 'Dinas Kelautan dan Perikanan Aceh',
            'organization_type' => 'Instansi',
        ]);
    }
}

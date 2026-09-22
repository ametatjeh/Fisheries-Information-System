<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use App\Models\Fisherman;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Species;
use App\Models\User;
use App\Models\Vessel;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/reports');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_reports_index(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this
            ->actingAs($user)
            ->get('/reports');

        $response->assertOk();
        $response->assertSee('Pusat Laporan &amp; Ekspor Data Perikanan', false);
        $response->assertSee('Hasil Tangkapan');
    }

    public function test_reports_tabs_can_be_accessed(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        // 1. Efforts tab
        $responseEfforts = $this->actingAs($user)->get('/reports?type=efforts');
        $responseEfforts->assertOk();
        $responseEfforts->assertSee('Upaya &amp; Trip Tangkap', false);

        // 2. Landings tab
        $responseLandings = $this->actingAs($user)->get('/reports?type=landings');
        $responseLandings->assertOk();
        $responseLandings->assertSee('Pendaratan &amp; Omzet TPI', false);

        // 3. Sampling tab
        $responseSampling = $this->actingAs($user)->get('/reports?type=sampling');
        $responseSampling->assertOk();
        $responseSampling->assertSee('Biologi &amp; Sampling', false);

        // 4. Monthly tab
        $responseMonthly = $this->actingAs($user)->get('/reports?type=monthly&year=2026');
        $responseMonthly->assertOk();
        $responseMonthly->assertSee('Rekapitulasi Bulanan');
    }

    public function test_reports_can_be_exported_as_csv(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this
            ->actingAs($user)
            ->get('/reports/export?type=catches');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment;', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString('laporan_catches_', (string) $response->headers->get('content-disposition'));
    }

    public function test_reports_print_preview_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this
            ->actingAs($user)
            ->get('/reports/print?type=catches');

        $response->assertOk();
        $response->assertDontSee('Kementerian Kelautan dan Perikanan');
        $response->assertDontSee('Kepala Pelabuhan / Satuan Kerja');
        $response->assertSee('Petugas');
        $response->assertSee('Laporan Hasil Tangkapan Ikan');
        $response->assertSee('Mode Pratinjau Dokumen Cetak');
    }

    public function test_catches_report_displays_species_name(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $species = Species::create([
            'scientific_name' => 'Katsuwonus pelamis',
            'local_name_id' => 'Cakalang Spesial',
            'fao_code' => 'SKJ',
        ]);

        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::create(['province_id' => $province->id, 'name' => 'Banda Aceh', 'code' => '1171']);
        $site = LandingSite::create([
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'name' => 'PPS Lampulo',
            'code' => 'LMP',
            'is_active' => true,
        ]);

        $captain = Fisherman::create([
            'nik' => '1101011010850001',
            'name' => 'Panglima Laot',
            'gender' => 'L',
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'fisher_type' => 'nahkoda_jurumudi',
            'is_active' => true,
        ]);

        $vessel = Vessel::create([
            'name' => 'KM Test 01',
            'registration_number' => 'REG-TEST-001',
            'is_active' => true,
        ]);

        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-TEST-001',
            'vessel_id' => $vessel->id,
            'captain_id' => $captain->id,
            'departure_site_id' => $site->id,
            'landing_site_id' => $site->id,
            'departure_date' => now()->subDays(2),
        ]);

        FishCatch::create([
            'fishing_trip_id' => $trip->id,
            'fish_species_id' => $species->id,
            'weight_kg' => 125.5,
            'fish_count' => 50,
            'catch_status' => 'target',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/reports?type=catches');

        $response->assertOk();
        $response->assertSee('Cakalang Spesial');
        $response->assertSee('Katsuwonus pelamis');

        $responsePrint = $this
            ->actingAs($user)
            ->get('/reports/print?type=catches');

        $responsePrint->assertOk();
        $responsePrint->assertSee('Cakalang Spesial');
    }

    public function test_production_statistics_and_summary_reports_can_be_accessed(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        // 1. Production report tab
        $responseProd = $this->actingAs($user)->get('/reports?type=production');
        $responseProd->assertOk();
        $responseProd->assertSee('Produksi Terpadu');

        // 2. Statistics report tab
        $responseStat = $this->actingAs($user)->get('/reports?type=statistics');
        $responseStat->assertOk();
        $responseStat->assertSee('Statistik &amp; CPUE', false);

        // 3. Summary report tab
        $responseSum = $this->actingAs($user)->get('/reports?type=summary');
        $responseSum->assertOk();
        $responseSum->assertSee('Ringkasan Eksekutif');
        $responseSum->assertSee('Total Fishing Trips');
    }

    public function test_production_statistics_and_summary_can_be_exported_as_csv(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        foreach (['production', 'statistics', 'summary'] as $type) {
            $response = $this->actingAs($user)->get("/reports/export?type={$type}");
            $response->assertOk();
            $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
            $this->assertStringContainsString("laporan_{$type}_", (string) $response->headers->get('content-disposition'));
        }
    }

    public function test_production_statistics_and_summary_print_previews(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        foreach (['production', 'statistics', 'summary'] as $type) {
            $response = $this->actingAs($user)->get("/reports/print?type={$type}");
            $response->assertOk();
            $response->assertSee('Mode Pratinjau Dokumen Cetak');
        }
    }
}

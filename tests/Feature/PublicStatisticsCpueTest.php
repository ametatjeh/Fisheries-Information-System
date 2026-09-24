<?php

namespace Tests\Feature;

use App\Services\AdvancedStatisticService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicStatisticsCpueTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Connect to actual database 'sistem_perikanan' for audit reconciliation tests
        try {
            config([
                'database.default' => 'mysql',
                'database.connections.mysql.database' => 'sistem_perikanan',
            ]);
            DB::purge();
            DB::connection('mysql')->getPdo();
        } catch (\Throwable) {
            // Fallback if mysql not available
        }
    }

    /**
     * Test 1: GET /statistik (Default UX)
     * Expected:
     * - HTTP 200
     * - Menampilkan "FILTER STATISTIK"
     * - Menampilkan tombol "Tampilkan Statistik"
     * - Menampilkan instruksi filter
     * - TIDAK menampilkan KPI statistik
     * - TIDAK menampilkan chart statistik
     * - TIDAK menampilkan hasil CPUE
     */
    public function test_1_initial_get_statistik_shows_filter_only_and_no_kpi_charts(): void
    {
        $response = $this->get('/statistik');

        $response->assertOk();
        $response->assertSee('STATISTIK PERIKANAN ACEH');
        $response->assertSee('Visualisasi dan ringkasan data perikanan berdasarkan periode, wilayah, alat tangkap, dan spesies.');
        $response->assertSee('FILTER STATISTIK');
        $response->assertSee('Tampilkan Statistik');

        // Card DATA DEMO hanya tampil setelah statistik ditampilkan
        $response->assertDontSee('DATA DEMO');

        // HARUS TIDAK menampilkan card intro Eksplorasi Statistik & CPUE Perikanan
        $response->assertDontSee('Eksplorasi Statistik & CPUE Perikanan');
        $response->assertDontSee('Pilih filter statistik kemudian klik');
        $response->assertDontSee('Tangkapan (Catch)');
        $response->assertDontSee('Upaya (Effort)');
        $response->assertDontSee('Catch Per Unit Effort (CPUE)');
        $response->assertDontSee('Pemetaan Spasial GIS');

        // TIDAK menampilkan KPI sebelum submit
        $response->assertDontSee('Total Catch');
        $response->assertDontSee('Total Fishing Trip');
        $response->assertDontSee('Total Effort');
        $response->assertDontSee('Indeks CPUE');
        $response->assertDontSee('TABEL STATISTIK PERIKANAN ACEH');
        $response->assertDontSee('cdn.jsdelivr.net/npm/chart.js');
        $response->assertDontSee('maplibre-gl.js');
    }

    /**
     * Test 2: GET /statistik?tahun=2026
     * Expected:
     * - HTTP 200
     * - Filter tahun = 2026
     * - Card DATA DEMO tampil setelah statistik ditampilkan (Glassmorphic)
     * - Card intro TIDAK tampil
     * - Dashboard tampil
     * - Data catch tampil
     * - Data effort tampil
     * - CPUE tampil
     */
    public function test_2_get_statistik_with_submitted_filter_shows_dashboard(): void
    {
        $response = $this->get('/statistik?tahun=2026');

        $response->assertOk();
        $response->assertSee('FILTER STATISTIK');
        $response->assertSee('DATA DEMO:');
        $response->assertSee('Data yang ditampilkan pada halaman statistik ini merupakan data demo/contoh untuk membantu pengguna memahami tampilan statistik.');
        $response->assertSee('bg-amber-500/10');
        $response->assertDontSee('Eksplorasi Statistik & CPUE Perikanan');
        $response->assertSee('Total Catch');
        $response->assertSee('Total Fishing Trip');
        $response->assertSee('Total Effort');
        $response->assertSee('Indeks CPUE');

        // Data metric baselines
        $response->assertSee('29.090,00', false);
        $response->assertSee('471,5', false);
        $response->assertSee('61,70', false);
        $response->assertSee('646,44', false);

        // Assets and chart elements rendered
        $response->assertSee('cdn.jsdelivr.net/npm/chart.js');
        $response->assertSee('maplibre-gl.js');
        $response->assertSee('TABEL STATISTIK PERIKANAN ACEH');
    }

    /**
     * Test 3: GET /statistik dengan kombinasi filter
     * Pastikan hasil berubah sesuai filter yang dikirim.
     */
    public function test_3_get_statistik_with_filter_combinations_updates_data(): void
    {
        // 1. Tahun + Alat Tangkap (Pukat Cincin Satu Kapal = ID 1)
        $resGear = $this->get('/statistik?tahun=2026&fishing_gear_id=1');
        $resGear->assertOk();
        $resGear->assertSee('15.675,00', false);
        $resGear->assertSee('134,8', false);
        $resGear->assertSee('116,33', false);

        // 2. Tahun + Spesies (Cakalang = ID 1)
        $resSpecies = $this->get('/statistik?tahun=2026&species_id=1');
        $resSpecies->assertOk();
        $resSpecies->assertSee('9.387,00', false);
        $resSpecies->assertSee('146,3', false);
        $resSpecies->assertSee('64,18', false);

        // 3. Tahun + WPP-RI (WPP 571 = ID 1)
        $resWpp = $this->get('/statistik?tahun=2026&wppnri_id=1');
        $resWpp->assertOk();
        $resWpp->assertSee('7.017,00', false);
        $resWpp->assertSee('231,0', false);
        $resWpp->assertSee('30,38', false);

        // 4. Gear + Species (Gear 1 + Species 1)
        $resGearSpecies = $this->get('/statistik?fishing_gear_id=1&species_id=1');
        $resGearSpecies->assertOk();
        $resGearSpecies->assertSee('7.850,00', false);
        $resGearSpecies->assertSee('8,8', false);
        $resGearSpecies->assertSee('897,14', false);
    }

    /**
     * Test 4: Filter tanpa data (Empty result set)
     * Expected:
     * - HTTP 200
     * - Empty state alert "Tidak ada data statistik untuk kombinasi filter yang dipilih."
     * - Tombol "Ubah Filter"
     * - TIDAK menampilkan data palsu / KPI cards angka 0
     */
    public function test_4_empty_filter_result_handling_shows_empty_state_and_no_fake_zeros(): void
    {
        $response = $this->get('/statistik?tahun=1999');

        $response->assertOk();
        $response->assertSee('Tidak ada data statistik untuk kombinasi filter yang dipilih.');
        $response->assertSee('Ubah Filter');

        // Jangan menampilkan KPI cards seolah-olah ada data
        $response->assertDontSee('Total Catch');
        $response->assertDontSee('Total Fishing Trip');
        $response->assertDontSee('Indeks CPUE');
        $response->assertDontSee('cdn.jsdelivr.net/npm/chart.js');
    }

    /**
     * Test 5: Validasi formula CPUE:
     * SUM(Catch kg) / SUM(Operating Hours)
     * Sesuai hasil rekonsiliasi database.
     */
    public function test_5_cpue_hour_formula_ratio_of_sums_validation(): void
    {
        /** @var AdvancedStatisticService $service */
        $service = app(AdvancedStatisticService::class);
        $data = $service->getPublicCpueDashboardData([]);

        $dbTotalCatch = (float) DB::table('catches')->sum('weight_kg');
        $dbTotalEffort = (float) DB::table('fishing_efforts')->sum('duration_hours');

        $this->assertEquals($dbTotalCatch, $data['catch_weight']);
        $this->assertEquals($dbTotalEffort, $data['effort_hours']);

        $expectedCpueHour = round($dbTotalCatch / $dbTotalEffort, 2);
        $this->assertEquals($expectedCpueHour, $data['cpue_kg_per_hour']);
        $this->assertEquals(61.70, $data['cpue_kg_per_hour']);

        // Verifikasi pada level Gear: SUM(catch) / SUM(effort)
        foreach ($data['gear_cpue_table'] as $gearRow) {
            if ($gearRow['effort_hours'] > 0) {
                $calcCpueHour = round($gearRow['catch_kg'] / $gearRow['effort_hours'], 2);
                $this->assertEquals($calcCpueHour, $gearRow['cpue_hour']);
            }
        }
    }

    /**
     * Test 6: Validasi formula CPUE trip:
     * SUM(Catch kg) / COUNT(DISTINCT Fishing Trip)
     */
    public function test_6_cpue_trip_formula_ratio_of_sums_validation(): void
    {
        /** @var AdvancedStatisticService $service */
        $service = app(AdvancedStatisticService::class);
        $data = $service->getPublicCpueDashboardData([]);

        $dbTotalCatch = (float) DB::table('catches')->sum('weight_kg');
        $dbDistinctTrips = (int) DB::table('fishing_trips')->distinct()->count('id');

        $this->assertEquals(45, $dbDistinctTrips);
        $this->assertEquals($dbDistinctTrips, $data['trip_count']);

        $expectedCpueTrip = round($dbTotalCatch / $dbDistinctTrips, 2);
        $this->assertEquals($expectedCpueTrip, $data['cpue_kg_per_trip']);
        $this->assertEquals(646.44, $data['cpue_kg_per_trip']);
    }

    /**
     * Test 7: Fan-out test:
     * Skenario dimana 1 effort memiliki 3 catch records.
     * Pastikan effort duration hanya dihitung satu kali, tidak terduplikasi (fan-out).
     */
    public function test_7_fan_out_prevention_on_multi_catch_effort(): void
    {
        // Effort ID 1 memiliki 3 catch records
        $effort = DB::table('fishing_efforts')->where('id', 1)->first();
        $this->assertNotNull($effort);

        $catchCount = DB::table('catches')->where('fishing_effort_id', 1)->count();
        $this->assertEquals(3, $catchCount, 'Effort ID 1 harus memiliki tepat 3 catch records.');

        // Metode A (Naive JOIN fan-out): 2.75 jam * 3 catch rows = 8.25 jam
        $naiveJoinEffort = (float) DB::table('catches')
            ->join('fishing_efforts', 'catches.fishing_effort_id', '=', 'fishing_efforts.id')
            ->where('fishing_efforts.id', 1)
            ->sum('fishing_efforts.duration_hours');
        $this->assertEquals(8.25, $naiveJoinEffort, 'Naive JOIN menghasilkan fan-out 8.25 jam.');

        // Metode B (Clean de-duplicated effort): 2.75 jam
        $cleanEffort = (float) DB::table('fishing_efforts')
            ->where('id', 1)
            ->sum('duration_hours');
        $this->assertEquals(2.75, $cleanEffort, 'Actual clean effort harus tepat 2.75 jam.');

        // Global baseline fan-out audit
        $globalNaiveEffort = (float) DB::table('catches')
            ->join('fishing_efforts', 'catches.fishing_effort_id', '=', 'fishing_efforts.id')
            ->sum('fishing_efforts.duration_hours');
        $globalCleanEffort = (float) DB::table('fishing_efforts')->sum('duration_hours');

        $this->assertEquals(1338.25, $globalNaiveEffort);
        $this->assertEquals(471.50, $globalCleanEffort);

        // Service harus menghasilkan nilai de-duplicated tanpa fan-out
        /** @var AdvancedStatisticService $service */
        $service = app(AdvancedStatisticService::class);
        $data = $service->getPublicCpueDashboardData([]);
        $this->assertEquals(471.50, $data['effort_hours'], 'Service harus bebas dari fan-out double counting.');
    }

    /**
     * Rekonsiliasi angka awal terhadap audit baseline Tahap 2:
     * 45 trip, 62 setting, 169 catch rows, 29.090 kg (29.09 ton), 471.5 jam effort.
     */
    public function test_baseline_dataset_reconciliation_against_audit(): void
    {
        /** @var AdvancedStatisticService $service */
        $service = app(AdvancedStatisticService::class);
        $data = $service->getPublicCpueDashboardData([]);

        $this->assertEquals(45, $data['trip_count'], 'Total trip harus 45 sesuai audit.');
        $this->assertEquals(62, $data['setting_count'], 'Total setting harus 62 sesuai audit.');
        $this->assertEquals(29090.0, $data['catch_weight'], 'Total catch harus 29.090 kg sesuai audit.');
        $this->assertEquals(29.09, $data['catch_ton'], 'Total catch ton harus 29.09 ton.');
        $this->assertEquals(471.5, $data['effort_hours'], 'Total effort harus 471.5 jam.');

        // CPUE Hour
        $this->assertEquals(61.70, $data['cpue_kg_per_hour']);
        // CPUE Trip
        $this->assertEquals(646.44, $data['cpue_kg_per_trip']);

        // ISSCFG Fishing Gears count: 4 active gears
        $this->assertCount(4, $data['gear_cpue_table']);
        // Species count: 10 active species
        $this->assertCount(10, $data['species_cpue_table']);
    }

    /**
     * Keamanan pembagian dengan nol (Zero effort safe handling):
     * Tidak terjadi division by zero error, CPUE bernilai null jika effort kosong.
     */
    public function test_zero_effort_division_by_zero_safety(): void
    {
        /** @var AdvancedStatisticService $service */
        $service = app(AdvancedStatisticService::class);

        // Filter yang tidak menghasilkan record data
        $data = $service->getPublicCpueDashboardData(['tahun' => 1999]);

        $this->assertNull($data['cpue_kg_per_hour'], 'CPUE per jam harus null jika effort kosong.');
        $this->assertNull($data['cpue_kg_per_trip'], 'CPUE per trip harus null jika trip kosong.');
        $this->assertEquals(0, $data['catch_weight']);
        $this->assertEquals(0, $data['effort_hours']);
        $this->assertFalse($data['has_data']);
    }

    /**
     * Entry point pada Homepage:
     * Label: "Statistik Perikanan"
     * Deskripsi: "Lihat statistik tangkapan, effort, dan CPUE perikanan Aceh."
     * Link: "/statistik"
     */
    public function test_homepage_has_statistik_entry_point(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        // Navigation has STATISTIK entry point
        $response->assertSee('STATISTIK');
        // Card on home page is hidden per user request
        $response->assertDontSee('Tata Kelola & Analitik Terpadu Sumber Daya Laut Aceh');
        $response->assertDontSee('Lihat statistik tangkapan, effort, dan CPUE perikanan Aceh.');
    }
}

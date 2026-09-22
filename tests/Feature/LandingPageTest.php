<?php

namespace Tests\Feature;

use App\Http\Controllers\LandingPageController;
use Illuminate\Http\Request;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_can_be_rendered(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('IKAN KECIL');
        $response->assertSee('HOME');
        $response->assertSee('CHART');
        $response->assertSee('DATA FLOW');
        $response->assertDontSee('setMenu(\'file\')');
        $response->assertSee('STATISTIK');
        $response->assertSee('https://nagakecil.site/');
        $response->assertSee('nagakecil');
        $response->assertSee('All Rights Reserved');
        $response->assertDontSee('Hak cipta dilindungi undang-undang');
        $response->assertDontSee('Tata Kelola Data Perikanan Modern Berbasis Standar FAO Internasional');
        $response->assertDontSee('Empat Pilar Pengelolaan Data');
        $response->assertDontSee('Standarisasi FAO');
    }

    public function test_workflow_page_renders_complete_glassmorphism_flow(): void
    {
        $response = $this->get('/workflow');

        $response->assertOk();
        $response->assertDontSee('Sistem Alur Kerja Terintegrasi');
        $response->assertSee('TAHAP 01');
        $response->assertSee('MASTER DATA');
        $response->assertSee('TAHAP 02');
        $response->assertSee('DATA COLLECTION');
        $response->assertSee('TAHAP 03');
        $response->assertSee('REPORTING');
        $response->assertSee('TAHAP AKHIR (04)');
        $response->assertSee('DASHBOARD & GIS');
        $response->assertSee('Fishing Ground');
        $response->assertSee('Logbook');
    }

    public function test_data_flow_page_can_be_rendered(): void
    {
        $response = $this->get('/data-flow');

        $response->assertOk();
        $response->assertSee('DATA FLOW');
        $response->assertSee('Perekaman Lapangan');
        $response->assertDontSee('Pipeline & Siklus Hidup Data');
        $response->assertDontSee('Alur Aliran Data (Data Flow Pipeline)');
        $response->assertSee('Dokumen Rujukan di Aplikasi Ini');
        $response->assertSee('FAO CWP ISSCFG Annex M');
        $response->assertSee('FAO ASFIS List of Species');
    }

    public function test_landing_page_does_not_render_gis_map_and_links_to_dashboard_gis(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        // Public Home must NOT render map elements
        $response->assertDontSee('id="home-map-preview"', false);
        $response->assertDontSee('id="explorer-map-canvas"', false);
        $response->assertDontSee('api/gis/data', false);
        // But provides operational overview of geographic scope and links to internal GIS
        $response->assertSee('CAKUPAN WILAYAH PERIKANAN ACEH');
        $response->assertSee('Peta Terpadu');
        $response->assertSee(route('dashboard.gis'));
    }

    public function test_map_route_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/map');

        $response->assertRedirect('/login');
    }

    public function test_file_page_returns_not_found(): void
    {
        $response = $this->get('/file');

        $response->assertNotFound();
    }

    public function test_statistik_page_can_be_rendered(): void
    {
        $response = $this->get('/statistik');

        $response->assertOk();
        $response->assertDontSee('Ringkasan Statistik Perikanan');
        $response->assertDontSee('Distribusi Kategori Alat Tangkap');
        $response->assertDontSee('Total Landing / Produksi (Kg)');
        $response->assertSee('Total Catch / Produksi Tangkapan (Kg)');
        $response->assertSee('Frekuensi Panjang Ikan');
        $response->assertSee('Catch berdasarkan Fishing Gear');
        $response->assertSee('Catch berdasarkan WPP/Wilayah');
        $response->assertSee('Fishing Ground / Fishing Effort Location');
        $response->assertSee('mapFishingGround');
        $response->assertDontSee('Illuminate\Support\Collection');
        $response->assertDontSee('__PHP_Incomplete_Class');
    }

    public function test_statistik_charts_5_to_8_data_contract_and_calculations(): void
    {
        $controller = app()->make(LandingPageController::class);
        $result = $controller->getStatistikData(new Request);
        $stats = $result['stats'];

        // Chart 5: Length Frequency
        $this->assertArrayHasKey('length_frequency', $stats);
        $this->assertArrayHasKey('labels', $stats['length_frequency']);
        $this->assertArrayHasKey('data', $stats['length_frequency']);
        $this->assertEquals(
            $stats['length_frequency']['valid_count'],
            array_sum($stats['length_frequency']['data'])
        );

        // Chart 6: Catch by Gear
        $this->assertArrayHasKey('catch_by_gear', $stats);
        $this->assertArrayHasKey('labels', $stats['catch_by_gear']);
        $this->assertArrayHasKey('data', $stats['catch_by_gear']);
        $this->assertArrayHasKey('items', $stats['catch_by_gear']);

        // Chart 7: Catch by WPP
        $this->assertArrayHasKey('catch_by_wpp', $stats);
        $this->assertArrayHasKey('labels', $stats['catch_by_wpp']);
        $this->assertArrayHasKey('data', $stats['catch_by_wpp']);
        $this->assertArrayHasKey('items', $stats['catch_by_wpp']);

        // Chart 8: GIS Map
        $this->assertArrayHasKey('fishing_ground', $stats);
        $this->assertArrayHasKey('points', $stats['fishing_ground']);
        $this->assertArrayHasKey('fishing_locations', $stats);
    }

    public function test_statistik_filter_variations(): void
    {
        // Test with year filter
        $responseYear = $this->get('/statistik?year=2026');
        $responseYear->assertOk();

        // Test with year and month filter
        $responseMonth = $this->get('/statistik?year=2026&month=3');
        $responseMonth->assertOk();

        // Test with WPP filter
        $responseWpp = $this->get('/statistik?year=2026&wppnri_id=1');
        $responseWpp->assertOk();

        // Test with Gear filter
        $responseGear = $this->get('/statistik?year=2026&fishing_gear_id=1');
        $responseGear->assertOk();

        // Test with Species filter
        $responseSpecies = $this->get('/statistik?year=2026&species=1');
        $responseSpecies->assertOk();

        // Test with Family filter
        $responseFamily = $this->get('/statistik?family=SCOMBRIDAE');
        $responseFamily->assertOk();
    }

    public function test_statistik_cpue_and_catch_reconciliation(): void
    {
        $controller = app()->make(LandingPageController::class);
        $result = $controller->getStatistikData(new Request(['year' => 2026]));
        $stats = $result['stats'];

        // Reconciliation: Species catch sum equals Gear catch sum when no specific gear/species filter
        $speciesTotal = array_sum($stats['species_catch']['data']);
        $gearTotal = array_sum($stats['catch_by_gear']['data']);
        $this->assertEquals($speciesTotal, $gearTotal);

        // CPUE trend is calculated and not negative
        foreach ($stats['cpue_trend']['data'] as $cpue) {
            $this->assertGreaterThanOrEqual(0, $cpue);
        }

        // Length frequency total equals valid count
        $this->assertEquals(
            $stats['length_frequency']['valid_count'],
            array_sum($stats['length_frequency']['data'])
        );
    }

    public function test_statistik_ux_kpi_units_and_interpretation(): void
    {
        $response = $this->get('/statistik');

        $response->assertOk();
        // Official KPI Labels and Units
        $response->assertSee('Total Fishing Trip');
        $response->assertSee('Trip');
        $response->assertSee('Total Vessel');
        $response->assertSee('Kapal');
        $response->assertSee('Total Catch');
        $response->assertSee('kg');
        $response->assertSee('Total Species');
        $response->assertSee('Spesies');

        // Verify Total Landing is not used as KPI or Chart 1 label
        $response->assertDontSee('Total Landing / Produksi (Kg)');

        // Interpretation badges and explanatory notes
        $response->assertSee('departure_date');
        $response->assertSee('Panduan Interpretasi & Batasan Data Statistik', false);
        $response->assertSee('CPUE Trend (Kg/Jam)');
        $response->assertSee('Lokasi Fishing Effort');

        // Empty state containers for all charts
        $response->assertSee('emptyLandingTrend');
        $response->assertSee('emptySpeciesCatch');
        $response->assertSee('emptyCatchComp');
        $response->assertSee('emptyCpueTrend');
        $response->assertSee('emptyLengthFreq');
        $response->assertSee('emptyCatchGear');
        $response->assertSee('emptyCatchWpp');
        $response->assertSee('emptyMapFishingGround');
    }

    public function test_statistik_zero_data_handling(): void
    {
        // Filter that yields 0 records
        $response = $this->get('/statistik?year=1990&month=1');

        $response->assertOk();
        $response->assertSee('0');
        $response->assertDontSee('NaN');
        $response->assertDontSee('Infinity');
        $response->assertDontSee('undefined');
    }
}

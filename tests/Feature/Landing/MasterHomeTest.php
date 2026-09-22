<?php

namespace Tests\Feature\Landing;

use App\Http\Controllers\LandingPageController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_home_page_renders_operational_overview(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('OPERATIONAL OVERVIEW');
        $response->assertSee('Status Sistem:');
        $response->assertSee('Periode Data:');
        $response->assertSee('Statistik Cepat Sistem');
        $response->assertSee('Aksi Cepat Sistem:');
        $response->assertSee('Aktivitas Operasional Terkini');
        $response->assertSee('Perlu Perhatian');
        $response->assertSee('CAKUPAN WILAYAH PERIKANAN ACEH');
        $response->assertSee('Peta Terpadu');
        $response->assertDontSee('id="home-map-preview"', false);
        $response->assertSee('Ringkasan Statistik Komoditas');
        $response->assertSee('Alur Rantai & Keturunan Data');
        $response->assertSee('Matriks Kualitas & Kelengkapan Data');
        $response->assertSee('Modul Utama Sistem Informasi Perikanan');
        $response->assertSee('Kamus & Definisi Entitas Perikanan');
        $response->assertSee('Sumber Data, Metodologi & Batasan Sistem');
    }

    public function test_global_search_endpoint_returns_json_results(): void
    {
        // 1. Empty or short query
        $responseShort = $this->getJson('/home/search?q=a');
        $responseShort->assertOk()
            ->assertJson([
                'success' => true,
                'total' => 0,
                'data' => [],
            ]);

        // 2. Query matching vessels/fishermen/species/landing
        $responseSearch = $this->getJson('/home/search?q=aceh');
        $responseSearch->assertOk()
            ->assertJsonStructure([
                'success',
                'query',
                'total',
                'data' => [
                    '*' => ['category', 'icon', 'title', 'subtitle', 'badge', 'url'],
                ],
            ]);
    }

    public function test_operational_overview_data_contract(): void
    {
        $controller = app()->make(LandingPageController::class);
        $overviewData = $controller->getOperationalOverviewData();

        $this->assertArrayHasKey('systemStatus', $overviewData);
        $this->assertArrayHasKey('dataPeriod', $overviewData);
        $this->assertArrayHasKey('quickStats', $overviewData);
        $this->assertArrayHasKey('recentActivities', $overviewData);
        $this->assertArrayHasKey('attentionItems', $overviewData);
        $this->assertArrayHasKey('statsSummary', $overviewData);
        $this->assertArrayHasKey('dataQuality', $overviewData);

        // Ensure quick stats contains positive numbers
        $this->assertGreaterThanOrEqual(0, $overviewData['quickStats']['fishers']);
        $this->assertGreaterThanOrEqual(0, $overviewData['quickStats']['vessels']);
        $this->assertGreaterThanOrEqual(0, $overviewData['quickStats']['trips']);
    }
}

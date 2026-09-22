<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\Rzwp3kZoneController;
use App\Models\Rzwp3kZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Rzwp3kMapLibreApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_rzwp3k_zones_endpoint_returns_200_and_feature_collection(): void
    {
        $response = $this->getJson(route('api.rzwp3k.zones'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/geo+json');
        $response->assertJson([
            'type' => 'FeatureCollection',
            'features' => [],
            'metadata' => [
                'legal_basis' => 'Qanun Aceh Nomor 1 Tahun 2020',
                'authority' => 'Pemerintah Aceh / DKP Aceh',
                'target_crs' => 'EPSG:4326',
            ],
        ]);
    }

    public function test_api_rzwp3k_zones_excludes_null_geometry_records(): void
    {
        // 1. Zone without geometry (default verified state)
        Rzwp3kZone::create([
            'code' => 'KPU-PT-NULL',
            'parent_code' => 'KPU',
            'name' => 'Zona Perikanan Tangkap Null',
            'zone_type' => 'KPU',
            'subzone_type' => 'KPU-PT',
            'source' => 'DKP Aceh',
            'source_document' => 'Qanun 1/2020',
            'legal_basis' => 'Qanun Aceh 1/2020',
            'valid_from' => '2020-01-13',
            'valid_until' => '2040-01-13',
            'status' => 'legal_active',
            'geometry' => null,
        ]);

        // 2. Zone with valid geometry
        Rzwp3kZone::create([
            'code' => 'KPU-PT-VALID',
            'parent_code' => 'KPU',
            'name' => 'Zona Perikanan Tangkap Valid',
            'zone_type' => 'KPU',
            'subzone_type' => 'KPU-PT',
            'source' => 'DKP Aceh',
            'source_document' => 'Qanun 1/2020',
            'legal_basis' => 'Qanun Aceh 1/2020',
            'valid_from' => '2020-01-13',
            'valid_until' => '2040-01-13',
            'status' => 'legal_active',
            'area_ha' => 15000.50,
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [
                    [
                        [95.25, 5.80],
                        [95.35, 5.80],
                        [95.35, 5.90],
                        [95.25, 5.90],
                        [95.25, 5.80],
                    ],
                ],
            ],
        ]);

        Rzwp3kZoneController::clearCache();

        $response = $this->getJson(route('api.rzwp3k.zones'));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals(1, count($data['features']), 'Only record with geometry must be included in FeatureCollection');
        $this->assertEquals('KPU-PT-VALID', $data['features'][0]['properties']['code']);
        $this->assertEquals('Polygon', $data['features'][0]['geometry']['type']);
        $this->assertStringContainsString('zonasi spasial', $data['features'][0]['properties']['disclaimer']);
    }

    public function test_api_rzwp3k_zones_filter_by_zone_type(): void
    {
        // KPU zone
        Rzwp3kZone::create([
            'code' => 'KPU-PT-01',
            'name' => 'Zona KPU',
            'zone_type' => 'KPU',
            'subzone_type' => 'KPU-PT',
            'source' => 'DKP Aceh',
            'source_document' => 'Qanun 1/2020',
            'legal_basis' => 'Qanun Aceh 1/2020',
            'status' => 'legal_active',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [[[95.2, 5.8], [95.3, 5.8], [95.3, 5.9], [95.2, 5.9], [95.2, 5.8]]],
            ],
        ]);

        // KK zone
        Rzwp3kZone::create([
            'code' => 'KK-KKP-01',
            'name' => 'Zona Konservasi',
            'zone_type' => 'KK',
            'subzone_type' => 'KK-KKP',
            'source' => 'DKP Aceh',
            'source_document' => 'Qanun 1/2020',
            'legal_basis' => 'Qanun Aceh 1/2020',
            'status' => 'legal_active',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [[[95.4, 5.8], [95.5, 5.8], [95.5, 5.9], [95.4, 5.9], [95.4, 5.8]]],
            ],
        ]);

        Rzwp3kZoneController::clearCache();

        // Query KK only
        $response = $this->getJson(route('api.rzwp3k.zones', ['zone_type' => 'KK']));
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals(1, count($data['features']));
        $this->assertEquals('KK-KKP-01', $data['features'][0]['properties']['code']);
    }

    public function test_api_rzwp3k_zones_does_not_expose_secrets(): void
    {
        Rzwp3kZone::create([
            'code' => 'KPU-PT-SEC',
            'name' => 'Zona Sekuriti',
            'zone_type' => 'KPU',
            'subzone_type' => 'KPU-PT',
            'source' => 'DKP Aceh',
            'source_document' => 'Qanun 1/2020',
            'legal_basis' => 'Qanun Aceh 1/2020',
            'status' => 'legal_active',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [[[95.2, 5.8], [95.3, 5.8], [95.3, 5.9], [95.2, 5.9], [95.2, 5.8]]],
            ],
        ]);

        Rzwp3kZoneController::clearCache();

        $response = $this->getJson(route('api.rzwp3k.zones'));
        $content = $response->getContent();

        $this->assertStringNotContainsString('DB_PASSWORD', $content);
        $this->assertStringNotContainsString('GFW_API_KEY', $content);
        $this->assertStringNotContainsString('APP_KEY', $content);
        $this->assertStringNotContainsString('base_path', $content);
    }

    public function test_statistik_page_renders_rzwp3k_layer_toggle_and_maplibre_markup(): void
    {
        $response = $this->get('/statistik');

        $response->assertStatus(200);
        $response->assertSee('toggleRzwp3k');
        $response->assertSee('badgeCountRzwp3k');
        $response->assertSee('RZWP3K Aceh');
        $response->assertSee('rzwp3kEmptyNotice');
        $response->assertSee('source-rzwp3k');
        $response->assertSee('layer-rzwp3k-fill');
        $response->assertSee('layer-rzwp3k-line');
    }
}

<?php

namespace Tests\Feature\Gfw;

use App\Models\FishingGround;
use App\Models\Province;
use App\Models\Wppnri;
use App\Services\Gfw\GfwRegionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GfwRegionTest extends TestCase
{
    use RefreshDatabase;

    protected GfwRegionService $regionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->regionService = app(GfwRegionService::class);
    }

    public function test_regions_index_returns_list_of_supported_regions(): void
    {
        $response = $this->getJson('/api/gfw/regions');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'global_fishing_watch',
            ])
            ->assertJsonStructure([
                'success',
                'source',
                'total',
                'data' => [
                    '*' => [
                        'key',
                        'name',
                        'type',
                        'gfw_dataset',
                        'description',
                        'bounding_box',
                        'provenance_note',
                    ],
                ],
            ]);

        $this->assertGreaterThanOrEqual(4, $response->json('total'));
    }

    public function test_indonesia_region_returns_correct_eez_definition_and_query_params(): void
    {
        $response = $this->getJson('/api/gfw/regions/indonesia_eez');

        $response->assertStatus(200)
            ->assertJsonPath('data.key', 'indonesia_eez')
            ->assertJsonPath('data.type', 'eez')
            ->assertJsonPath('data.gfw_dataset', 'public-eez-areas')
            ->assertJsonPath('data.gfw_region_id', 'IDN')
            ->assertJsonPath('data.query_parameters.region.dataset', 'public-eez-areas')
            ->assertJsonPath('data.query_parameters.region.id', 'IDN')
            ->assertJsonPath('data.provenance.source', 'global_fishing_watch')
            ->assertJsonPath('data.provenance.observation_concept', 'vessel_activity_observed_within_geographic_bounds');
    }

    public function test_aceh_waters_region_returns_correct_geometry_and_disclaimer(): void
    {
        $response = $this->getJson('/api/gfw/regions/aceh_waters');

        $response->assertStatus(200)
            ->assertJsonPath('data.key', 'aceh_waters')
            ->assertJsonPath('data.type', 'maritime_zone')
            ->assertJsonPath('data.gfw_dataset', 'custom_geometry')
            ->assertJsonPath('data.bounding_box.0', 94.5)
            ->assertJsonPath('data.bounding_box.1', 1.8)
            ->assertJsonPath('data.bounding_box.2', 98.3)
            ->assertJsonPath('data.bounding_box.3', 6.2)
            ->assertJsonStructure([
                'data' => [
                    'polygon' => [
                        'type',
                        'coordinates',
                    ],
                    'provenance' => [
                        'administrative_disclaimer',
                        'observation_concept',
                    ],
                ],
            ]);

        // Verify the architectural distinction note is present
        $disclaimer = $response->json('data.provenance.administrative_disclaimer');
        $this->assertStringContainsString('tidak menentukan asal pangkalan', $disclaimer);
    }

    public function test_region_aliases_are_properly_resolved(): void
    {
        // 'indonesia' -> 'indonesia_eez'
        $resIdn = $this->getJson('/api/gfw/regions/indonesia');
        $resIdn->assertStatus(200)->assertJsonPath('data.key', 'indonesia_eez');

        // 'aceh' -> 'aceh_waters'
        $resAceh = $this->getJson('/api/gfw/regions/aceh');
        $resAceh->assertStatus(200)->assertJsonPath('data.key', 'aceh_waters');

        // '571' -> 'wppnri_571'
        $res571 = $this->getJson('/api/gfw/regions/571');
        $res571->assertStatus(200)->assertJsonPath('data.key', 'wppnri_571');
    }

    public function test_unknown_region_returns_404(): void
    {
        $response = $this->getJson('/api/gfw/regions/unknown_atlantis_zone');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_coordinate_validation_endpoint_with_valid_and_invalid_coordinates(): void
    {
        // Valid coordinate in Aceh (e.g. Banda Aceh coastal waters: 5.55N, 95.31E)
        $validRes = $this->postJson('/api/gfw/regions/validate', [
            'latitude' => 5.55,
            'longitude' => 95.31,
        ]);

        $validRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'valid' => true,
                'type' => 'coordinate_pair',
                'data' => [
                    'latitude' => 5.55,
                    'longitude' => 95.31,
                ],
            ]);

        // Invalid latitude (> 90)
        $invalidRes = $this->postJson('/api/gfw/regions/validate', [
            'latitude' => 105.0,
            'longitude' => 95.31,
        ]);

        $invalidRes->assertStatus(422)
            ->assertJson([
                'success' => false,
                'valid' => false,
            ]);
    }

    public function test_bounding_box_validation_endpoint(): void
    {
        // Valid Bounding Box
        $validRes = $this->postJson('/api/gfw/regions/validate', [
            'bounding_box' => [94.5, 1.8, 98.3, 6.2],
        ]);

        $validRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'valid' => true,
                'type' => 'bounding_box',
                'data' => [
                    'min_lon' => 94.5,
                    'min_lat' => 1.8,
                    'max_lon' => 98.3,
                    'max_lat' => 6.2,
                ],
            ]);

        // Invalid Bounding Box (min_lon > max_lon)
        $invalidRes = $this->postJson('/api/gfw/regions/validate', [
            'bounding_box' => [99.0, 1.8, 95.0, 6.2],
        ]);

        $invalidRes->assertStatus(422)
            ->assertJson([
                'success' => false,
                'valid' => false,
            ]);
    }

    public function test_geojson_polygon_validation_endpoint(): void
    {
        // Valid Polygon (closed loop, >= 4 points)
        $validPolygon = [
            'type' => 'Polygon',
            'coordinates' => [
                [
                    [95.0, 2.0],
                    [95.0, 6.0],
                    [98.0, 6.0],
                    [98.0, 2.0],
                    [95.0, 2.0],
                ],
            ],
        ];

        $validRes = $this->postJson('/api/gfw/regions/validate', [
            'geojson' => $validPolygon,
        ]);

        $validRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'valid' => true,
                'type' => 'geojson_polygon',
            ]);

        // Invalid Polygon (unclosed loop)
        $unclosedPolygon = [
            'type' => 'Polygon',
            'coordinates' => [
                [
                    [95.0, 2.0],
                    [95.0, 6.0],
                    [98.0, 6.0],
                    [98.0, 2.0], // missing closing point [95.0, 2.0]
                ],
            ],
        ];

        $invalidRes = $this->postJson('/api/gfw/regions/validate', [
            'geojson' => $unclosedPolygon,
        ]);

        $invalidRes->assertStatus(422)
            ->assertJson([
                'success' => false,
                'valid' => false,
            ]);
    }

    public function test_query_params_builder_with_date_range_and_provenance(): void
    {
        $response = $this->getJson('/api/gfw/regions/aceh_waters?start_date=2026-01-01&end_date=2026-01-31');

        $response->assertStatus(200)
            ->assertJsonPath('data.query_parameters.start_date', '2026-01-01')
            ->assertJsonPath('data.query_parameters.end_date', '2026-01-31')
            ->assertJsonPath('data.provenance.query_period.start_date', '2026-01-01')
            ->assertJsonPath('data.provenance.query_period.end_date', '2026-01-31');
    }

    public function test_geographic_filtering_preserves_local_tables_and_master_data(): void
    {
        // Seed standard local records
        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $wpp = Wppnri::create(['code' => '571', 'name' => 'Selat Malaka dan Laut Andaman', 'is_active' => true]);
        $ground = FishingGround::create([
            'code' => 'FG-LOCAL-01',
            'name' => 'Perairan Lokal Ujong Breuh',
            'wppnri_id' => $wpp->id,
            'is_active' => true,
        ]);

        // Perform GFW geographic operations
        $this->getJson('/api/gfw/regions');
        $this->getJson('/api/gfw/regions/indonesia_eez');
        $this->getJson('/api/gfw/regions/aceh_waters');

        // Assert local master tables are intact and unpolluted
        $this->assertDatabaseHas('provinces', ['code' => '11', 'name' => 'Aceh']);
        $this->assertDatabaseHas('wppnri', ['code' => '571']);
        $this->assertDatabaseHas('fishing_grounds', ['code' => 'FG-LOCAL-01']);
        $this->assertDatabaseCount('vessels', 0);
    }
}

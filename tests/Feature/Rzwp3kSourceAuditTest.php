<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use App\Models\FishingEffort;
use App\Models\FishingGround;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\Rzwp3kZone;
use App\Models\Vessel;
use App\Services\Rzwp3k\Rzwp3kGeoJsonValidator;
use App\Services\Rzwp3k\Rzwp3kSourceHealthCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Rzwp3kSourceAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_rzwp3k_config_registry_structure_is_valid(): void
    {
        $config = config('rzwp3k');

        $this->assertIsArray($config);
        $this->assertEquals('EPSG:4326', $config['target_crs']);
        $this->assertArrayHasKey('sources', $config);
        $this->assertArrayHasKey('official_qanun', $config['sources']);
        $this->assertArrayHasKey('dkp_aceh_geoportal', $config['sources']);
        $this->assertArrayHasKey('kkp_sigap_wms', $config['sources']);

        // Check official qanun
        $qanun = $config['sources']['official_qanun'];
        $this->assertEquals('Pemerintah Aceh / DPRA', $qanun['authority']);
        $this->assertEquals('Qanun Aceh Nomor 1 Tahun 2020', $qanun['legal_basis']);
        $this->assertTrue($qanun['verified']);
        $this->assertStringContainsString('jdih.acehprov.go.id', $qanun['dataset_url']);

        // Check unverified sources do not have fake URLs
        $dkp = $config['sources']['dkp_aceh_geoportal'];
        $this->assertFalse($dkp['verified']);
        $this->assertNull($dkp['dataset_url']);
        $this->assertNull($dkp['service_url']);
    }

    public function test_rzwp3k_source_health_check_service_audits_sources(): void
    {
        $healthCheck = app(Rzwp3kSourceHealthCheck::class);
        $results = $healthCheck->auditAll();

        $this->assertArrayHasKey('official_qanun', $results);
        $this->assertArrayHasKey('dkp_aceh_geoportal', $results);
        $this->assertEquals('official_qanun', $results['official_qanun']['key']);
        $this->assertEquals('NO_ENDPOINT_ATTACHED', $results['dkp_aceh_geoportal']['status_label']);
    }

    public function test_rzwp3k_source_audit_command_executes_successfully(): void
    {
        $this->artisan('rzwp3k:source-audit')
            ->expectsOutputToContain('RZWP3K ACEH SPATIAL SOURCE AUDIT')
            ->expectsOutputToContain('Qanun Aceh Nomor 1 Tahun 2020')
            ->expectsOutputToContain('EPSG:4326')
            ->assertExitCode(0);
    }

    public function test_geojson_validator_validates_multipolygon_and_coordinate_crs_bounds(): void
    {
        $validator = app(Rzwp3kGeoJsonValidator::class);

        // Valid MultiPolygon
        $validMultiPolygon = [
            'type' => 'MultiPolygon',
            'coordinates' => [
                [
                    [
                        [95.25, 5.80],
                        [95.35, 5.80],
                        [95.35, 5.90],
                        [95.25, 5.90],
                        [95.25, 5.80],
                    ],
                ],
                [
                    [
                        [95.40, 5.80],
                        [95.50, 5.80],
                        [95.50, 5.90],
                        [95.40, 5.90],
                        [95.40, 5.80],
                    ],
                ],
            ],
        ];

        $result = $validator->validate($validMultiPolygon);
        $this->assertTrue($result['is_valid']);
        $this->assertEquals(1, $result['valid_count']);

        // Invalid Coordinate Bounds (e.g. Longitude > 180 or Latitude > 90)
        $outOfBoundsGeometry = [
            'type' => 'Polygon',
            'coordinates' => [
                [
                    [195.0, 5.5],
                    [195.1, 5.5],
                    [195.1, 5.6],
                    [195.0, 5.6],
                    [195.0, 5.5],
                ],
            ],
        ];

        $invalidResult = $validator->validate($outOfBoundsGeometry);
        $this->assertFalse($invalidResult['is_valid']);
        $this->assertNotEmpty($invalidResult['errors']);
    }

    public function test_catalog_matching_and_provenance_verification(): void
    {
        // Create catalog records in rzwp3k_zones
        Rzwp3kZone::create([
            'code' => 'KPU-PT-TEST',
            'parent_code' => 'KPU',
            'name' => 'Zona Perikanan Tangkap Uji',
            'zone_type' => 'KPU',
            'subzone_type' => 'KPU-PT',
            'source' => 'Dinas Kelautan dan Perikanan Aceh',
            'source_document' => 'Qanun Aceh Nomor 1 Tahun 2020',
            'legal_basis' => 'Qanun Aceh 1/2020',
            'valid_from' => '2020-01-13',
            'valid_until' => '2040-01-13',
            'status' => 'legal_active',
            'metadata' => [
                'acquisition_date' => '2026-09-21',
                'authority' => 'Pemerintah Aceh',
                'crs' => 'EPSG:4326',
            ],
            'geometry' => null,
        ]);

        $zone = Rzwp3kZone::where('code', 'KPU-PT-TEST')->first();

        $this->assertNotNull($zone);
        $this->assertEquals('EPSG:4326', $zone->metadata['crs']);
        $this->assertEquals('Pemerintah Aceh', $zone->metadata['authority']);
        $this->assertNull($zone->geometry, 'Geometry must remain NULL until official verified GeoJSON is attached');
    }

    public function test_stage_18_3_does_not_modify_fisheries_or_gfw_domain(): void
    {
        $initialVessels = Vessel::count();
        $initialTrips = FishingTrip::count();
        $initialEfforts = FishingEffort::count();
        $initialCatches = FishCatch::count();
        $initialLandings = LandingSite::count();
        $initialGrounds = FishingGround::count();

        // Run source audit
        Artisan::call('rzwp3k:source-audit');

        $this->assertEquals($initialVessels, Vessel::count());
        $this->assertEquals($initialTrips, FishingTrip::count());
        $this->assertEquals($initialEfforts, FishingEffort::count());
        $this->assertEquals($initialCatches, FishCatch::count());
        $this->assertEquals($initialLandings, LandingSite::count());
        $this->assertEquals($initialGrounds, FishingGround::count());
    }
}

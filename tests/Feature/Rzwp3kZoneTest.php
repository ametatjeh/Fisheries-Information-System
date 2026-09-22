<?php

namespace Tests\Feature;

use App\Models\Rzwp3kZone;
use App\Services\Rzwp3k\Rzwp3kGeoJsonValidator;
use Database\Seeders\AcehProvinceSeeder;
use Database\Seeders\AcehRegencySeeder;
use Database\Seeders\Rzwp3kZoneSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Rzwp3kZoneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AcehProvinceSeeder::class);
        $this->seed(AcehRegencySeeder::class);
    }

    /**
     * Uji keberadaan tabel rzwp3k_zones dan operasi CRUD dasar model.
     */
    public function test_rzwp3k_zones_table_exists_and_can_create_record(): void
    {
        $zone = Rzwp3kZone::create([
            'code' => 'TEST-KPU-01',
            'parent_code' => 'KPU',
            'name' => 'Zona Uji Perikanan Tangkap',
            'zone_type' => 'KPU',
            'subzone_type' => 'KPU-PT',
            'description' => 'Zona uji coba integrasi',
            'area_ha' => 1250.50,
            'source' => 'DKP Aceh Test',
            'source_document' => 'Qanun Aceh No. 1 Tahun 2020',
            'legal_basis' => 'Qanun Aceh 1/2020',
            'valid_from' => '2020-01-13',
            'valid_until' => '2040-01-13',
            'status' => 'legal_active',
            'metadata' => ['test_key' => 'test_value'],
            'geometry' => null,
        ]);

        $this->assertDatabaseHas('rzwp3k_zones', [
            'code' => 'TEST-KPU-01',
            'name' => 'Zona Uji Perikanan Tangkap',
            'zone_type' => 'KPU',
        ]);

        $this->assertEquals(1250.50, (float) $zone->area_ha);
        $this->assertEquals(['test_key' => 'test_value'], $zone->metadata);
    }

    /**
     * Uji bahwa kolom code pada rzwp3k_zones memiliki unique constraint.
     */
    public function test_rzwp3k_zone_code_must_be_unique(): void
    {
        Rzwp3kZone::create([
            'code' => 'UNIQUE-CODE-01',
            'name' => 'Zona Unik 1',
            'zone_type' => 'KPU',
            'subzone_type' => 'KPU-PT',
            'source' => 'DKP Aceh',
            'source_document' => 'Qanun 1/2020',
            'legal_basis' => 'Qanun 1/2020',
        ]);

        $this->expectException(QueryException::class);

        Rzwp3kZone::create([
            'code' => 'UNIQUE-CODE-01',
            'name' => 'Zona Unik 2 (Duplikat)',
            'zone_type' => 'KK',
            'subzone_type' => 'KK-KKP',
            'source' => 'DKP Aceh',
            'source_document' => 'Qanun 1/2020',
            'legal_basis' => 'Qanun 1/2020',
        ]);
    }

    /**
     * Uji scopes dan GeoJSON Feature formatter pada model Rzwp3kZone.
     */
    public function test_rzwp3k_zone_scopes_and_geojson_formatter(): void
    {
        $sampleGeometry = [
            'type' => 'Polygon',
            'coordinates' => [
                [
                    [95.30, 5.50],
                    [95.40, 5.50],
                    [95.40, 5.60],
                    [95.30, 5.60],
                    [95.30, 5.50],
                ],
            ],
        ];

        $zone = Rzwp3kZone::create([
            'code' => 'TEST-KK-01',
            'parent_code' => 'KK',
            'name' => 'Zona Konservasi Uji',
            'zone_type' => 'KK',
            'subzone_type' => 'KK-KKP',
            'source' => 'DKP Aceh',
            'source_document' => 'Qanun 1/2020',
            'legal_basis' => 'Qanun 1/2020',
            'status' => 'legal_active',
            'geometry' => $sampleGeometry,
        ]);

        $kkZones = Rzwp3kZone::ofZoneType('KK')->get();
        $this->assertTrue($kkZones->contains('id', $zone->id));

        $withGeom = Rzwp3kZone::withGeometry()->get();
        $this->assertTrue($withGeom->contains('id', $zone->id));

        $feature = $zone->toGeoJsonFeature();
        $this->assertEquals('Feature', $feature['type']);
        $this->assertEquals($sampleGeometry, $feature['geometry']);
        $this->assertEquals('TEST-KK-01', $feature['properties']['code']);
    }

    /**
     * Uji seeder Rzwp3kZoneSeeder berhasil mengisi katalog zona resmi Qanun 1/2020.
     */
    public function test_rzwp3k_zone_seeder_loads_official_catalog(): void
    {
        $this->seed(Rzwp3kZoneSeeder::class);

        $this->assertDatabaseHas('rzwp3k_zones', [
            'code' => 'KPU-PT-01',
            'zone_type' => 'KPU',
            'subzone_type' => 'KPU-PT',
        ]);

        $this->assertDatabaseHas('rzwp3k_zones', [
            'code' => 'KK-KKP-01',
            'zone_type' => 'KK',
            'subzone_type' => 'KK-KKP',
        ]);

        $this->assertDatabaseHas('rzwp3k_zones', [
            'code' => 'AL-P-01',
            'zone_type' => 'AL',
            'subzone_type' => 'AL-P',
        ]);

        $this->assertDatabaseHas('rzwp3k_zones', [
            'code' => 'KSNT-PPKT-01',
            'zone_type' => 'KSNT',
            'subzone_type' => 'KSNT-PPKT',
        ]);

        // Verifikasi bahwa seluruh zona seeder memiliki geometry NULL (tanpa koordinat fiktif)
        $this->assertEquals(0, Rzwp3kZone::whereNotNull('geometry')->count());
    }

    /**
     * Uji GeoJSON Validator untuk format Polygon dan MultiPolygon valid.
     */
    public function test_geojson_validator_accepts_valid_polygon_and_multipolygon(): void
    {
        $validator = new Rzwp3kGeoJsonValidator;

        $validFeatureCollection = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => ['code' => 'GEO-01', 'name' => 'Polygon 1'],
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [
                            [
                                [95.10, 5.10],
                                [95.20, 5.10],
                                [95.20, 5.20],
                                [95.10, 5.20],
                                [95.10, 5.10],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'Feature',
                    'properties' => ['code' => 'GEO-02', 'name' => 'MultiPolygon 1'],
                    'geometry' => [
                        'type' => 'MultiPolygon',
                        'coordinates' => [
                            [
                                [
                                    [95.30, 5.30],
                                    [95.40, 5.30],
                                    [95.40, 5.40],
                                    [95.30, 5.40],
                                    [95.30, 5.30],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = $validator->validate($validFeatureCollection);

        $this->assertTrue($result['is_valid']);
        $this->assertEquals(2, $result['valid_count']);
        $this->assertEquals(0, $result['invalid_count']);
        $this->assertEmpty($result['errors']);
    }

    /**
     * Uji GeoJSON Validator menolak koordinat tidak valid dan linear ring yang tidak tertutup.
     */
    public function test_geojson_validator_rejects_invalid_geometry(): void
    {
        $validator = new Rzwp3kGeoJsonValidator;

        // 1. Unclosed polygon ring
        $unclosedFeature = [
            'type' => 'Feature',
            'properties' => [],
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [
                    [
                        [95.10, 5.10],
                        [95.20, 5.10],
                        [95.20, 5.20],
                        [95.15, 5.25], // Tidak kembali ke [95.10, 5.10]
                    ],
                ],
            ],
        ];
        $res1 = $validator->validate($unclosedFeature);
        $this->assertFalse($res1['is_valid']);
        $this->assertGreaterThan(0, $res1['invalid_count']);

        // 2. Out-of-range coordinates (Latitude > 90)
        $outOfRangeFeature = [
            'type' => 'Feature',
            'properties' => [],
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [
                    [
                        [95.10, 105.10], // Lat 105 invalid
                        [95.20, 105.10],
                        [95.20, 105.20],
                        [95.10, 105.10],
                    ],
                ],
            ],
        ];
        $res2 = $validator->validate($outOfRangeFeature);
        $this->assertFalse($res2['is_valid']);
    }

    /**
     * Uji command rzwp3k:import dengan mode --dry-run tidak mengubah basis data.
     */
    public function test_artisan_import_dry_run_does_not_mutate_database(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'rzwp3k_test_').'.geojson';
        $sampleData = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'code' => 'DRY-RUN-01',
                        'name' => 'Zona Uji Dry Run',
                        'zone_type' => 'KPU',
                        'subzone_type' => 'KPU-PT',
                    ],
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [
                            [
                                [95.10, 5.10],
                                [95.20, 5.10],
                                [95.20, 5.20],
                                [95.10, 5.20],
                                [95.10, 5.10],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        file_put_contents($tmpFile, json_encode($sampleData));

        $exitCode = Artisan::call('rzwp3k:import', [
            '--file' => $tmpFile,
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseMissing('rzwp3k_zones', ['code' => 'DRY-RUN-01']);

        @unlink($tmpFile);
    }

    /**
     * Uji eksekusi import rzwp3k:import menyimpan data secara benar dalam transaksi.
     */
    public function test_artisan_import_saves_valid_record(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'rzwp3k_real_').'.geojson';
        $sampleData = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'code' => 'IMPORT-TEST-01',
                        'name' => 'Zona Impor Berhasil',
                        'zone_type' => 'KK',
                        'subzone_type' => 'KK-KKP',
                        'area_ha' => 3500.75,
                    ],
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [
                            [
                                [95.10, 5.10],
                                [95.20, 5.10],
                                [95.20, 5.20],
                                [95.10, 5.20],
                                [95.10, 5.10],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        file_put_contents($tmpFile, json_encode($sampleData));

        $exitCode = Artisan::call('rzwp3k:import', [
            '--file' => $tmpFile,
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseHas('rzwp3k_zones', [
            'code' => 'IMPORT-TEST-01',
            'name' => 'Zona Impor Berhasil',
            'zone_type' => 'KK',
        ]);

        @unlink($tmpFile);
    }
}

<?php

namespace Tests\Feature\Gfw;

use App\Http\Controllers\Gfw\GfwVesselMonitoringController;
use App\Models\Gfw\GfwSyncRun;
use App\Models\Gfw\GfwVessel;
use App\Models\Gfw\GfwVesselPresence;
use App\Services\Gfw\AoiService;
use App\Services\Gfw\GfwVesselService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

class GfwVesselObservatoryV12Test extends TestCase
{
    /** @var list<array<string, mixed>> */
    protected static array $vesselBackup = [];

    /** @var list<array<string, mixed>> */
    protected static array $presenceBackup = [];

    /** @var list<array<string, mixed>> */
    protected static array $syncRunBackup = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        try {
            self::$vesselBackup = GfwVessel::all()->toArray();
            self::$presenceBackup = GfwVesselPresence::all()->toArray();
            self::$syncRunBackup = GfwSyncRun::all()->toArray();
        } catch (\Throwable) {
        }
    }

    public static function tearDownAfterClass(): void
    {
        try {
            GfwVessel::where('gfw_vessel_id', 'like', 'vessel-test-%')->delete();
            GfwVesselPresence::where('gfw_vessel_id', 'like', 'vessel-test-%')->delete();
        } catch (\Throwable) {
        }

        parent::tearDownAfterClass();
    }

    public function test_buffer_100nm_geospatial_specifications(): void
    {
        $aoiService = app(AoiService::class);
        $summary = $aoiService->getZeeIndonesiaAcehBuffer100NmSummary();

        $this->assertTrue($summary['success']);
        $this->assertSame('Zona Observasi GFW +100 NM', $summary['name']);
        $this->assertSame('Polygon', $summary['geometry_type']);
        $this->assertSame('EPSG:4326', $summary['crs']);
        $this->assertSame(185200, $summary['buffer_distance_meters']);
        $this->assertSame(185.2, $summary['buffer_distance_km']);
        $this->assertSame(100, $summary['buffer_distance_nm']);

        // Verify valid RFC 7946 polygon geometry
        $geoJson = $aoiService->getZeeIndonesiaAcehBuffer100NmGeometry();
        $this->assertSame('FeatureCollection', $geoJson['type']);
        $polygon = $geoJson['features'][0]['geometry'];
        $this->assertSame('Polygon', $polygon['type']);
        $this->assertIsArray($polygon['coordinates']);
        $this->assertNotEmpty($polygon['coordinates'][0]);

        // First and last coordinates must be identical (closed ring)
        $ring = $polygon['coordinates'][0];
        $this->assertSame($ring[0], end($ring));

        // Bbox comparison: buffer zone must be strictly wider than base ZEE
        $baseSummary = $aoiService->getZeeIndonesiaAcehSummary();
        $this->assertLessThan($baseSummary['bounding_box']['min_lon'], $summary['bounding_box']['min_lon']);
        $this->assertLessThan($baseSummary['bounding_box']['min_lat'], $summary['bounding_box']['min_lat']);
        $this->assertGreaterThan($baseSummary['bounding_box']['max_lon'], $summary['bounding_box']['max_lon']);
        $this->assertGreaterThan($baseSummary['bounding_box']['max_lat'], $summary['bounding_box']['max_lat']);
    }

    public function test_aoi_api_endpoints_return_correct_buffer_data(): void
    {
        // 1. Base endpoint
        $resBase = $this->getJson('/api/gfw/aoi/zee-indonesia-aceh');
        $resBase->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('name', 'ZEE Indonesia - Kawasan Aceh');

        // 2. Buffer 100 NM endpoint
        $resBuffer = $this->getJson('/api/gfw/aoi/zee-indonesia-aceh?buffer=100nm');
        $resBuffer->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('name', 'Zona Observasi GFW +100 NM')
            ->assertJsonPath('buffer_distance_meters', 185200)
            ->assertJsonPath('buffer_distance_km', 185.2)
            ->assertJsonPath('buffer_distance_nm', 100);

        // 3. Buffer 100 NM with GeoJSON
        $resGeoJson = $this->getJson('/api/gfw/aoi/zee-indonesia-aceh?buffer=100nm&geojson=1');
        $resGeoJson->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'geojson' => [
                    'type',
                    'features' => [
                        '*' => ['type', 'properties', 'geometry'],
                    ],
                ],
            ]);

        // 4. Regional buffer endpoint
        $resRegion = $this->getJson('/api/gfw/regions/aceh_waters_buffer_100nm');
        $resRegion->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.buffer_distance_meters', 185200)
            ->assertJsonPath('data.name', 'Zona Observasi GFW +100 NM');
    }

    public function test_vessel_normalization_includes_all_required_identity_fields(): void
    {
        $vesselService = app(GfwVesselService::class);

        $mockRawEntry = [
            'id' => 'vessel-test-v12-001',
            'ssvid' => '525999888',
            'shipname' => 'KM. BAHARI SENTOSA',
            'flag' => 'IDN',
            'callsign' => 'YB9999',
            'imo' => '9988776',
            'vesselType' => 'fishing',
            'geartype' => 'trawlers',
            'lengthM' => 28.5,
            'tonnageGt' => 120.0,
            'dataset' => 'public-global-vessel-identity:latest',
        ];

        $normalized = $vesselService->normalize($mockRawEntry);

        $this->assertSame('vessel-test-v12-001', $normalized['gfw_vessel_id']);
        $this->assertSame('525999888', $normalized['mmsi']);
        $this->assertSame('KM. BAHARI SENTOSA', $normalized['name']);
        $this->assertSame('KM. BAHARI SENTOSA', $normalized['shipname']);
        $this->assertSame('9988776', $normalized['imo']);
        $this->assertSame('YB9999', $normalized['callsign']);
        $this->assertSame('IDN', $normalized['flag']);
        $this->assertSame('fishing', $normalized['vessel_type']);
        $this->assertSame('fishing', $normalized['vesselType']);
        $this->assertSame(28.5, $normalized['length_m']);
        $this->assertSame(28.5, $normalized['lengthM']);
        $this->assertSame(120.0, $normalized['tonnage_gt']);
        $this->assertSame(120.0, $normalized['tonnageGt']);

        // Missing fields produce null and do not throw exceptions
        $mockEmptyEntry = [
            'id' => 'vessel-test-empty-002',
        ];
        $emptyNormalized = $vesselService->normalize($mockEmptyEntry);
        $this->assertNull($emptyNormalized['mmsi']);
        $this->assertNull($emptyNormalized['imo']);
        $this->assertNull($emptyNormalized['callsign']);
        $this->assertNull($emptyNormalized['length_m']);
        $this->assertNull($emptyNormalized['tonnage_gt']);
    }

    public function test_last_observation_resolves_latest_observed_at_and_coordinates(): void
    {
        $vesselService = app(GfwVesselService::class);
        $testVesselId = 'vessel-test-last-obs-003';

        // Clean any existing
        GfwVesselPresence::where('gfw_vessel_id', $testVesselId)->delete();
        GfwVessel::where('gfw_vessel_id', $testVesselId)->delete();

        $vessel = GfwVessel::create([
            'gfw_vessel_id' => $testVesselId,
            'name' => 'KM. UJUNG SUMATRA',
            'mmsi' => '525123456',
            'imo' => '1234567',
            'callsign' => 'PKACEH',
            'flag' => 'IDN',
            'vessel_type' => 'fishing',
            'gear_type' => 'tuna_longlines',
            'length_m' => 32.4,
            'tonnage_gt' => 150.0,
            'raw_data' => [],
            'last_synced_at' => now(),
        ]);

        // Observation 1: 10:00
        GfwVesselPresence::create([
            'gfw_vessel_id' => $testVesselId,
            'aoi' => 'zee-indonesia-aceh',
            'observed_at' => Carbon::parse('2026-09-20 10:00:00'),
            'latitude' => 5.1000,
            'longitude' => 95.1000,
            'speed' => 6.2,
            'course' => 90.0,
            'source_dataset' => 'public-global-vessel-tracks:latest',
            'source_version' => 'v3',
        ]);

        // Observation 2: 11:00
        GfwVesselPresence::create([
            'gfw_vessel_id' => $testVesselId,
            'aoi' => 'zee-indonesia-aceh',
            'observed_at' => Carbon::parse('2026-09-20 11:00:00'),
            'latitude' => 5.2000,
            'longitude' => 95.2000,
            'speed' => 7.1,
            'course' => 95.0,
            'source_dataset' => 'public-global-vessel-tracks:latest',
            'source_version' => 'v3',
        ]);

        // Observation 3: 12:00 (Latest observation)
        GfwVesselPresence::create([
            'gfw_vessel_id' => $testVesselId,
            'aoi' => 'zee-indonesia-aceh',
            'observed_at' => Carbon::parse('2026-09-20 12:00:00'),
            'latitude' => 5.3500,
            'longitude' => 95.4500,
            'speed' => 8.5,
            'course' => 110.0,
            'source_dataset' => 'public-global-vessel-tracks:latest',
            'source_version' => 'v3',
        ]);

        $retrieved = $vesselService->getById($testVesselId);
        $this->assertNotNull($retrieved);

        // Expected: Observasi Terakhir = 12:00 and coordinates match 12:00 observation
        $this->assertSame('2026-09-20T12:00:00+00:00', Carbon::parse($retrieved['observed_at'])->toIso8601String());
        $this->assertEqualsWithDelta(5.3500, $retrieved['latitude'], 0.0001);
        $this->assertEqualsWithDelta(95.4500, $retrieved['longitude'], 0.0001);
        $this->assertEqualsWithDelta(8.5, $retrieved['speed'], 0.1);
        $this->assertEqualsWithDelta(110.0, $retrieved['course'], 0.1);

        // Cleanup
        GfwVesselPresence::where('gfw_vessel_id', $testVesselId)->delete();
        GfwVessel::where('gfw_vessel_id', $testVesselId)->delete();
    }

    public function test_gfw_vessels_web_controller_and_view_render(): void
    {
        $controller = app(GfwVesselMonitoringController::class);
        $request = Request::create('/gfw/vessels', 'GET');
        $view = $controller->index($request);

        $this->assertSame('gfw.vessels', $view->getName());
        $data = $view->getData();
        $this->assertSame('ZEE Indonesia - Kawasan Aceh', $data['aoiSummary']['name']);
        $this->assertSame('Zona Observasi GFW +100 NM', $data['bufferSummary']['name']);
        $this->assertSame(185200, $data['bufferSummary']['buffer_distance_meters']);
        $this->assertSame(185.2, $data['bufferSummary']['buffer_distance_km']);
        $this->assertSame(100, $data['bufferSummary']['buffer_distance_nm']);

        // Verify template contents for required elements
        $viewPath = resource_path('views/gfw/vessels.blade.php');
        $this->assertFileExists($viewPath);
        $content = file_get_contents($viewPath);

        $this->assertStringContainsString('ZEE Aceh', $content);
        $this->assertStringContainsString('MMSI', $content);
        $this->assertStringContainsString('IMO', $content);
        $this->assertStringContainsString('Tidak tersedia', $content);
        $this->assertStringContainsString('big-zee-aceh-line', $content);
        $this->assertStringContainsString('toggle-big-zee-aceh', $content);

        // Security check: Never leak GFW credentials in template
        $this->assertStringNotContainsString('GFW_API_TOKEN', $content);
        $this->assertStringNotContainsString('Authorization', $content);
        $this->assertStringNotContainsString('Bearer', $content);
    }
}

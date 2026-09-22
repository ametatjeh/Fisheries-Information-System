<?php

namespace Tests\Feature\Gfw;

use App\Services\Gfw\AoiService;
use Tests\TestCase;

class GfwAoiTest extends TestCase
{
    public function test_zee_indonesia_aceh_endpoint_returns_expected_summary_json(): void
    {
        $response = $this->getJson('/api/gfw/aoi/zee-indonesia-aceh');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'name' => 'ZEE Indonesia - Kawasan Aceh',
                'geometry_type' => 'Polygon',
                'crs' => 'EPSG:4326',
                'feature_count' => 1,
            ]);

        $content = $response->getContent();
        $this->assertStringNotContainsString('token', strtolower($content));
        $this->assertStringNotContainsString('bearer', strtolower($content));
        $this->assertStringNotContainsString('authorization', strtolower($content));
    }

    public function test_legacy_aoi_endpoint_returns_expected_summary_json(): void
    {
        $response = $this->getJson('/api/gfw/aoi/zee-aceh');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'name' => 'ZEE Aceh',
                'geometry_type' => 'Polygon',
                'crs' => 'EPSG:4326',
                'feature_count' => 1,
            ]);
    }

    public function test_aoi_service_reads_and_validates_zee_indonesia_aceh_geojson(): void
    {
        $service = new AoiService;
        $geoJson = $service->getZeeIndonesiaAcehGeometry();

        $this->assertIsArray($geoJson);
        $this->assertEquals('FeatureCollection', $geoJson['type']);
        $this->assertNotEmpty($geoJson['features']);

        $feature = $geoJson['features'][0];
        $this->assertEquals('Feature', $feature['type']);
        $this->assertEquals('Polygon', $feature['geometry']['type']);

        $coordinates = $feature['geometry']['coordinates'][0];
        $this->assertGreaterThanOrEqual(4, count($coordinates));

        // Validate coordinate order [lon, lat] and bounds
        foreach ($coordinates as $point) {
            $lon = $point[0];
            $lat = $point[1];

            // Lon for Aceh region (~94 - 99 E)
            $this->assertGreaterThanOrEqual(94.0, $lon);
            $this->assertLessThanOrEqual(99.0, $lon);

            // Lat for Aceh region (~1.0 - 7.0 N)
            $this->assertGreaterThanOrEqual(1.0, $lat);
            $this->assertLessThanOrEqual(7.0, $lat);
        }

        // Closed ring check (first == last)
        $first = $coordinates[0];
        $last = $coordinates[count($coordinates) - 1];
        $this->assertEquals($first[0], $last[0]);
        $this->assertEquals($first[1], $last[1]);
    }

    public function test_aoi_service_computes_bounding_box_for_zee_indonesia_aceh(): void
    {
        $service = new AoiService;
        $summary = $service->getZeeIndonesiaAcehSummary();

        $this->assertTrue($summary['success']);
        $this->assertArrayHasKey('bounding_box', $summary);
        $bbox = $summary['bounding_box'];

        $this->assertEquals(94.5, $bbox['min_lon']);
        $this->assertEquals(1.8, $bbox['min_lat']);
        $this->assertEquals(98.3, $bbox['max_lon']);
        $this->assertEquals(6.2, $bbox['max_lat']);
    }
}

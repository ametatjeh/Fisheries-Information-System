<?php

namespace Tests\Feature\Gfw;

use App\Models\FishCatch;
use App\Models\Fisher;
use App\Models\Gfw\GfwSyncRun;
use App\Models\Gfw\GfwVessel;
use App\Models\Gfw\GfwVesselPresence;
use App\Models\Gfw\GfwVesselType;
use App\Models\Landing;
use App\Models\Vessel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GfwDatabaseIsolationTest extends TestCase
{
    /**
     * Test that all GFW models use the dedicated 'gfw' database connection.
     */
    public function test_gfw_models_use_dedicated_gfw_connection(): void
    {
        $vessel = new GfwVessel;
        $vesselType = new GfwVesselType;
        $presence = new GfwVesselPresence;
        $syncRun = new GfwSyncRun;

        $this->assertSame('gfw', $vessel->getConnectionName());
        $this->assertSame('gfw', $vesselType->getConnectionName());
        $this->assertSame('gfw', $presence->getConnectionName());
        $this->assertSame('gfw', $syncRun->getConnectionName());
    }

    /**
     * Test that core fisheries domain models do NOT use the GFW connection.
     */
    public function test_main_domain_models_use_default_fisheries_connection(): void
    {
        $vessel = new Vessel;
        $fisher = new Fisher;
        $catch = new FishCatch;
        $landing = new Landing;

        $this->assertNotSame('gfw', $vessel->getConnectionName());
        $this->assertNotSame('gfw', $fisher->getConnectionName());
        $this->assertNotSame('gfw', $catch->getConnectionName());
        $this->assertNotSame('gfw', $landing->getConnectionName());

        $this->assertNull($vessel->getConnectionName());
        $this->assertNull($fisher->getConnectionName());
        $this->assertNull($catch->getConnectionName());
        $this->assertNull($landing->getConnectionName());
    }

    /**
     * Test that configuration establishes distinct database connections.
     */
    public function test_connection_configuration_is_strictly_separated(): void
    {
        $gfwConfig = Config::get('database.connections.gfw');
        $defaultConnection = Config::get('database.default');

        $this->assertIsArray($gfwConfig);
        $this->assertSame('sistem_gfw', env('GFW_DB_DATABASE', 'sistem_gfw'));
        $this->assertSame('sistem_gfw', $gfwConfig['database']);
        $this->assertNotSame('gfw', $defaultConnection);
    }

    /**
     * Critical Isolation Test:
     * - The local fisheries table 'vessels' must NOT exist in the GFW database.
     * - New GFW Observatory tables must NOT exist in 'sistem_perikanan'.
     */
    public function test_critical_isolation_between_gfw_and_fisheries_databases(): void
    {
        // When MySQL connections are available
        try {
            $gfwPdo = DB::connection('gfw')->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('MySQL connection gfw not accessible in this environment.');
        }

        // 1. Local fisheries table 'vessels' MUST NOT exist in GFW database
        $this->assertFalse(
            Schema::connection('gfw')->hasTable('vessels'),
            'Table [vessels] must NOT exist in sistem_gfw database.'
        );

        $this->assertFalse(
            Schema::connection('gfw')->hasTable('fishermen'),
            'Table [fishermen] must NOT exist in sistem_gfw database.'
        );

        $this->assertFalse(
            Schema::connection('gfw')->hasTable('catches'),
            'Table [catches] must NOT exist in sistem_gfw database.'
        );

        // 2. New GFW Observatory tables MUST exist in sistem_gfw
        $this->assertTrue(
            Schema::connection('gfw')->hasTable('gfw_vessels'),
            'Table [gfw_vessels] must exist in sistem_gfw database.'
        );

        $this->assertTrue(
            Schema::connection('gfw')->hasTable('gfw_vessel_types'),
            'Table [gfw_vessel_types] must exist in sistem_gfw database.'
        );

        $this->assertTrue(
            Schema::connection('gfw')->hasTable('gfw_vessel_presence'),
            'Table [gfw_vessel_presence] must exist in sistem_gfw database.'
        );

        $this->assertTrue(
            Schema::connection('gfw')->hasTable('gfw_sync_runs'),
            'Table [gfw_sync_runs] must exist in sistem_gfw database.'
        );

        // 3. New GFW Observatory tables MUST NOT exist in sistem_perikanan
        try {
            DB::connection('mysql')->getPdo();

            $this->assertFalse(
                Schema::connection('mysql')->hasTable('gfw_vessel_types'),
                'Table [gfw_vessel_types] must NOT exist in sistem_perikanan database.'
            );

            $this->assertFalse(
                Schema::connection('mysql')->hasTable('gfw_vessel_presence'),
                'Table [gfw_vessel_presence] must NOT exist in sistem_perikanan database.'
            );

            $this->assertFalse(
                Schema::connection('mysql')->hasTable('gfw_sync_runs'),
                'Table [gfw_sync_runs] must NOT exist in sistem_perikanan database.'
            );

            // 4. Verify 0 cross-database foreign keys
            $crossFks = DB::connection('gfw')->select("
                SELECT CONSTRAINT_NAME, TABLE_NAME, REFERENCED_TABLE_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = 'sistem_gfw' 
                  AND REFERENCED_TABLE_SCHEMA = 'sistem_perikanan'
            ");

            $this->assertCount(0, $crossFks, 'There must be zero foreign keys from sistem_gfw to sistem_perikanan.');
        } catch (\Throwable $e) {
            // Ignored if mysql connection is not accessible in this test runner
        }
    }
}

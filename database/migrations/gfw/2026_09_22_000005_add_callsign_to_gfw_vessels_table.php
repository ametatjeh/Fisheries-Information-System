<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'gfw';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('gfw')->table('gfw_vessels', function (Blueprint $table) {
            if (! Schema::connection('gfw')->hasColumn('gfw_vessels', 'callsign')) {
                $table->string('callsign', 50)->nullable()->after('imo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('gfw')->table('gfw_vessels', function (Blueprint $table) {
            if (Schema::connection('gfw')->hasColumn('gfw_vessels', 'callsign')) {
                $table->dropColumn('callsign');
            }
        });
    }
};

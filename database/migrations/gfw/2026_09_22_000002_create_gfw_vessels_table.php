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
        Schema::connection('gfw')->create('gfw_vessels', function (Blueprint $table) {
            $table->id();
            $table->string('gfw_vessel_id', 100)->unique();
            $table->string('name', 150)->nullable()->index();
            $table->string('ship_name', 150)->nullable()->index();
            $table->string('mmsi', 30)->nullable()->index();
            $table->string('imo', 30)->nullable()->index();
            $table->string('flag', 10)->nullable()->index();
            $table->string('vessel_type', 50)->nullable()->index();
            $table->string('vessel_class', 50)->nullable();
            $table->string('gear_type', 50)->nullable();
            $table->decimal('length_m', 8, 2)->nullable();
            $table->decimal('tonnage_gt', 10, 2)->nullable();
            $table->decimal('gross_tonnage', 10, 2)->nullable();
            $table->decimal('engine_power_kw', 10, 2)->nullable();
            $table->string('source', 50)->default('GFW');
            $table->string('source_version', 50)->default('public-global-vessel-identity:latest');
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->char('raw_hash', 32)->nullable()->index();
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('gfw')->dropIfExists('gfw_vessels');
    }
};

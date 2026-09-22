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
        Schema::connection('gfw')->create('gfw_vessel_presence', function (Blueprint $table) {
            $table->id();
            $table->string('gfw_vessel_id', 100)->index();
            $table->string('aoi', 50)->default('zee-indonesia-aceh')->index();
            $table->timestamp('observed_at')->index();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('speed', 5, 2)->nullable();
            $table->decimal('course', 5, 2)->nullable();
            $table->string('vessel_type', 50)->nullable()->index();
            $table->string('flag', 10)->nullable()->index();
            $table->string('source_dataset', 100)->default('public-global-vessel-tracks:latest');
            $table->string('source_version', 50)->nullable();
            $table->timestamps();

            $table->index(['gfw_vessel_id', 'observed_at']);
            $table->index(['aoi', 'observed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('gfw')->dropIfExists('gfw_vessel_presence');
    }
};

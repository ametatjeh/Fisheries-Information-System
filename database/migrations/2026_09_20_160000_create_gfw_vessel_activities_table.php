<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gfw_vessel_activities', function (Blueprint $table) {
            $table->id();
            $table->string('gfw_vessel_id')->nullable()->index();
            $table->string('activity_type', 50)->default('presence')->index();
            $table->string('region_key', 50)->nullable()->index();
            $table->decimal('latitude', 10, 7)->nullable()->index();
            $table->decimal('longitude', 10, 7)->nullable()->index();
            $table->timestamp('observation_timestamp')->nullable()->index();
            $table->date('period_start')->nullable()->index();
            $table->date('period_end')->nullable()->index();
            $table->decimal('hours', 8, 2)->nullable();
            $table->decimal('distance_km', 10, 2)->nullable();
            $table->decimal('speed_knots', 6, 2)->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gfw_vessel_activities');
    }
};

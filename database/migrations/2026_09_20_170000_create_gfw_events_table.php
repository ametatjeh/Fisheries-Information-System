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
        Schema::create('gfw_events', function (Blueprint $table) {
            $table->id();
            $table->string('gfw_event_id')->nullable()->unique();
            $table->string('event_type', 50)->index(); // apparent_fishing, potential_encounter, loitering, port_visit
            $table->string('gfw_vessel_id')->nullable()->index();
            $table->string('secondary_vessel_id')->nullable()->index();
            $table->string('region_key', 50)->nullable()->index();
            $table->decimal('latitude', 10, 7)->nullable()->index();
            $table->decimal('longitude', 10, 7)->nullable()->index();
            $table->timestamp('start_time')->nullable()->index();
            $table->timestamp('end_time')->nullable()->index();
            $table->decimal('duration_hours', 8, 2)->nullable();
            $table->string('confidence', 50)->nullable();
            $table->string('port_name')->nullable();
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
        Schema::dropIfExists('gfw_events');
    }
};

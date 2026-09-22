<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // 1. vessels
        Schema::table('vessels', function (Blueprint $table) {
            $table->dropForeign('vessels_owner_id_foreign');
            $table->foreign('owner_id')->references('id')->on('fishers')->restrictOnDelete();
        });

        // 2. fishing_trips
        Schema::table('fishing_trips', function (Blueprint $table) {
            $table->dropForeign('fishing_trips_vessel_id_foreign');
            $table->dropForeign('fishing_trips_captain_id_foreign');
            $table->foreign('vessel_id')->references('id')->on('vessels')->restrictOnDelete();
            $table->foreign('captain_id')->references('id')->on('fishers')->restrictOnDelete();
        });

        // 3. fishing_efforts
        Schema::table('fishing_efforts', function (Blueprint $table) {
            $table->dropForeign('fishing_efforts_fishing_trip_id_foreign');
            $table->dropForeign('fishing_efforts_fishing_gear_id_foreign');
            $table->foreign('fishing_trip_id')->references('id')->on('fishing_trips')->restrictOnDelete();
            $table->foreign('fishing_gear_id')->references('id')->on('fishing_gears')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('fishing_efforts', function (Blueprint $table) {
            $table->dropForeign(['fishing_trip_id']);
            $table->dropForeign(['fishing_gear_id']);
            $table->foreign('fishing_trip_id')->references('id')->on('fishing_trips')->cascadeOnDelete();
            $table->foreign('fishing_gear_id')->references('id')->on('fishing_gears')->cascadeOnDelete();
        });

        Schema::table('fishing_trips', function (Blueprint $table) {
            $table->dropForeign(['vessel_id']);
            $table->dropForeign(['captain_id']);
            $table->foreign('vessel_id')->references('id')->on('vessels')->cascadeOnDelete();
            $table->foreign('captain_id')->references('id')->on('fishers')->nullOnDelete();
        });

        Schema::table('vessels', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->foreign('owner_id')->references('id')->on('fishers')->nullOnDelete();
        });
    }
};

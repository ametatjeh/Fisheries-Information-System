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
        Schema::create('gfw_vessels', function (Blueprint $table) {
            $table->id();
            $table->string('gfw_vessel_id')->unique();
            $table->string('name')->nullable();
            $table->string('mmsi', 30)->nullable()->index();
            $table->string('imo', 30)->nullable()->index();
            $table->string('flag', 10)->nullable();
            $table->string('vessel_type')->nullable();
            $table->string('gear_type')->nullable();
            $table->decimal('length_m', 8, 2)->nullable();
            $table->decimal('tonnage_gt', 10, 2)->nullable();
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
        Schema::dropIfExists('gfw_vessels');
    }
};

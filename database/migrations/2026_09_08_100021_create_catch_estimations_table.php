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
        Schema::create('catch_estimations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regency_id')->constrained('regencies')->cascadeOnDelete()->comment('Wilayah kabupaten/kota');
            $table->foreignId('landing_site_id')->nullable()->constrained('landing_sites')->nullOnDelete()->comment('Lokasi pendaratan jika diestimasi per site');
            $table->foreignId('fish_species_id')->nullable()->constrained('fish_species')->nullOnDelete()->comment('Spesies ikan jika diestimasi per jenis');
            $table->foreignId('fishing_gear_id')->nullable()->constrained('fishing_gears')->nullOnDelete()->comment('Alat tangkap jika diestimasi per gear');
            $table->unsignedSmallInteger('year')->comment('Tahun periode estimasi (misal 2026)');
            $table->unsignedTinyInteger('month')->comment('Bulan periode estimasi (1-12)');
            $table->decimal('sampled_catch_kg', 12, 2)->default(0)->comment('Volume hasil tangkapan riil tersampel (kg)');
            $table->decimal('raising_factor', 8, 4)->default(1.0000)->comment('Faktor pengali / ekstrapolasi ke populasi');
            $table->decimal('estimated_catch_kg', 14, 2)->default(0)->comment('Hasil estimasi total produksi tangkapan (kg)');
            $table->unsignedInteger('estimated_effort_trips')->default(0)->comment('Estimasi total trip armada di wilayah');
            $table->decimal('cpue', 10, 4)->nullable()->comment('Catch Per Unit Effort (kg/trip)');
            $table->decimal('variance', 14, 4)->nullable()->comment('Nilai variansi statistik estimasi');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['year', 'month']);
            $table->index('regency_id');
            $table->index('landing_site_id');
            $table->index('fish_species_id');
            $table->index('fishing_gear_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catch_estimations');
    }
};

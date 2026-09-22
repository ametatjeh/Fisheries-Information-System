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
        Schema::create('monthly_production_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regency_id')->constrained('regencies')->cascadeOnDelete()->comment('Wilayah kabupaten/kota');
            $table->foreignId('landing_site_id')->nullable()->constrained('landing_sites')->nullOnDelete()->comment('Lokasi pendaratan jika spesifik per TPI');
            $table->foreignId('fish_species_id')->constrained('fish_species')->cascadeOnDelete()->comment('Komoditas jenis ikan');
            $table->foreignId('fishing_gear_id')->nullable()->constrained('fishing_gears')->nullOnDelete()->comment('Alat tangkap yang digunakan');
            $table->unsignedSmallInteger('year')->comment('Tahun statistik');
            $table->unsignedTinyInteger('month')->comment('Bulan statistik (1-12)');
            $table->decimal('total_volume_kg', 14, 2)->default(0)->comment('Total volume produksi (kg)');
            $table->decimal('total_value_rp', 16, 2)->default(0)->comment('Total nilai produksi (Rp)');
            $table->decimal('average_price_per_kg', 12, 2)->default(0)->comment('Harga rata-rata tertimbang per kg (Rp)');
            $table->unsignedInteger('total_active_vessels')->default(0)->comment('Jumlah kapal aktif beroperasi');
            $table->unsignedInteger('total_trips')->default(0)->comment('Jumlah trip penangkapan');
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
        Schema::dropIfExists('monthly_production_statistics');
    }
};

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
        Schema::create('landing_sites', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique()->comment('Kode pangkalan / TPI / Pelabuhan');
            $table->string('name', 150)->comment('Nama tempat pendaratan ikan (TPI) / pangkalan');
            $table->enum('site_type', [
                'PPS',
                'PPN',
                'PPP',
                'PPI',
                'TPI',
                'pangkalan_pendaratan_tradisional',
            ])->default('TPI')->comment('Kategori pelabuhan perikanan / pangkalan');
            $table->foreignId('province_id')->constrained('provinces')->cascadeOnDelete();
            $table->foreignId('regency_id')->constrained('regencies')->cascadeOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->text('address')->nullable()->comment('Alamat lengkap lokasi');
            $table->decimal('latitude', 10, 7)->nullable()->comment('Koordinat lintang GIS');
            $table->decimal('longitude', 10, 7)->nullable()->comment('Koordinat bujur GIS');
            $table->boolean('is_active')->default(true)->comment('Status keaktifan lokasi');
            $table->timestamps();

            $table->index('name');
            $table->index('site_type');
            $table->index('regency_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_sites');
    }
};

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
        Schema::create('fish_species', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Kode spesies / 3-alpha code FAO (misal SKJ, YFT)');
            $table->string('indonesian_name', 100)->comment('Nama umum Indonesia (misal Cakalang)');
            $table->string('local_name', 100)->nullable()->comment('Nama daerah/lokal');
            $table->string('scientific_name', 150)->comment('Nama ilmiah / latin (misal Katsuwonus pelamis)');
            $table->string('english_name', 100)->nullable()->comment('Nama dalam bahasa Inggris');
            $table->string('family', 100)->nullable()->comment('Famili taksonomi (misal Scombridae)');
            $table->enum('fish_group', [
                'pelagis_besar',
                'pelagis_kecil',
                'demersal',
                'karang',
                'udang_krustasea',
                'moluska',
                'lainnya',
            ])->default('pelagis_besar')->comment('Kelompok sumber daya ikan');
            $table->string('conservation_status', 50)->nullable()->comment('Status konservasi IUCN / CITES');
            $table->string('photo_path', 255)->nullable()->comment('Path gambar/foto referensi');
            $table->boolean('is_active')->default(true)->comment('Status aktif dalam sistem');
            $table->timestamps();

            $table->index('indonesian_name');
            $table->index('scientific_name');
            $table->index('fish_group');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fish_species');
    }
};

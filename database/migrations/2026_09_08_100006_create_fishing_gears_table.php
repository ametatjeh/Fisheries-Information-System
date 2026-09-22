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
        Schema::create('fishing_gears', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Kode baku alat tangkap KKP/FAO');
            $table->string('name', 100)->comment('Nama alat penangkap ikan');
            $table->enum('category', [
                'jaring_lingkar',
                'jaring_tarik',
                'jaring_hela',
                'jaring_angkat',
                'alat_jatuh',
                'jaring_insang',
                'perangkap',
                'pancing',
                'alat_penjepit_melukai',
                'lainnya',
            ])->comment('Klasifikasi kelompok alat penangkapan ikan (Permen KP / FAO)');
            $table->text('description')->nullable()->comment('Deskripsi spesifikasi alat');
            $table->boolean('is_active')->default(true)->comment('Status aktif dalam sistem');
            $table->timestamps();

            $table->index('name');
            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishing_gears');
    }
};

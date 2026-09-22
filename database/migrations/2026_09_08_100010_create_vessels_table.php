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
        Schema::create('vessels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->comment('Nama kapal / perahu (KM / KMN)');
            $table->string('registration_number', 50)->unique()->nullable()->comment('Tanda selar / nomor registrasi kapal');
            $table->foreignId('owner_id')->nullable()->constrained('fishers')->nullOnDelete()->comment('Pemilik kapal');
            $table->enum('vessel_type', [
                'tanpa_motor',
                'motor_tempel',
                'kapal_motor',
            ])->default('kapal_motor')->comment('Tipe armada penangkapan');
            $table->decimal('gross_tonnage', 8, 2)->default(0)->comment('Ukuran kapal dalam Gross Tonnage (GT)');
            $table->decimal('length', 6, 2)->nullable()->comment('Panjang kapal (LoA) dalam meter');
            $table->decimal('width', 6, 2)->nullable()->comment('Lebar kapal (Beam) dalam meter');
            $table->decimal('depth', 6, 2)->nullable()->comment('Dalam kapal (Draft) dalam meter');
            $table->decimal('engine_power_hp', 8, 2)->nullable()->comment('Kekuatan mesin utama (PK / HP)');
            $table->string('engine_brand', 100)->nullable()->comment('Merek mesin kapal');
            $table->unsignedSmallInteger('build_year')->nullable()->comment('Tahun pembuatan kapal');
            $table->foreignId('homeport_site_id')->nullable()->constrained('landing_sites')->nullOnDelete()->comment('Pangkalan / Homebase');
            $table->foreignId('primary_gear_id')->nullable()->constrained('fishing_gears')->nullOnDelete()->comment('Alat tangkap utama');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index('vessel_type');
            $table->index('gross_tonnage');
            $table->index('owner_id');
            $table->index('homeport_site_id');
            $table->index('primary_gear_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vessels');
    }
};

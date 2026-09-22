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
        Schema::create('fishing_efforts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fishing_trip_id')->constrained('fishing_trips')->cascadeOnDelete();
            $table->foreignId('fishing_gear_id')->constrained('fishing_gears')->cascadeOnDelete();
            $table->unsignedSmallInteger('setting_number')->default(1)->comment('Urutan setting/penurunan alat (ke-1, ke-2, dst)');
            $table->dateTime('setting_date')->nullable()->comment('Waktu mulai penurunan alat (setting)');
            $table->dateTime('hauling_date')->nullable()->comment('Waktu mulai penarikan alat (hauling)');
            $table->decimal('duration_hours', 5, 2)->nullable()->comment('Lama perendaman / penarikan alat (jam)');
            $table->unsignedInteger('setting_count')->default(1)->comment('Frekuensi tebar / ulur');
            $table->unsignedInteger('hook_count')->nullable()->comment('Jumlah mata pancing (jika pancing/rawai)');
            $table->decimal('net_length_meters', 8, 2)->nullable()->comment('Panjang jaring yang dibentangkan (meter)');
            $table->decimal('latitude_setting', 10, 7)->nullable()->comment('Titik GPS setting');
            $table->decimal('longitude_setting', 10, 7)->nullable()->comment('Titik GPS setting');
            $table->decimal('latitude_hauling', 10, 7)->nullable()->comment('Titik GPS hauling');
            $table->decimal('longitude_hauling', 10, 7)->nullable()->comment('Titik GPS hauling');
            $table->timestamps();

            $table->index(['fishing_trip_id', 'setting_number']);
            $table->index('fishing_gear_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishing_efforts');
    }
};

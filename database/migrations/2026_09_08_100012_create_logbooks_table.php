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
        Schema::create('logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fishing_trip_id')->constrained('fishing_trips')->cascadeOnDelete();
            $table->date('log_date')->comment('Tanggal pencatatan logbook');
            $table->time('log_time')->nullable()->comment('Waktu kejadian / pengamatan');
            $table->decimal('latitude', 10, 7)->nullable()->comment('Posisi lintang GPS');
            $table->decimal('longitude', 10, 7)->nullable()->comment('Posisi bujur GPS');
            $table->enum('weather_condition', [
                'cerah',
                'berawan',
                'hujan_ringan',
                'hujan_lebat',
                'badai',
            ])->nullable()->comment('Kondisi cuaca');
            $table->decimal('wave_height_meters', 4, 2)->nullable()->comment('Tinggi gelombang laut (meter)');
            $table->string('sea_condition', 50)->nullable()->comment('Kondisi arus/ombak laut');
            $table->text('activity_description')->nullable()->comment('Uraian kegiatan di laut');
            $table->timestamps();

            $table->index(['fishing_trip_id', 'log_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbooks');
    }
};

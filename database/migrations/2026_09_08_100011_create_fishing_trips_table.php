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
        Schema::create('fishing_trips', function (Blueprint $table) {
            $table->id();
            $table->string('trip_number', 50)->unique()->comment('Nomor unik trip penangkapan (misal TRIP-202609-0001)');
            $table->foreignId('vessel_id')->constrained('vessels')->cascadeOnDelete()->comment('Kapal yang melakukan operasi');
            $table->foreignId('captain_id')->nullable()->constrained('fishers')->nullOnDelete()->comment('Nahkoda / jurumudi');
            $table->foreignId('departure_site_id')->constrained('landing_sites')->cascadeOnDelete()->comment('Pelabuhan / TPI keberangkatan');
            $table->foreignId('landing_site_id')->nullable()->constrained('landing_sites')->nullOnDelete()->comment('Pelabuhan / TPI kedatangan / bongkar');
            $table->dateTime('departure_date')->comment('Waktu keberangkatan ke laut');
            $table->dateTime('return_date')->nullable()->comment('Waktu kembali / mendarat');
            $table->unsignedSmallInteger('crew_count')->default(1)->comment('Jumlah awak kapal / ABK');
            $table->decimal('fuel_consumption_liters', 10, 2)->nullable()->comment('Konsumsi BBM (liter)');
            $table->decimal('ice_consumption_kg', 10, 2)->nullable()->comment('Konsumsi es balok/curah (kg)');
            $table->foreignId('primary_gear_id')->nullable()->constrained('fishing_gears')->nullOnDelete()->comment('Alat tangkap yang digunakan pada trip');
            $table->string('fishing_ground_name', 150)->nullable()->comment('Nama area penangkapan / perairan');
            $table->string('fma_code', 20)->nullable()->comment('Kode Wilayah Pengelolaan Perikanan (WPPNRI misal 571, 572)');
            $table->enum('validation_status', [
                'draft',
                'submitted',
                'validated',
                'rejected',
            ])->default('draft')->comment('Status tahapan verifikasi data');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete()->comment('Enumerator penginput');
            $table->dateTime('submitted_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete()->comment('Validator yang memverifikasi');
            $table->dateTime('validated_at')->nullable();
            $table->text('rejection_reason')->nullable()->comment('Alasan penolakan jika status rejected');
            $table->text('notes')->nullable()->comment('Catatan operasional tambahan');
            $table->timestamps();

            $table->index('vessel_id');
            $table->index('captain_id');
            $table->index('departure_site_id');
            $table->index('landing_site_id');
            $table->index('departure_date');
            $table->index('validation_status');
            $table->index('fma_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishing_trips');
    }
};

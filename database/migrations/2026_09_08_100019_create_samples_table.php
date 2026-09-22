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
        Schema::create('samples', function (Blueprint $table) {
            $table->id();
            $table->string('sample_code', 50)->unique()->comment('Nomor identifikasi sampel (misal SMP-202609-001)');
            $table->foreignId('sampling_plan_id')->nullable()->constrained('sampling_plans')->nullOnDelete()->comment('Rencana program sampling induk jika ada');
            $table->foreignId('fishing_trip_id')->nullable()->constrained('fishing_trips')->nullOnDelete()->comment('Trip penangkapan sumber sampel jika terlacak');
            $table->foreignId('landing_site_id')->constrained('landing_sites')->cascadeOnDelete()->comment('Lokasi TPI pengambilan sampel');
            $table->foreignId('enumerator_id')->nullable()->constrained('users')->nullOnDelete()->comment('Petugas enumerator pengambil sampel');
            $table->date('sample_date')->comment('Tanggal sampling');
            $table->unsignedInteger('total_specimens')->default(0)->comment('Jumlah ikan yang diukur');
            $table->decimal('total_weight_kg', 10, 2)->default(0)->comment('Total berat keranjang/sub-sampel (kg)');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('sampling_plan_id');
            $table->index('fishing_trip_id');
            $table->index('landing_site_id');
            $table->index('sample_date');
            $table->index('enumerator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};

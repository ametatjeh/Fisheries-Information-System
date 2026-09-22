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
        Schema::create('catches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fishing_trip_id')->constrained('fishing_trips')->cascadeOnDelete();
            $table->foreignId('fishing_effort_id')->nullable()->constrained('fishing_efforts')->nullOnDelete()->comment('Terkait setting alat tertentu (opsional)');
            $table->foreignId('fish_species_id')->constrained('fish_species')->cascadeOnDelete();
            $table->decimal('weight_kg', 10, 2)->default(0)->comment('Total berat tangkapan (kg)');
            $table->unsignedInteger('fish_count')->nullable()->comment('Jumlah ekor jika dihitung');
            $table->enum('catch_status', [
                'target',
                'bycatch',
                'discarded',
            ])->default('target')->comment('Hasil tangkapan utama, sampingan, atau dibuang');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('fishing_trip_id');
            $table->index('fishing_effort_id');
            $table->index('fish_species_id');
            $table->index('catch_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catches');
    }
};

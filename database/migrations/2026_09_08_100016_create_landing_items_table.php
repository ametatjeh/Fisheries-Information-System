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
        Schema::create('landing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_id')->constrained('landings')->cascadeOnDelete();
            $table->foreignId('fish_species_id')->constrained('fish_species')->cascadeOnDelete();
            $table->decimal('weight_kg', 10, 2)->default(0)->comment('Volume jenis ikan yang didaratkan (kg)');
            $table->unsignedInteger('fish_count')->nullable()->comment('Jumlah ekor jika dihitung');
            $table->decimal('price_per_kg', 12, 2)->default(0)->comment('Harga satuan lelang/pasar per kg (Rp)');
            $table->decimal('total_price', 15, 2)->default(0)->comment('Total nilai jenis ikan ini (Rp)');
            $table->enum('quality_grade', ['A', 'B', 'C', 'reject'])->nullable()->comment('Mutu/kesegaran ikan');
            $table->timestamps();

            $table->index('landing_id');
            $table->index('fish_species_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_items');
    }
};

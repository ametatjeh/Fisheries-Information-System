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
        Schema::create('biological_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained('samples')->cascadeOnDelete();
            $table->foreignId('fish_species_id')->constrained('fish_species')->cascadeOnDelete();
            $table->unsignedInteger('specimen_number')->comment('Nomor urut spesimen dalam sampel');
            $table->decimal('fork_length_cm', 6, 2)->nullable()->comment('Panjang cagak / FL (cm)');
            $table->decimal('total_length_cm', 6, 2)->nullable()->comment('Panjang total / TL (cm)');
            $table->decimal('standard_length_cm', 6, 2)->nullable()->comment('Panjang baku / SL (cm)');
            $table->decimal('weight_gram', 8, 2)->nullable()->comment('Berat individu (gram)');
            $table->enum('sex', ['male', 'female', 'undetermined'])->default('undetermined')->comment('Jenis kelamin individu ikan');
            $table->unsignedTinyInteger('gonad_maturity_stage')->nullable()->comment('Tingkat Kematangan Gonad / TKG (skala 1-5)');
            $table->unsignedTinyInteger('stomach_fullness')->nullable()->comment('Tingkat kepenuhan lambung (skala 1-5)');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('sample_id');
            $table->index('fish_species_id');
            $table->index('sex');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biological_measurements');
    }
};

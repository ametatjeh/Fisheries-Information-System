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
        Schema::create('fao_asfis_species', function (Blueprint $table) {
            $table->id();
            $table->char('fao_code', 3)->unique();
            $table->string('taxonomic_code', 15)->nullable()->index();
            $table->char('isscaap_code', 2)->nullable()->index();
            $table->string('scientific_name', 255)->nullable()->index();
            $table->string('author', 255)->nullable();
            $table->string('english_name', 255)->nullable()->index();
            $table->string('french_name', 255)->nullable();
            $table->string('spanish_name', 255)->nullable();
            $table->string('family', 100)->nullable();
            $table->string('higher_taxa', 150)->nullable();
            $table->string('taxon_level', 30)->nullable();
            $table->boolean('is_statistical_item')->default(false);
            $table->string('fao_version', 20)->nullable();
            $table->string('fao_source', 255)->nullable();
            $table->foreignId('import_batch_id')->nullable()->constrained('reference_imports')->restrictOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fao_asfis_species');
    }
};

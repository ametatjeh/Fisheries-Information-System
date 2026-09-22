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
        Schema::table('species', function (Blueprint $table) {
            $table->foreignId('fao_asfis_species_id')
                ->nullable()
                ->after('id')
                ->constrained('fao_asfis_species')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('species', function (Blueprint $table) {
            $table->dropForeign(['fao_asfis_species_id']);
            $table->dropColumn('fao_asfis_species_id');
        });
    }
};

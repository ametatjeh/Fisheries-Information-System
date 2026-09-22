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
        Schema::table('fao_asfis_species', function (Blueprint $table) {
            $table->foreign('isscaap_code')
                ->references('isscaap_code')
                ->on('fao_isscaap_groups')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fao_asfis_species', function (Blueprint $table) {
            $table->dropForeign(['isscaap_code']);
        });
    }
};

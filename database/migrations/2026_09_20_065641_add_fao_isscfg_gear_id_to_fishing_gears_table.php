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
        Schema::table('fishing_gears', function (Blueprint $table) {
            $table->foreignId('fao_isscfg_gear_id')
                ->nullable()
                ->after('id')
                ->constrained('fao_isscfg_gears')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fishing_gears', function (Blueprint $table) {
            $table->dropForeign(['fao_isscfg_gear_id']);
            $table->dropColumn('fao_isscfg_gear_id');
        });
    }
};

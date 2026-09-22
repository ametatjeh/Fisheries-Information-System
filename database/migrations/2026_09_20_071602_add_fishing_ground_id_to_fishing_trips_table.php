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
        Schema::table('fishing_trips', function (Blueprint $table) {
            $table->foreignId('wppnri_id')->nullable()->after('primary_gear_id')->constrained('wppnri')->nullOnDelete();
            $table->foreignId('fishing_ground_id')->nullable()->after('wppnri_id')->constrained('fishing_grounds')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fishing_trips', function (Blueprint $table) {
            $table->dropForeign(['wppnri_id']);
            $table->dropForeign(['fishing_ground_id']);
            $table->dropColumn(['wppnri_id', 'fishing_ground_id']);
        });
    }
};

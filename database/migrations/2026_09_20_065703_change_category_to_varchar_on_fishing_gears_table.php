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
            $table->string('category', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fishing_gears', function (Blueprint $table) {
            DB::statement("ALTER TABLE fishing_gears MODIFY category ENUM('jaring_lingkar','jaring_tarik','jaring_hela','jaring_angkat','alat_jatuh','jaring_insang','perangkap','pancing','alat_penjepit_melukai','lainnya') NOT NULL");
        });
    }
};

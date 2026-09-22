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
            $table->string('local_name_id', 100)
                ->nullable()
                ->comment('Nama umum Indonesia (misal Cakalang)')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safety check before rolling back
        $nullCount = DB::table('species')->whereNull('local_name_id')->count();
        if ($nullCount > 0) {
            throw new Exception("Rollback aborted: {$nullCount} records have NULL local_name_id. Cannot revert to NOT NULL without data loss.");
        }

        Schema::table('species', function (Blueprint $table) {
            $table->string('local_name_id', 100)
                ->nullable(false)
                ->comment('Nama umum Indonesia (misal Cakalang)')
                ->change();
        });
    }
};

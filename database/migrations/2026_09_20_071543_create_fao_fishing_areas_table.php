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
        Schema::create('fao_fishing_areas', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name_en');
            $table->boolean('is_active')->default(true);
            $table->foreignId('import_batch_id')->nullable()->constrained('reference_imports')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fao_fishing_areas');
    }
};

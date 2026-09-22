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
        Schema::create('fao_isscfg_gears', function (Blueprint $table) {
            $table->id();
            $table->string('isscfg_code', 20)->unique();
            $table->string('standard_abbreviation', 10)->nullable()->index();
            $table->string('name_en', 150);
            $table->string('name_id', 150)->nullable();

            // Self-referencing FK for hierarchy
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('fao_isscfg_gears')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('level')->default(3)->index();
            $table->text('description')->nullable();
            $table->string('fao_version', 50)->nullable();
            $table->boolean('is_active')->default(true);

            // Link to the import batch tracker
            $table->foreignId('import_batch_id')
                ->nullable()
                ->constrained('reference_imports')
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fao_isscfg_gears');
    }
};

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
        Schema::create('fao_isscaap_groups', function (Blueprint $table) {
            $table->id();
            $table->char('isscaap_code', 2)->unique();
            $table->string('name_en', 150);
            $table->string('name_id', 150)->nullable();
            $table->text('description')->nullable();
            $table->string('fao_version', 50)->nullable();
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
        Schema::dropIfExists('fao_isscaap_groups');
    }
};

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
        Schema::create('reference_imports', function (Blueprint $table) {
            $table->id();
            $table->string('reference_type', 50)->index();
            $table->string('source', 50);
            $table->string('source_version', 30);
            $table->timestamp('imported_at')->nullable();
            $table->unsignedInteger('total_records')->default(0);
            $table->unsignedInteger('total_inserted')->default(0);
            $table->unsignedInteger('total_updated')->default(0);
            $table->unsignedInteger('total_deactivated')->default(0);
            $table->string('status', 20);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reference_imports');
    }
};

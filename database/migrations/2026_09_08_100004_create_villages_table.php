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
        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->string('code', 20)->unique()->comment('Kode resmi kelurahan/desa/gampong');
            $table->string('name', 100)->comment('Nama desa/kelurahan');
            $table->string('postal_code', 10)->nullable()->comment('Kode pos jika ada');
            $table->timestamps();

            $table->index(['district_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};

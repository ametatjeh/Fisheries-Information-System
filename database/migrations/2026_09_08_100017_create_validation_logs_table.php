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
        Schema::create('validation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fishing_trip_id')->constrained('fishing_trips')->cascadeOnDelete()->comment('Trip yang divalidasi');
            $table->foreignId('validator_id')->constrained('users')->cascadeOnDelete()->comment('User validator yang melakukan aksi');
            $table->string('from_status', 30)->comment('Status sebelum perubahan');
            $table->string('to_status', 30)->comment('Status sesudah perubahan');
            $table->text('notes')->nullable()->comment('Catatan/keterangan verifikasi atau alasan perbaikan');
            $table->timestamps();

            $table->index('fishing_trip_id');
            $table->index('validator_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validation_logs');
    }
};

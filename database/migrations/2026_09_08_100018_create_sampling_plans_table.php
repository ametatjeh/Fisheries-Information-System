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
        Schema::create('sampling_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique()->comment('Kode rencana sampling (misal SMP-2026-001)');
            $table->string('title', 150)->comment('Nama program / judul rencana sampling');
            $table->foreignId('landing_site_id')->constrained('landing_sites')->cascadeOnDelete()->comment('Lokasi TPI sasaran sampling');
            $table->foreignId('target_species_id')->nullable()->constrained('fish_species')->nullOnDelete()->comment('Target spesies prioritas jika spesifik');
            $table->date('start_date')->comment('Tanggal mulai periode rencana');
            $table->date('end_date')->comment('Tanggal selesai periode rencana');
            $table->unsignedInteger('target_sample_size')->default(100)->comment('Target jumlah sampel spesimen');
            $table->enum('sampling_method', [
                'random',
                'stratified',
                'systematic',
            ])->default('stratified')->comment('Metode penarikan sampel statistik');
            $table->enum('status', [
                'planned',
                'active',
                'completed',
                'cancelled',
            ])->default('planned')->comment('Status siklus program sampling');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('landing_site_id');
            $table->index('target_species_id');
            $table->index(['start_date', 'end_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sampling_plans');
    }
};

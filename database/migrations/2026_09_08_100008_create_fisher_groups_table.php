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
        Schema::create('fisher_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique()->nullable()->comment('Kode registrasi KUB/kelompok nelayan');
            $table->string('name', 150)->comment('Nama Kelompok Usaha Bersama (KUB) / Nelayan');
            $table->foreignId('regency_id')->constrained('regencies')->cascadeOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->string('leader_name', 100)->nullable()->comment('Nama ketua kelompok');
            $table->string('phone', 30)->nullable()->comment('Nomor kontak kelompok/ketua');
            $table->text('address')->nullable()->comment('Sekretariat / alamat');
            $table->date('established_date')->nullable()->comment('Tanggal pengukuhan / berdiri');
            $table->unsignedInteger('total_members')->default(0)->comment('Jumlah anggota terdaftar');
            $table->timestamps();

            $table->index(['regency_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fisher_groups');
    }
};

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
        Schema::create('fishers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Akun login jika nelayan terdaftar sebagai user');
            $table->string('nik', 20)->unique()->comment('Nomor Induk Kependudukan (KTP)');
            $table->string('kusuka_number', 50)->unique()->nullable()->comment('Nomor Kartu Pelaku Usaha Kelautan dan Perikanan');
            $table->string('name', 150)->comment('Nama lengkap nelayan');
            $table->enum('gender', ['L', 'P'])->default('L')->comment('Jenis kelamin');
            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone', 30)->nullable();
            $table->foreignId('province_id')->constrained('provinces')->cascadeOnDelete();
            $table->foreignId('regency_id')->constrained('regencies')->cascadeOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->text('address')->nullable();
            $table->foreignId('fisher_group_id')->nullable()->constrained('fisher_groups')->nullOnDelete();
            $table->enum('fisher_type', [
                'pemilik',
                'nahkoda_jurumudi',
                'abk',
                'nelayan_tanpa_perahu',
            ])->default('abk')->comment('Klasifikasi peran nelayan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index('regency_id');
            $table->index('fisher_group_id');
            $table->index('fisher_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishers');
    }
};

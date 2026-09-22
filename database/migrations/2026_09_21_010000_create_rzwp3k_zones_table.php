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
        Schema::create('rzwp3k_zones', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('parent_code', 50)->nullable()->index();
            $table->string('name');
            $table->string('zone_type', 50)->index(); // 'KPU', 'KK', 'KSNT', 'AL'
            $table->string('subzone_type', 50)->index(); // 'KPU-PT', 'KPU-PB', 'KK-KKP', etc.
            $table->text('description')->nullable();
            $table->foreignId('regency_id')->nullable()->constrained('regencies')->nullOnDelete();
            $table->decimal('area_ha', 14, 2)->nullable();
            $table->string('source', 100)->default('DKP Aceh');
            $table->string('source_document', 255)->default('Qanun Aceh No. 1 Tahun 2020');
            $table->string('legal_basis', 255)->default('Qanun Aceh Nomor 1 Tahun 2020 tentang RZWP3K Aceh 2020-2040');
            $table->date('valid_from')->default('2020-01-13');
            $table->date('valid_until')->default('2040-01-13');
            $table->string('status', 50)->default('legal_active')->index();
            $table->json('metadata')->nullable();
            $table->json('geometry')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rzwp3k_zones');
    }
};

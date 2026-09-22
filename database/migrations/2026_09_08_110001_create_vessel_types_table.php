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
        Schema::create('vessel_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Kode tipe armada kapal (misal: PTM, PMT, KM_05, KM_30)');
            $table->string('name', 100)->comment('Nama klasifikasi tipe kapal');
            $table->enum('category', [
                'tanpa_motor',
                'motor_tempel',
                'kapal_motor',
            ])->default('kapal_motor')->comment('Kategori umum armada penangkapan');
            $table->string('tonnage_range', 50)->nullable()->comment('Rentang tonase kotor (Gross Tonnage / GT)');
            $table->text('description')->nullable()->comment('Deskripsi spesifikasi armada kapal');
            $table->boolean('is_active')->default(true)->comment('Status aktif dalam sistem');
            $table->timestamps();

            $table->index('code');
            $table->index('category');
            $table->index('is_active');
        });

        // Hubungkan foreign key ke tabel vessels jika belum ada
        if (Schema::hasTable('vessels') && ! Schema::hasColumn('vessels', 'vessel_type_id')) {
            Schema::table('vessels', function (Blueprint $table) {
                $table->foreignId('vessel_type_id')->nullable()->after('owner_id')->constrained('vessel_types')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('vessels') && Schema::hasColumn('vessels', 'vessel_type_id')) {
            Schema::table('vessels', function (Blueprint $table) {
                $table->dropConstrainedForeignId('vessel_type_id');
            });
        }
        Schema::dropIfExists('vessel_types');
    }
};

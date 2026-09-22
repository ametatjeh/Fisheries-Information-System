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
        Schema::create('landings', function (Blueprint $table) {
            $table->id();
            $table->string('landing_number', 50)->unique()->comment('Nomor transaksi pembongkaran/pendaratan (misal LND-202609-0001)');
            $table->foreignId('fishing_trip_id')->nullable()->constrained('fishing_trips')->nullOnDelete()->comment('Trip asal pendaratan');
            $table->foreignId('landing_site_id')->constrained('landing_sites')->cascadeOnDelete()->comment('TPI tempat ikan dibongkar');
            $table->dateTime('landing_date')->comment('Waktu pembongkaran ikan');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete()->comment('Petugas pencatat');
            $table->decimal('total_weight_kg', 12, 2)->default(0)->comment('Akumulasi berat total pendaratan (kg)');
            $table->decimal('total_value_rp', 15, 2)->default(0)->comment('Nilai total transaksi pelelangan/pendaratan (Rp)');
            $table->unsignedSmallInteger('buyer_count')->nullable()->comment('Jumlah bakul / pembeli');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('landing_site_id');
            $table->index('landing_date');
            $table->index('fishing_trip_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landings');
    }
};

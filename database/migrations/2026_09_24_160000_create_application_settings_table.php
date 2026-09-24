<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('application_settings', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->string('organization_type')->nullable();
            $table->string('logo_path')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->timestamps();
        });

        // Insert default initial organization identity (idempotent)
        DB::table('application_settings')->insertOrIgnore([
            'id' => 1,
            'organization_name' => 'Dinas Kelautan dan Perikanan Aceh',
            'organization_type' => 'Instansi',
            'logo_path' => null,
            'address' => null,
            'website' => null,
            'email' => null,
            'phone' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_settings');
    }
};

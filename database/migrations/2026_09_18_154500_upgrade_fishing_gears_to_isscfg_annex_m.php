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
        Schema::table('fishing_gears', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('id')
                ->constrained('fishing_gears')
                ->nullOnDelete();

            $table->string('isscfg_code', 20)->nullable()->index()->after('code');
            $table->string('standard_abbreviation', 10)->nullable()->index()->after('isscfg_code');
            $table->string('name_en', 150)->nullable()->after('name');
            $table->string('name_id', 150)->nullable()->after('name_en');
            $table->string('local_name', 150)->nullable()->after('name_id');
            $table->unsignedTinyInteger('level')->default(3)->index()->after('category');
            $table->string('source', 20)->default('LOCAL')->index()->after('level');
            $table->integer('sort_order')->default(0)->after('is_active');

            $table->index(['source', 'isscfg_code'], 'fishing_gears_source_isscfg_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fishing_gears', function (Blueprint $table) {
            if (Schema::hasIndex('fishing_gears', 'fishing_gears_source_isscfg_idx')) {
                $table->dropIndex('fishing_gears_source_isscfg_idx');
            }
            if (Schema::hasColumn('fishing_gears', 'parent_id')) {
                $table->dropForeign(['parent_id']);
            }
            $table->dropColumn([
                'parent_id',
                'isscfg_code',
                'standard_abbreviation',
                'name_en',
                'name_id',
                'local_name',
                'level',
                'source',
                'sort_order',
            ]);
        });
    }
};

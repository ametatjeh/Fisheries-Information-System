<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'gfw';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('gfw')->create('gfw_vessel_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('parent_type', 50)->nullable()->index();
            $table->text('description')->nullable();
            $table->string('source', 50)->default('GFW');
            $table->string('source_version', 50)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('gfw')->dropIfExists('gfw_vessel_types');
    }
};

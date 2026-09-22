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
        Schema::connection('gfw')->create('gfw_sync_runs', function (Blueprint $table) {
            $table->id();
            $table->string('aoi', 50)->default('zee-indonesia-aceh')->index();
            $table->date('date_from');
            $table->date('date_to');
            $table->string('dataset', 100);
            $table->string('endpoint', 150);
            $table->unsignedInteger('records_found')->default(0);
            $table->unsignedInteger('records_saved')->default(0);
            $table->enum('status', ['running', 'success', 'partial', 'failed'])->default('running')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('gfw')->dropIfExists('gfw_sync_runs');
    }
};

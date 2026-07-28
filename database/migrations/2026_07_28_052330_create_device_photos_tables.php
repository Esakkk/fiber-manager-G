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
        Schema::create('pop_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pop_id')->constrained('pop')->cascadeOnDelete();
            $table->string('filename', 255);
            $table->string('original_name', 255);
            $table->integer('file_size');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('olt_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('olt')->cascadeOnDelete();
            $table->string('filename', 255);
            $table->string('original_name', 255);
            $table->integer('file_size');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('odc_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odc_id')->constrained('odc')->cascadeOnDelete();
            $table->string('filename', 255);
            $table->string('original_name', 255);
            $table->integer('file_size');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('odp_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odp_id')->constrained('odp')->cascadeOnDelete();
            $table->string('filename', 255);
            $table->string('original_name', 255);
            $table->integer('file_size');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('port_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('port_id')->constrained('odp_ports')->cascadeOnDelete();
            $table->string('filename', 255);
            $table->string('original_name', 255);
            $table->integer('file_size');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('port_photos');
        Schema::dropIfExists('odp_photos');
        Schema::dropIfExists('odc_photos');
        Schema::dropIfExists('olt_photos');
        Schema::dropIfExists('pop_photos');
    }
};

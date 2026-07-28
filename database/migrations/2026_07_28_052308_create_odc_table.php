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
        Schema::create('odc', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->string('location', 255);
            $table->integer('capacity')->default(24);
            $table->integer('used_ports')->default(0);
            $table->text('description')->nullable();
            $table->enum('source_type', ['pop', 'olt', 'pon'])->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('pon_id')->nullable();
            $table->integer('pon_port_number')->nullable();
            $table->unsignedBigInteger('olt_id')->nullable();
            $table->longText('path_coordinates')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id'], 'idx_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odc');
    }
};

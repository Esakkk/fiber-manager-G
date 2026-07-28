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
        Schema::create('olt_ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('olt')->cascadeOnDelete();
            $table->integer('port_number');
            $table->enum('status', ['available', 'used', 'maintenance'])->default('available');
            $table->unsignedBigInteger('target_odc_id')->nullable()->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['olt_id', 'port_number'], 'unique_olt_port');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('olt_ports');
    }
};

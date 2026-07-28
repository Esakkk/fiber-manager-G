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
        Schema::create('pon_ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pon_id')->constrained('pon')->cascadeOnDelete();
            $table->integer('port_number');
            $table->enum('status', ['available', 'used', 'maintenance'])->default('available');
            $table->unsignedBigInteger('target_odc_id')->nullable()->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['pon_id', 'port_number'], 'unique_pon_port');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pon_ports');
    }
};

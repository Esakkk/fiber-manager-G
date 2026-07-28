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
        Schema::create('pon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('olt')->cascadeOnDelete();
            $table->integer('card_number');
            $table->string('name', 100)->nullable();
            $table->integer('port_count')->default(8);
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['olt_id', 'card_number'], 'unique_olt_card');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pon');
    }
};

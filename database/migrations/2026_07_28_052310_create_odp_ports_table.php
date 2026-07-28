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
        Schema::create('odp_ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odp_id')->constrained('odp')->cascadeOnDelete();
            $table->integer('port_number');
            $table->enum('status', ['available', 'used', 'maintenance'])->default('available');
            $table->string('target', 255)->nullable();
            $table->enum('connection_type', ['feeder', 'distribusi', 'drop'])->nullable();
            $table->integer('target_port')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->string('onu_number', 50)->nullable();
            $table->string('modem_type', 100)->nullable();
            $table->text('description')->nullable();
            $table->boolean('has_photo')->default(false);
            $table->text('path_coordinates')->nullable();
            $table->timestamps();

            $table->unique(['odp_id', 'port_number'], 'unique_odp_port');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odp_ports');
    }
};

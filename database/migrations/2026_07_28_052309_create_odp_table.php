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
        Schema::create('odp', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('source_id')->nullable()->constrained('odc')->nullOnDelete();
            $table->integer('port_number_in_odc')->nullable();
            $table->enum('source_type', ['odc', 'odp'])->nullable();
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->longText('path_coordinates')->nullable();
            $table->string('location', 255);
            $table->integer('total_ports')->default(8);
            $table->integer('available_ports')->default(8);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odp');
    }
};

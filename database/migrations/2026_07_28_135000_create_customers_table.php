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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->string('onu_number', 50)->nullable();
            $table->string('modem_type', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('odp_id')->nullable()->constrained('odp')->nullOnDelete();
            $table->integer('port_number')->nullable();
            $table->text('path_coordinates')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

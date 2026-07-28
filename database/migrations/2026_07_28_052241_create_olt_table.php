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
        Schema::create('olt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pop_id')->constrained('pop')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('model', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->integer('management_port')->default(22);
            $table->integer('total_ports')->default(16);
            $table->integer('total_pon_ports')->default(16);
            $table->integer('used_pon_ports')->default(0);
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->string('location', 255)->nullable();
            $table->text('description')->nullable();
            $table->boolean('has_photo')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('olt');
    }
};

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
        Schema::create('pole', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index();
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->string('location', 255);
            $table->text('description')->nullable();
            $table->string('jenis_tiang', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pole');
    }
};

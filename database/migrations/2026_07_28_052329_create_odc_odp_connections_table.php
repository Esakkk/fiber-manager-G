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
        Schema::create('odc_odp_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odc_id')->constrained('odc')->cascadeOnDelete();
            $table->foreignId('odp_id')->constrained('odp')->cascadeOnDelete();
            $table->integer('port_number')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['odc_id', 'odp_id'], 'unique_connection');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odc_odp_connections');
    }
};

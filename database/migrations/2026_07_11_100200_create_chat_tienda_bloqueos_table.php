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
        Schema::create('chat_tienda_bloqueos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bloqueador_id');
            $table->unsignedBigInteger('bloqueado_id');
            $table->string('motivo_reporte')->nullable();
            $table->timestamps();

            $table->unique(['bloqueador_id', 'bloqueado_id']);
            $table->index('bloqueado_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_tienda_bloqueos');
    }
};

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
        Schema::create('chats_tienda', function (Blueprint $table) {
            $table->id();
            // El par se guarda normalizado (menor, mayor) para que el UNIQUE
            // garantice una sola conversación por pareja de afiliados.
            $table->unsignedBigInteger('afiliado_menor_id');
            $table->unsignedBigInteger('afiliado_mayor_id');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique(['afiliado_menor_id', 'afiliado_mayor_id']);
            $table->index('afiliado_mayor_id');
            $table->index('last_message_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats_tienda');
    }
};

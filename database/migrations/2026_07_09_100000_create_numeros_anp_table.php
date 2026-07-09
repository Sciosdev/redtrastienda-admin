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
        Schema::create('numeros_anp', function (Blueprint $table) {
            $table->id();
            $table->string('numero_anp', 50)->unique();
            $table->string('estatus')->default('disponible');
            $table->unsignedBigInteger('afiliado_asignado')->nullable();
            $table->timestamp('fecha_generacion')->useCurrent();
            $table->timestamp('fecha_activacion')->nullable();
            $table->string('operador')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('estatus');
            $table->index('afiliado_asignado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('numeros_anp');
    }
};

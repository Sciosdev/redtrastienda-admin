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
        Schema::create('mercado_reportes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('publicacion_id');
            $table->unsignedBigInteger('reporter_id');
            $table->string('motivo')->nullable();
            $table->timestamps();

            // El UNIQUE hace idempotente el reporte por (publicación, reportante):
            // el correo solo se manda cuando la fila es nueva.
            $table->unique(['publicacion_id', 'reporter_id']);
            $table->index('reporter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mercado_reportes');
    }
};

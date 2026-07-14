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
        Schema::create('mercado_publicaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // producto|aviso validado en request, no enum de BD (evita ALTER futuros).
            $table->string('tipo', 20);
            $table->string('titulo', 120);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2)->nullable();
            $table->string('unidad', 30)->nullable();
            $table->string('foto')->nullable();
            $table->boolean('es_oferta')->default(false);
            // Oferta vencida deja de destacarse, no se borra.
            $table->date('vigencia_hasta')->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('oculto_por_admin')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index(['oculto_por_admin', 'activo', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mercado_publicaciones');
    }
};

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
        Schema::create('affiliate_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->unique();
            $table->string('numero_anp', 50)->unique();
            $table->string('nombre_negocio')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('direccion')->nullable();
            $table->string('estado')->nullable();
            $table->string('municipio')->nullable();
            $table->string('colonia')->nullable();
            $table->string('foto_negocio')->nullable();
            $table->string('estatus')->default('pendiente');
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamps();

            $table->index('estatus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_profiles');
    }
};

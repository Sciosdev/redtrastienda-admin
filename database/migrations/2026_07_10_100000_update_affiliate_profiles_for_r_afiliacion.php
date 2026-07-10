<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * R-Afiliación: la afiliación vive en el sistema de ANPEC.
     * - numero_anp pasa a nullable: los leads registran cuenta sin número
     *   (el UNIQUE existente admite múltiples NULL en MySQL).
     * - reclamada/fecha_reclamo: cuentas precargadas nacen sin reclamar; la
     *   activación las marca y las re-importaciones JAMÁS pisan reclamadas.
     * - telefono_contacto: teléfono del Excel de ANPEC (puede traer dos números
     *   separados por "/", por eso 50 chars); whatsapp queda para lo que ya es.
     * - datos_importacion: resto de columnas del Excel que no mapean a campos.
     */
    public function up(): void
    {
        Schema::table('affiliate_profiles', function (Blueprint $table) {
            // En Laravel 12 change() re-declara la columna completa: tipo y
            // longitud deben repetirse o se pierden.
            $table->string('numero_anp', 50)->nullable()->change();
        });

        Schema::table('affiliate_profiles', function (Blueprint $table) {
            $table->string('telefono_contacto', 50)->nullable()->after('whatsapp');
            $table->boolean('reclamada')->default(0)->index()->after('estatus');
            $table->timestamp('fecha_reclamo')->nullable()->after('reclamada');
            $table->json('datos_importacion')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_profiles', function (Blueprint $table) {
            $table->dropColumn(['telefono_contacto', 'reclamada', 'fecha_reclamo', 'datos_importacion']);
        });

        Schema::table('affiliate_profiles', function (Blueprint $table) {
            $table->string('numero_anp', 50)->nullable(false)->change();
        });
    }
};

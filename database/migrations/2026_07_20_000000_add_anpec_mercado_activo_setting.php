<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * R-Mercado remoto: interruptor del Mercado en business_settings. La app lee
 * `anpec_mercado_activo` en /api/v1/config y muestra/oculta la pestaña Mercado
 * sin recompilar (toggle en el panel de moderación del Mercado). Default 0:
 * el AAB de review viaja con el Mercado oculto; se prende el día de la expo.
 *
 * Insert-only a propósito: un updateOrInsert pisaría el valor a 0 si el deploy
 * se re-corre con el toggle ya prendido (en plena expo).
 */
return new class extends Migration {
    public function up(): void
    {
        if (!DB::table('business_settings')->where('type', 'anpec_mercado_activo')->exists()) {
            DB::table('business_settings')->insert([
                'type' => 'anpec_mercado_activo',
                'value' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('business_settings')->where('type', 'anpec_mercado_activo')->delete();
    }
};

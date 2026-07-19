<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Fix de datos: los shops de proveedores creados manualmente (Coca-Cola, Bimbo)
 * quedaron con slug = 'en' (el código de idioma se coló como slug al crearlos),
 * y ADEMÁS duplicado entre ambos. La app pide los productos del proveedor por
 * `/api/v1/seller/{slug}/products`, así que con slug 'en' duplicado la lista
 * "Todos los productos" sale VACÍA y el detalle resuelve el shop equivocado.
 *
 * Este fix reasigna un slug único y legible a cada shop con slug inválido
 * (vacío, 'en', o duplicado), derivándolo del nombre del shop. Idempotente:
 * si un shop ya tiene slug propio y único, no se toca.
 */
return new class extends Migration {
    public function up(): void
    {
        $shops = DB::table('shops')->select('id', 'name', 'slug', 'seller_id')->get();
        $usados = [];

        // Primero registra los slugs válidos ya existentes para no colisionar.
        foreach ($shops as $shop) {
            $slug = (string) $shop->slug;
            if ($slug !== '' && $slug !== 'en' && !in_array($slug, $usados, true)) {
                $usados[] = $slug;
            }
        }

        foreach ($shops as $shop) {
            $slug = (string) $shop->slug;

            // Solo reasigna si el slug es inválido ('en', vacío) o está duplicado.
            $esDuplicado = DB::table('shops')->where('slug', $slug)->count() > 1;
            if ($slug === '' || $slug === 'en' || $esDuplicado) {
                $base = Str::slug($shop->name);
                if ($base === '') {
                    $base = 'proveedor-' . $shop->id;
                }
                $nuevo = $base;
                $i = 2;
                while (in_array($nuevo, $usados, true) || DB::table('shops')->where('slug', $nuevo)->where('id', '!=', $shop->id)->exists()) {
                    $nuevo = $base . '-' . $i;
                    $i++;
                }
                DB::table('shops')->where('id', $shop->id)->update(['slug' => $nuevo]);
                $usados[] = $nuevo;
            }
        }
    }

    public function down(): void
    {
        // Fix de datos de un solo sentido; no se revierte (el slug 'en' era un bug).
    }
};

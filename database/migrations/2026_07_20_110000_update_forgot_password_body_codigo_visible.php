<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ajuste de copy: el correo del reset ahora muestra el código de 6 dígitos
 * GRANDE en el cuerpo (blade forgot-password), así que la instrucción de
 * "sacar el código del enlace" sobra. Solo se actualiza si el body sigue
 * siendo el sembrado original (no pisa ediciones del admin).
 */
return new class extends Migration {
    public function up(): void
    {
        $bodySembrado = '<p>Hola {userName},</p><p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en Red Trastienda. Da clic en el enlace de abajo para crear una contraseña nueva.</p><p>En la app también puedes escribir el código de 6 dígitos que aparece al final del enlace (después de "token=").</p><p>Si no solicitaste este cambio, ignora este correo.</p>';
        $bodyNuevo = '<p>Hola {userName},</p><p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en Red Trastienda.</p><p>Escribe en la app el <strong>código de 6 dígitos</strong> que ves abajo, o da clic en el enlace para crear tu contraseña nueva desde el navegador.</p><p>Si no solicitaste este cambio, ignora este correo.</p>';

        DB::table('email_templates')
            ->where('user_type', 'customer')
            ->where('template_name', 'forgot-password')
            ->where('body', $bodySembrado)
            ->update(['body' => $bodyNuevo, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Ajuste de copy; no se revierte.
    }
};

<?php

use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix de datos: la tabla email_templates está VACÍA para el tipo customer — la
 * instalación no la sembró y el trait de envío hace silencio si la plantilla no
 * existe (EmailTemplateTrait::sendingMail `if ($template)`), así que NINGÚN
 * correo al afiliado salía (reset de contraseña, pedidos, registro). La página
 * del panel que las auto-crearía da 500, de ahí esta siembra directa.
 *
 * La fila crítica (forgot-password) se inserta con contenido propio en español
 * y SIN depender de EmailTemplateService (sospechoso del 500). El resto de la
 * lista de customer se intenta vía el servicio, tolerante a fallos: una
 * plantilla problemática no debe tumbar el deploy.
 */
return new class extends Migration {
    public function up(): void
    {
        $now = now();

        if (!DB::table('email_templates')->where('user_type', 'customer')->where('template_name', 'forgot-password')->exists()) {
            DB::table('email_templates')->insert([
                'template_name' => 'forgot-password',
                'user_type' => 'customer',
                'template_design_name' => 'forgot-password',
                'title' => 'Restablecer tu contraseña',
                'body' => '<p>Hola {userName},</p><p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en Red Trastienda. Da clic en el enlace de abajo para crear una contraseña nueva.</p><p>En la app también puedes escribir el código de 6 dígitos que aparece al final del enlace (después de "token=").</p><p>Si no solicitaste este cambio, ignora este correo.</p>',
                'button_name' => 'Cambiar contraseña',
                'footer_text' => 'Si necesitas ayuda, contáctanos. Siempre es un gusto ayudarte.',
                'copyright_text' => 'Copyright ' . date('Y') . ' ANPEC Red Trastienda. Todos los derechos reservados.',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        try {
            $service = new EmailTemplateService();
            foreach ($service->getEmailTemplateData(userType: 'customer') as $templateName) {
                try {
                    $exists = DB::table('email_templates')->where('user_type', 'customer')->where('template_name', $templateName)->exists();
                    if (!$exists) {
                        // El modelo (no DB::table) para que los casts json de
                        // hide_field/pages/social_media serialicen bien.
                        EmailTemplate::create($service->getAddData(
                            userType: 'customer',
                            templateName: $templateName,
                            hideField: $service->getHiddenField(userType: 'customer', templateName: $templateName),
                            title: $service->getTitleData(userType: 'customer', templateName: $templateName),
                            body: $service->getBodyData(userType: 'customer', templateName: $templateName),
                        ));
                    }
                } catch (\Throwable $e) {
                    continue;
                }
            }
        } catch (\Throwable $e) {
            // La fila crítica (forgot-password) ya quedó arriba; el resto puede
            // sembrarse después desde el panel cuando su página se repare.
        }
    }

    public function down(): void
    {
        // Siembra de datos; no se revierte.
    }
};

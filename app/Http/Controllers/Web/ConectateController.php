<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ConectateController extends Controller
{
    public function index(): View
    {
        $this->forceSpanishLocale();
        $companyPhone = (string)(getWebConfig(name: 'company_phone') ?? '');
        return view('anpec.conectate', [
            'companyName' => (string)(getWebConfig(name: 'company_name') ?? ''),
            'companyEmail' => (string)(getWebConfig(name: 'company_email') ?? ''),
            'companyPhone' => $companyPhone,
            'companyLogoPath' => $this->getConfigImagePath(name: 'company_web_logo'),
            'companyFavIconPath' => $this->getConfigImagePath(name: 'company_fav_icon'),
            'whatsappNumber' => $this->getWhatsappNumber(phone: $companyPhone),
        ]);
    }

    public function getPrivacyPolicyView(): View
    {
        $this->forceSpanishLocale();
        return view('anpec.politica-privacidad', [
            'companyName' => (string)(getWebConfig(name: 'company_name') ?? ''),
            'companyEmail' => (string)(getWebConfig(name: 'company_email') ?? ''),
            'companyLogoPath' => $this->getConfigImagePath(name: 'company_web_logo'),
            'companyFavIconPath' => $this->getConfigImagePath(name: 'company_fav_icon'),
        ]);
    }

    /**
     * Estas páginas públicas son en español por diseño. translate() resuelve el
     * idioma con getDefaultLanguage() (sesión 'local' → default del storefront,
     * hoy 'en'): sin esto, un visitante sin sesión vería las claves humanizadas
     * en lugar del contenido.
     */
    private function forceSpanishLocale(): void
    {
        session(['local' => 'es']);
    }

    private function getConfigImagePath(string $name): string
    {
        $config = getWebConfig(name: $name);
        return is_array($config) ? (string)($config['path'] ?? '') : '';
    }

    /**
     * Mismo criterio que el canal WhatsApp F5 de la app: solo dígitos y,
     * si el número viene local (10 dígitos), se asume México (52).
     */
    private function getWhatsappNumber(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        return strlen($digits) === 10 ? '52' . $digits : $digits;
    }
}

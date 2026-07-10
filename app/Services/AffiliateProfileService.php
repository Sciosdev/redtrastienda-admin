<?php

namespace App\Services;

use App\Contracts\Repositories\AffiliateProfileRepositoryInterface;
use App\Contracts\Repositories\NumeroAnpRepositoryInterface;
use App\Models\NumeroAnp;
use App\Utils\ImageManager;
use Illuminate\Http\Request;

class AffiliateProfileService
{
    public function __construct(
        private readonly AffiliateProfileRepositoryInterface $affiliateProfileRepo,
        private readonly NumeroAnpRepositoryInterface         $numeroAnpRepo,
    )
    {
    }

    /**
     * Create the affiliate profile (status "pendiente") and consume the ANP number.
     * Must be called inside a DB transaction together with the user creation.
     */
    public function createProfileAndConsumeAnp(Request $request, object $user, NumeroAnp $numeroAnp): void
    {
        $fotoNegocio = null;
        if ($request->hasFile('foto_negocio')) {
            $fotoNegocio = ImageManager::upload(dir: 'affiliate/', format: 'webp', image: $request->file('foto_negocio'));
        }

        $this->affiliateProfileRepo->add([
            'customer_id' => $user->id,
            'numero_anp' => $numeroAnp->numero_anp,
            'nombre_negocio' => $request['nombre_negocio'] ?? null,
            'whatsapp' => $request['whatsapp'] ?? null,
            'direccion' => $request['direccion'] ?? null,
            'estado' => $request['estado'] ?? null,
            'municipio' => $request['municipio'] ?? null,
            'colonia' => $request['colonia'] ?? null,
            'foto_negocio' => $fotoNegocio,
            'estatus' => 'pendiente',
            // Quien se registra tecleando su ANP crea sus credenciales aquí
            // mismo: no hay nada que activar después (login por ANP directo).
            'reclamada' => 1,
            'fecha_reclamo' => now(),
        ]);

        $this->numeroAnpRepo->update(id: $numeroAnp->id, data: [
            'estatus' => 'usado',
            'afiliado_asignado' => $user->id,
            'fecha_activacion' => now(),
        ]);
    }

    /**
     * R-Lead: perfil de un interesado SIN número ANP. Nace pendiente y con
     * reclamada=1 (él creó sus credenciales); no puede iniciar sesión hasta que
     * el admin le asigne número. Must be called inside the user's transaction.
     */
    public function createLeadProfile(Request $request, object $user): void
    {
        $this->affiliateProfileRepo->add([
            'customer_id' => $user->id,
            'numero_anp' => null,
            'nombre_negocio' => $request['nombre_negocio'] ?? null,
            'whatsapp' => $request['whatsapp'] ?? null,
            'estatus' => 'pendiente',
            'reclamada' => 1,
            'fecha_reclamo' => now(),
        ]);
    }

    /**
     * Change an affiliate profile status. When approving, stamps approved_at/by.
     */
    public function changeStatus(string $id, string $estatus, ?string $adminName = null): bool
    {
        $data = ['estatus' => $estatus];
        if ($estatus === 'activo') {
            $data['approved_at'] = now();
            $data['approved_by'] = $adminName;
        }
        return $this->affiliateProfileRepo->update(id: $id, data: $data);
    }
}

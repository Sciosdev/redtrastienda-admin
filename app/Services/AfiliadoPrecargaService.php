<?php

namespace App\Services;

use App\Contracts\Repositories\AffiliateProfileRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\NumeroAnpRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * R-Precarga: creación de cuentas COMPLETAS de afiliados (usuario + perfil +
 * número ANP ligado) a partir del Excel de ANPEC, alta manual individual y
 * asignación de número a leads. La app nunca genera números: aquí solo se
 * reflejan los que ya existen en el sistema de ANPEC.
 */
class AfiliadoPrecargaService
{
    private const DOMINIO_EMAIL_SINTETICO = '@anpec.com.mx';

    public function __construct(
        private readonly NumeroAnpRepositoryInterface        $numeroAnpRepo,
        private readonly AffiliateProfileRepositoryInterface $affiliateProfileRepo,
        private readonly CustomerRepositoryInterface         $customerRepo,
    )
    {
    }

    /**
     * Process one chunk of already-validated Excel rows inside a transaction.
     * Rules (cerradas con ANPEC):
     * - Perfil reclamado => SKIP total, jamás se pisa.
     * - Perfil sin reclamar => solo se rellenan campos vacíos.
     * - Número existente 'disponible' sin perfil (import simple F3) => se adopta.
     * - Número 'bloqueado'/'cancelado' => NO se revive; 'usado' sin perfil => anomalía.
     *
     * @param iterable $rows Filas con encabezados del Excel real de ANPEC.
     * @return array<string,int> Contadores del chunk.
     */
    public function procesarChunk(iterable $rows): array
    {
        $resultado = [
            'creados' => 0,
            'actualizados' => 0,
            'sin_cambios' => 0,
            'saltados_reclamada' => 0,
            'saltados_bloqueado' => 0,
            'saltados_anomalia' => 0,
            'saltados_email_duplicado' => 0,
        ];

        $filas = [];
        foreach ($rows as $row) {
            $numero = strtoupper(trim((string)($row['username'] ?? '')));
            $filas[$numero] = $this->mapearFila(row: $row, numero: $numero);
        }
        if (empty($filas)) {
            return $resultado;
        }

        DB::transaction(function () use ($filas, &$resultado) {
            $numeros = array_keys($filas);
            $numerosExistentes = $this->numeroAnpRepo->getListWhereNumeroIn(numeros: $numeros)
                ->keyBy(fn($numero) => strtoupper($numero->numero_anp));
            $perfilesExistentes = $this->affiliateProfileRepo->getListWhereNumeroIn(numeros: $numeros, relations: ['customer'])
                ->keyBy(fn($perfil) => strtoupper($perfil->numero_anp));
            // Bulk lookup directo al modelo: el repositorio de customers no expone
            // consultas por lote y 18.5k add() individuales harían inviable el import.
            $emailsExistentes = User::whereIn('email', array_column($filas, 'email'))->pluck('id', 'email');

            $aCrear = [];
            $aAdoptar = [];
            foreach ($filas as $numero => $fila) {
                $perfil = $perfilesExistentes->get($numero);
                if ($perfil) {
                    if ($perfil->reclamada) {
                        $resultado['saltados_reclamada']++;
                        continue;
                    }
                    $huboCambio = $this->rellenarHuecos(perfil: $perfil, fila: $fila);
                    $resultado[$huboCambio ? 'actualizados' : 'sin_cambios']++;
                    continue;
                }

                $numeroRow = $numerosExistentes->get($numero);
                if ($numeroRow && $numeroRow->estatus !== 'disponible') {
                    // Bloqueado/cancelado a propósito por el admin: el import no lo
                    // revive. Un 'usado' sin perfil es una anomalía de datos: no tocar.
                    $resultado[in_array($numeroRow->estatus, ['bloqueado', 'cancelado']) ? 'saltados_bloqueado' : 'saltados_anomalia']++;
                    continue;
                }
                if (isset($emailsExistentes[$fila['email']])) {
                    $resultado['saltados_email_duplicado']++;
                    continue;
                }

                if ($numeroRow) {
                    $aAdoptar[$numero] = $fila;
                } else {
                    $aCrear[$numero] = $fila;
                }
            }

            $nuevas = $aCrear + $aAdoptar;
            if (!empty($nuevas)) {
                $this->crearCuentas(
                    filas: $nuevas,
                    numerosAdoptados: $numerosExistentes->only(array_keys($aAdoptar)),
                );
                $resultado['creados'] += count($nuevas);
            }
        });

        return $resultado;
    }

    /**
     * Alta manual individual (pedida por ANPEC): crea el mismo trío que el import.
     *
     * @return array{ok:bool, message:string}
     */
    public function altaManual(array $datos, ?string $operador): array
    {
        $numero = strtoupper(trim((string)$datos['numero_anp']));
        $email = strtolower(trim((string)($datos['email'] ?? '')));
        if ($email === '') {
            $email = strtolower($numero) . self::DOMINIO_EMAIL_SINTETICO;
        }

        $perfilExistente = $this->affiliateProfileRepo->getFirstWhere(params: ['numero_anp' => $numero]);
        if ($perfilExistente) {
            return ['ok' => false, 'message' => translate('ese_numero_ANP_ya_tiene_un_afiliado_ligado')];
        }
        $numeroRow = $this->numeroAnpRepo->getFirstWhere(params: ['numero_anp' => $numero]);
        if ($numeroRow && $numeroRow->estatus !== 'disponible') {
            return ['ok' => false, 'message' => translate('el_numero_ANP_no_esta_disponible') . ' (' . translate($numeroRow->estatus) . ')'];
        }
        if (User::where('email', $email)->exists()) {
            return ['ok' => false, 'message' => translate('ese_correo_ya_tiene_cuenta')];
        }

        DB::transaction(function () use ($datos, $numero, $email, $numeroRow, $operador) {
            $nombre = trim((string)$datos['nombre']);
            $user = $this->customerRepo->add([
                'name' => Str::limit($nombre, 80, ''),
                'f_name' => $nombre,
                'l_name' => '',
                'email' => $email,
                // phone es NOT NULL en users; el teléfono real va al perfil.
                'phone' => '',
                'password' => bcrypt(Str::random(40)),
                'referral_code' => strtoupper(Str::random(20)),
            ]);

            $this->affiliateProfileRepo->add([
                'customer_id' => $user->id,
                'numero_anp' => $numero,
                'nombre_negocio' => $datos['nombre_negocio'] ?? null,
                'telefono_contacto' => $datos['telefono'] ?? null,
                'direccion' => $datos['direccion'] ?? null,
                'estado' => $datos['estado'] ?? null,
                'estatus' => 'activo',
                'reclamada' => 0,
                'approved_at' => now(),
                'approved_by' => $operador ?? 'alta manual',
            ]);

            $datosNumero = [
                'estatus' => 'usado',
                'afiliado_asignado' => $user->id,
                'fecha_activacion' => now(),
                'operador' => $operador ?? 'alta manual',
            ];
            if ($numeroRow) {
                $this->numeroAnpRepo->update(id: $numeroRow->id, data: $datosNumero);
            } else {
                $this->numeroAnpRepo->add($datosNumero + ['numero_anp' => $numero, 'fecha_generacion' => now()]);
            }
        });

        return ['ok' => true, 'message' => translate('afiliado_creado_correctamente')];
    }

    /**
     * Assign an ANP number to a lead profile (numero_anp NULL). From that moment
     * the lead can sign in with the credentials they already created.
     *
     * @return array{ok:bool, message:string}
     */
    public function asignarNumeroALead(object $perfil, string $numeroInput, ?string $operador): array
    {
        if ($perfil->numero_anp !== null) {
            return ['ok' => false, 'message' => translate('este_afiliado_ya_tiene_numero_ANP')];
        }

        $numero = strtoupper(trim($numeroInput));
        if ($this->affiliateProfileRepo->getFirstWhere(params: ['numero_anp' => $numero])) {
            return ['ok' => false, 'message' => translate('ese_numero_ANP_ya_tiene_un_afiliado_ligado')];
        }
        $numeroRow = $this->numeroAnpRepo->getFirstWhere(params: ['numero_anp' => $numero]);
        if ($numeroRow && $numeroRow->estatus !== 'disponible') {
            return ['ok' => false, 'message' => translate('el_numero_ANP_no_esta_disponible') . ' (' . translate($numeroRow->estatus) . ')'];
        }

        DB::transaction(function () use ($perfil, $numero, $numeroRow, $operador) {
            $datosNumero = [
                'estatus' => 'usado',
                'afiliado_asignado' => $perfil->customer_id,
                'fecha_activacion' => now(),
                'operador' => $operador ?? 'asignación a lead',
            ];
            if ($numeroRow) {
                $this->numeroAnpRepo->update(id: $numeroRow->id, data: $datosNumero);
            } else {
                $this->numeroAnpRepo->add($datosNumero + ['numero_anp' => $numero, 'fecha_generacion' => now()]);
            }

            $this->affiliateProfileRepo->update(id: $perfil->id, data: [
                'numero_anp' => $numero,
                'estatus' => 'activo',
                'approved_at' => now(),
                'approved_by' => $operador,
            ]);
        });

        return ['ok' => true, 'message' => translate('numero_ANP_asignado_correctamente')];
    }

    /**
     * Map one Excel row to the internal structure. Values '-' count as empty.
     */
    private function mapearFila(mixed $row, string $numero): array
    {
        $nombre = $this->limpiarValor($row['firstname'] ?? '');
        $apellido = $this->limpiarValor($row['lastname'] ?? '');
        $email = strtolower(trim((string)($row['email'] ?? '')));
        if ($email === '' || $email === '-') {
            $email = strtolower($numero) . self::DOMINIO_EMAIL_SINTETICO;
        }

        $extras = [];
        foreach ([
            'id' => 'id_externo',
            'idnumber' => 'idnumber',
            'institution' => 'institution',
            'department' => 'department',
            'phone1' => 'phone1',
            'phone2' => 'phone2',
            'city' => 'city',
            'country' => 'country',
            'profile_field_validity' => 'validity',
            'profile_field_district' => 'district',
            'profile_field_semaphore' => 'semaphore',
            'profile_field_operator' => 'operator',
        ] as $columna => $clave) {
            $valor = $this->limpiarValor($row[$columna] ?? '');
            if ($valor !== null) {
                $extras[$clave] = $valor;
            }
        }
        $extras['email_origen'] = $email;

        return [
            'nombre' => trim($nombre . ($apellido !== null ? ' ' . $apellido : '')) ?: null,
            'email' => $email,
            'nombre_negocio' => $this->limpiarValor($row['profile_field_business_name'] ?? ''),
            'direccion' => $this->limpiarValor($row['profile_field_business_address'] ?? ''),
            'estado' => $this->limpiarValor($row['profile_field_state'] ?? ''),
            'telefono_contacto' => $this->limpiarTelefono($row['profile_field_phone'] ?? ''),
            'datos_importacion' => $extras,
        ];
    }

    private function limpiarValor(mixed $valor): ?string
    {
        $valor = trim((string)$valor);
        return ($valor === '' || $valor === '-') ? null : $valor;
    }

    private function limpiarTelefono(mixed $valor): ?string
    {
        $valor = $this->limpiarValor($valor);
        if ($valor === null || preg_replace('/\D+/', '', $valor) === '' || $valor === '0') {
            return null;
        }
        return Str::limit($valor, 50, '');
    }

    /**
     * Fill ONLY the empty fields of an unclaimed profile/user. Never touches
     * password or email. Returns true when something was actually written.
     */
    private function rellenarHuecos(object $perfil, array $fila): bool
    {
        $updatePerfil = [];
        foreach (['nombre_negocio', 'direccion', 'estado', 'telefono_contacto'] as $campo) {
            if (blank($perfil->{$campo}) && filled($fila[$campo])) {
                $updatePerfil[$campo] = $fila[$campo];
            }
        }
        if (blank($perfil->datos_importacion) && !empty($fila['datos_importacion'])) {
            // El update del repositorio va por Query Builder (sin casts): json manual.
            $updatePerfil['datos_importacion'] = json_encode($fila['datos_importacion'], JSON_UNESCAPED_UNICODE);
        }

        $huboCambio = false;
        if (!empty($updatePerfil)) {
            $this->affiliateProfileRepo->update(id: $perfil->id, data: $updatePerfil);
            $huboCambio = true;
        }

        $customer = $perfil->customer;
        if ($customer && blank($customer->f_name) && filled($fila['nombre'])) {
            $this->customerRepo->updateWhere(params: ['id' => $customer->id], data: [
                'name' => Str::limit((string)$fila['nombre'], 80, ''),
                'f_name' => $fila['nombre'],
            ]);
            $huboCambio = true;
        }

        return $huboCambio;
    }

    /**
     * Create user + profile + number in bulk for the chunk. Mirrors the fields
     * register() writes; phone queda VACÍO (la columna es NOT NULL sin unique;
     * hay 611 teléfonos duplicados en el Excel y poblarlo chocaría con el login
     * por teléfono). El teléfono del Excel vive en affiliate_profiles.telefono_contacto.
     *
     * @param array $filas numero => fila mapeada
     * @param \Illuminate\Support\Collection $numerosAdoptados Números 'disponible' ya existentes, keyBy numero.
     */
    private function crearCuentas(array $filas, $numerosAdoptados): void
    {
        $now = now();
        // Una contraseña aleatoria irrecuperable POR CHUNK: el texto plano se
        // descarta aquí mismo, así que ninguna cuenta puede iniciar sesión hasta
        // activarse. Hashear 18.5k veces (bcrypt ~80ms c/u) tomaría >20 min.
        $passwordInutilizable = bcrypt(Str::random(40));
        $referralCodes = $this->generarReferralCodes(cantidad: count($filas));

        $userRows = [];
        foreach ($filas as $fila) {
            $userRows[] = [
                'name' => Str::limit((string)$fila['nombre'], 80, ''),
                'f_name' => $fila['nombre'],
                'l_name' => '',
                'email' => $fila['email'],
                'phone' => '',
                'password' => $passwordInutilizable,
                'referral_code' => array_pop($referralCodes),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        User::insert($userRows);
        $idsPorEmail = User::whereIn('email', array_column($filas, 'email'))->pluck('id', 'email');

        $perfilRows = [];
        $numeroRowsNuevos = [];
        foreach ($filas as $numero => $fila) {
            $userId = $idsPorEmail[$fila['email']] ?? null;
            if (!$userId) {
                continue;
            }

            $perfilRows[] = [
                'customer_id' => $userId,
                'numero_anp' => $numero,
                'nombre_negocio' => $fila['nombre_negocio'],
                'telefono_contacto' => $fila['telefono_contacto'],
                'direccion' => $fila['direccion'],
                'estado' => $fila['estado'],
                'estatus' => 'activo',
                'reclamada' => 0,
                'approved_at' => $now,
                'approved_by' => 'precarga',
                'datos_importacion' => json_encode($fila['datos_importacion'], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $adoptado = $numerosAdoptados->get($numero);
            if ($adoptado) {
                $this->numeroAnpRepo->update(id: $adoptado->id, data: [
                    'estatus' => 'usado',
                    'afiliado_asignado' => $userId,
                    'fecha_activacion' => $now,
                    'operador' => 'precarga',
                ]);
            } else {
                $numeroRowsNuevos[] = [
                    'numero_anp' => $numero,
                    'estatus' => 'usado',
                    'afiliado_asignado' => $userId,
                    'fecha_generacion' => $now,
                    'fecha_activacion' => $now,
                    'operador' => 'precarga',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($perfilRows)) {
            $this->affiliateProfileRepo->insertMany(rows: $perfilRows);
        }
        if (!empty($numeroRowsNuevos)) {
            $this->numeroAnpRepo->insertMany(rows: $numeroRowsNuevos);
        }
    }

    /**
     * Generate unique referral codes with ONE whereIn check per batch instead of
     * the recursive per-row helper (18.5k queries would kill the import).
     */
    private function generarReferralCodes(int $cantidad): array
    {
        $codes = [];
        $guard = 0;
        while (count($codes) < $cantidad && $guard < 10) {
            $guard++;
            $candidatos = [];
            while (count($candidatos) < ($cantidad - count($codes))) {
                $candidatos[strtoupper(Str::random(20))] = true;
            }
            $candidatos = array_diff_key($candidatos, $codes);
            $existentes = User::whereIn('referral_code', array_keys($candidatos))->pluck('referral_code');
            foreach ($existentes as $existente) {
                unset($candidatos[$existente]);
            }
            $codes += $candidatos;
        }
        return array_keys($codes);
    }
}

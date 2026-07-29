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
            'email_sintetico_por_duplicado' => 0,
        ];

        $filas = [];
        foreach ($rows as $row) {
            $numero = strtoupper(trim((string)($row['folio_anpec'] ?? $row['username'] ?? '')));
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
            // Se consultan también los sintéticos: un correo repetido cae a
            // <folio>@... y ese también compite por users.email, que es UNIQUE.
            $emailsCandidatos = array_column($filas, 'email');
            foreach (array_keys($filas) as $numeroFila) {
                $emailsCandidatos[] = $this->emailSintetico(numero: (string)$numeroFila);
            }
            $emailsExistentes = User::whereIn('email', $emailsCandidatos)->pluck('id', 'email');

            $aCrear = [];
            $aAdoptar = [];
            $emailsEnChunk = [];
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
                // users.email es UNIQUE y un duplicado abortaría el insert masivo del
                // chunk completo. Un correo repetido ya NO descarta al afiliado: el
                // corte de julio 2026 trae ~8.8k filas donde un mismo dueño registra
                // varias tiendas con el mismo correo, y cada folio debe quedar con su
                // cuenta porque aquí la cuenta es de la TIENDA, no de la persona. La
                // primera conserva el correo real; las siguientes caen al sintético,
                // que es único por folio.
                $correoRepetido = isset($emailsExistentes[$fila['email']]) || isset($emailsEnChunk[$fila['email']]);
                if ($correoRepetido) {
                    $fila['email'] = $this->emailSintetico(numero: $numero);
                }
                if (isset($emailsExistentes[$fila['email']]) || isset($emailsEnChunk[$fila['email']])) {
                    $resultado['saltados_email_duplicado']++;
                    continue;
                }
                if ($correoRepetido) {
                    $resultado['email_sintetico_por_duplicado']++;
                }
                $emailsEnChunk[$fila['email']] = true;

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
            $email = $this->emailSintetico(numero: $numero);
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
     * Correo interno para el afiliado que no trae uno usable. Es único por folio,
     * así que sirve tanto para los vacíos como para los repetidos.
     */
    private function emailSintetico(string $numero): string
    {
        return strtolower($numero) . self::DOMINIO_EMAIL_SINTETICO;
    }

    /**
     * Map one Excel row to the internal structure. Values '-' count as empty.
     * El formato se detecta por columna: el corte propio de ANPEC trae
     * "folio_anpec"; el export Moodle de los cortes viejos trae "username".
     */
    private function mapearFila(mixed $row, string $numero): array
    {
        return isset($row['folio_anpec'])
            ? $this->mapearFilaAnpec(row: $row, numero: $numero)
            : $this->mapearFilaMoodle(row: $row, numero: $numero);
    }

    /**
     * Corte propio de ANPEC (julio 2026 en adelante). La dirección viene partida
     * en cuatro columnas y se recompone; el teléfono que se guarda es el celular,
     * que es el que sirve para WhatsApp, con el fijo como respaldo.
     *
     * La columna "Contraseña" del export NUNCA se lee: las cuentas nacen con una
     * contraseña inutilizable y solo se abren cuando el afiliado las activa.
     */
    private function mapearFilaAnpec(mixed $row, string $numero): array
    {
        $email = strtolower(trim((string)($row['email'] ?? '')));
        if ($email === '' || $email === '-') {
            $email = $this->emailSintetico(numero: $numero);
        }

        $extras = [];
        foreach ([
            'cp' => 'cp',
            'colonia' => 'colonia',
            'municipio' => 'municipio',
            'telefono' => 'telefono_fijo',
            'celular' => 'celular',
            'origen' => 'origen',
            'id_origen' => 'id_origen',
        ] as $columna => $clave) {
            $valor = $this->limpiarValor($row[$columna] ?? '');
            if ($valor !== null) {
                $extras[$clave] = $valor;
            }
        }
        $extras['email_origen'] = $email;

        return [
            'nombre' => $this->limpiarValor($row['nombre'] ?? ''),
            'email' => $email,
            'nombre_negocio' => $this->limpiarValor($row['negocio'] ?? ''),
            'direccion' => $this->componerDireccion(row: $row),
            'estado' => $this->limpiarValor($row['estado'] ?? ''),
            'telefono_contacto' => $this->limpiarTelefono($row['celular'] ?? '')
                ?? $this->limpiarTelefono($row['telefono'] ?? ''),
            'datos_importacion' => $extras,
        ];
    }

    /** Domicilio + colonia + municipio + CP en una línea, sin las partes vacías. */
    private function componerDireccion(mixed $row): ?string
    {
        $partes = array_filter([
            $this->limpiarValor($row['domicilio'] ?? ''),
            $this->limpiarValor($row['colonia'] ?? ''),
            $this->limpiarValor($row['municipio'] ?? ''),
            $this->limpiarValor($row['cp'] ?? ''),
        ]);

        return $partes ? Str::limit(implode(', ', $partes), 191, '') : null;
    }

    /** Export Moodle de los cortes anteriores a julio 2026. */
    private function mapearFilaMoodle(mixed $row, string $numero): array
    {
        $nombre = $this->limpiarValor($row['firstname'] ?? '');
        $apellido = $this->limpiarValor($row['lastname'] ?? '');
        $email = strtolower(trim((string)($row['email'] ?? '')));
        if ($email === '' || $email === '-') {
            $email = $this->emailSintetico(numero: $numero);
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

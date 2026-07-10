<?php

namespace App\Imports;

use App\Services\AfiliadoPrecargaService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * R-Precarga: import del Excel COMPLETO de usuarios de ANPEC (formato Moodle,
 * ~18.5k filas). Crea la cuenta completa de cada afiliado. Distinto e
 * independiente del import simple de números (NumerosAnpImport), que se conserva.
 */
class AfiliadosImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    /** Patrón real de la base ANPEC: "anp" + dígitos + una letra final opcional. */
    public const PATRON_NUMERO_ANP = '/^anp\d+a?$/i';

    private const CHUNK_SIZE = 500;

    /**
     * Contadores acumulados de todo el archivo.
     *
     * @var array<string,int>
     */
    public array $result = [
        'creados' => 0,
        'actualizados' => 0,
        'sin_cambios' => 0,
        'saltados_reclamada' => 0,
        'saltados_bloqueado' => 0,
        'saltados_anomalia' => 0,
        'saltados_email_duplicado' => 0,
        'saltados_sin_patron' => 0,
    ];

    public function __construct(
        private readonly AfiliadoPrecargaService $precargaService,
    )
    {
    }

    public function collection(Collection $rows): void
    {
        $validas = [];
        $vistos = [];
        foreach ($rows as $row) {
            $username = strtolower(trim((string)($row['username'] ?? '')));
            if (!preg_match(self::PATRON_NUMERO_ANP, $username)) {
                // Filas no-afiliado del export Moodle (admin/alumno/etc.).
                $this->result['saltados_sin_patron']++;
                continue;
            }
            if (isset($vistos[$username])) {
                // Username duplicado dentro del archivo: anomalía, gana la primera.
                $this->result['saltados_anomalia']++;
                continue;
            }
            $vistos[$username] = true;
            $validas[] = $row;
        }

        if (empty($validas)) {
            return;
        }

        $chunkResult = $this->precargaService->procesarChunk(rows: $validas);
        foreach ($chunkResult as $clave => $valor) {
            $this->result[$clave] += $valor;
        }
    }

    public function chunkSize(): int
    {
        return self::CHUNK_SIZE;
    }
}

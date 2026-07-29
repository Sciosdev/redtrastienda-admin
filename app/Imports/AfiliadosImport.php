<?php

namespace App\Imports;

use App\Services\AfiliadoPrecargaService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * R-Precarga: import del Excel COMPLETO de afiliados de ANPEC. Crea la cuenta
 * completa de cada afiliado. Distinto e independiente del import simple de
 * números (NumerosAnpImport), que se conserva.
 *
 * Soporta los dos formatos que ha entregado ANPEC, detectados por columna:
 * - Corte de julio 2026 en adelante: export propio de ANPEC, folio en la
 *   columna "Folio ANPEC" y encabezados en la fila 3 (arriba va el membrete).
 * - Cortes anteriores: export Moodle, folio en "username" y encabezados en la
 *   fila 1; para releerlo hay que construir el import con filaEncabezados: 1.
 */
class AfiliadosImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    /**
     * Patrón real de la base ANPEC: "anp" + dígitos + hasta DOS letras finales.
     * El corte de julio 2026 trae 28.9k folios con sufijo "w" (alta por web) y
     * 4 con sufijo "aa". El patrón anterior solo aceptaba "a", así que los
     * habría descartado en silencio como filas no-afiliado.
     */
    public const PATRON_NUMERO_ANP = '/^anp\d+[a-z]{0,2}$/i';

    /** El export de ANPEC lleva 2 filas de membrete antes de los encabezados. */
    private const FILA_ENCABEZADOS = 3;

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
        'email_sintetico_por_duplicado' => 0,
        'saltados_sin_patron' => 0,
    ];

    public function __construct(
        private readonly AfiliadoPrecargaService $precargaService,
        private readonly int                     $filaEncabezados = self::FILA_ENCABEZADOS,
    )
    {
    }

    public function headingRow(): int
    {
        return $this->filaEncabezados;
    }

    public function collection(Collection $rows): void
    {
        $validas = [];
        $vistos = [];
        foreach ($rows as $row) {
            $folio = strtolower(trim((string)($row['folio_anpec'] ?? $row['username'] ?? '')));
            if (!preg_match(self::PATRON_NUMERO_ANP, $folio)) {
                // Filas sin folio válido: membrete, totales, o cuentas
                // no-afiliado del export Moodle (admin/alumno/etc.).
                $this->result['saltados_sin_patron']++;
                continue;
            }
            if (isset($vistos[$folio])) {
                // Folio duplicado dentro del archivo: anomalía, gana la primera.
                $this->result['saltados_anomalia']++;
                continue;
            }
            $vistos[$folio] = true;
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

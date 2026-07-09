<?php

namespace App\Services;

use App\Contracts\Repositories\NumeroAnpRepositoryInterface;

class NumeroAnpService
{
    private const CODE_CHARSET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    private const CODE_LENGTH = 6;

    public function __construct(
        private readonly NumeroAnpRepositoryInterface $numeroAnpRepo,
    )
    {
    }

    /**
     * Generate a batch of unique ANP numbers and persist them as "disponible".
     *
     * @return int Amount of numbers actually generated.
     */
    public function generateBatch(int $cantidad, ?string $prefijo, ?string $operador): int
    {
        $prefijo = $prefijo ? strtoupper(trim($prefijo)) : null;

        $accepted = [];
        $acceptedSet = [];
        $guard = 0;
        $maxGuard = $cantidad * 20 + 200;

        while (count($accepted) < $cantidad && $guard < $maxGuard) {
            $guard++;
            $needed = $cantidad - count($accepted);

            $candidates = [];
            $innerGuard = 0;
            while (count($candidates) < $needed && $innerGuard < $needed * 20 + 100) {
                $innerGuard++;
                $code = $this->makeCode(prefijo: $prefijo);
                if (isset($acceptedSet[$code]) || isset($candidates[$code])) {
                    continue;
                }
                $candidates[$code] = true;
            }

            $candidateList = array_keys($candidates);
            $existing = $this->numeroAnpRepo->getExistingNumeros(numeros: $candidateList);
            foreach (array_diff($candidateList, $existing) as $code) {
                $accepted[] = $code;
                $acceptedSet[$code] = true;
            }
        }

        if (empty($accepted)) {
            return 0;
        }

        $now = now();
        $rows = array_map(fn($code) => [
            'numero_anp' => $code,
            'estatus' => 'disponible',
            'operador' => $operador,
            'fecha_generacion' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ], $accepted);

        $this->numeroAnpRepo->insertMany(rows: $rows);

        return count($accepted);
    }

    /**
     * Persist parsed import rows, skipping duplicates (in-file and already existing).
     *
     * @param array $parsedRows Each row: ['numero_anp' => string, 'observaciones' => string|null]
     * @return array{imported:int, skipped:int}
     */
    public function importNumeros(array $parsedRows): array
    {
        $now = now();
        $seen = [];
        $prepared = [];
        $skipped = 0;

        foreach ($parsedRows as $row) {
            $numero = trim((string)($row['numero_anp'] ?? ''));
            if ($numero === '') {
                continue;
            }
            if (isset($seen[$numero])) {
                $skipped++;
                continue;
            }
            $seen[$numero] = true;
            $observaciones = isset($row['observaciones']) ? trim((string)$row['observaciones']) : '';
            $prepared[$numero] = [
                'numero_anp' => $numero,
                'observaciones' => $observaciones !== '' ? $observaciones : null,
            ];
        }

        if (!empty($prepared)) {
            $existing = $this->numeroAnpRepo->getExistingNumeros(numeros: array_keys($prepared));
            foreach ($existing as $existingNumero) {
                unset($prepared[$existingNumero]);
                $skipped++;
            }
        }

        $rows = array_map(fn($item) => [
            'numero_anp' => $item['numero_anp'],
            'estatus' => 'disponible',
            'observaciones' => $item['observaciones'],
            'fecha_generacion' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ], array_values($prepared));

        if (!empty($rows)) {
            $this->numeroAnpRepo->insertMany(rows: $rows);
        }

        return ['imported' => count($rows), 'skipped' => $skipped];
    }

    private function makeCode(?string $prefijo): string
    {
        $random = '';
        $max = strlen(self::CODE_CHARSET) - 1;
        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $random .= self::CODE_CHARSET[random_int(0, $max)];
        }
        return $prefijo ? $prefijo . '-' . $random : $random;
    }
}

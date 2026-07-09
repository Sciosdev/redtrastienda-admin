<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class NumerosAnpImport implements ToCollection
{
    /**
     * Parsed rows: each ['numero_anp' => string, 'observaciones' => string|null].
     *
     * @var array<int, array{numero_anp:string, observaciones:string|null}>
     */
    public array $rows = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $numero = trim((string)($row[0] ?? ''));
            if ($numero === '') {
                continue;
            }
            $normalized = strtolower(str_replace(['_', ' '], '', $numero));
            if ($normalized === 'numeroanp') {
                continue;
            }
            $this->rows[] = [
                'numero_anp' => $numero,
                'observaciones' => isset($row[1]) ? trim((string)$row[1]) : null,
            ];
        }
    }
}

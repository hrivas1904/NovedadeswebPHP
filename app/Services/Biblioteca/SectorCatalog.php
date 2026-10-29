<?php

namespace App\Services\Biblioteca;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SectorCatalog
{
    public function names(): array
    {
        if (!Schema::hasTable('areas')) return [];
        $query = DB::table('areas');
        if (Schema::hasColumn('areas', 'ESTADO')) $query->where('ESTADO', 1);
        return $query->orderBy('NOMBRE')->pluck('NOMBRE')->filter(fn ($name) => is_string($name) && trim($name) !== '')->uniqueStrict()->values()->all();
    }

    public function options(string $current = ''): array
    {
        $names = $this->names();
        return ['names' => $names, 'current' => $current, 'unlisted' => $current !== '' && !in_array($current, $names, true)];
    }

    public function validate(mixed $value, ?string $previous = null): void
    {
        if (!is_string($value)) Content::fail('Sector / servicio inválido.');
        // A source or saved document may retain its exact previous literal until reviewed.
        if ($value === '' || $value === $previous || in_array($value, $this->names(), true)) return;
        Content::fail('Seleccioná Sector / servicio entre las áreas activas de Parametrización. Podés conservar el valor anterior del documento.');
    }
}
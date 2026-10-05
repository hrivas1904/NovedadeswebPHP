<?php

namespace App\Services\Organigrama;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Importador
{
    public function __construct(private readonly Estructura $estructura) {}

    public function importar(array $data, array $mapa = [], bool $simular = false): array
    {
        Validator::make($data, [
            'version' => ['required', 'in:2'], 'rootId' => ['required', 'string'],
            'nodes' => ['required', 'array', 'min:1'],
            'nodes.*.id' => ['required', 'string', 'max:120', 'distinct', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'nodes.*.title' => ['required', 'string', 'max:200', 'regex:/\S/u'],
            'nodes.*.person' => ['nullable', 'string', 'max:1000'],
            'edges' => ['present', 'array'], 'edges.*.parentId' => ['required', 'string'], 'edges.*.childId' => ['required', 'string'],
            'edges.*.relation' => ['required', Rule::in(['direct', 'support', 'shared'])],
            'edges.*.order' => ['required', 'integer', 'min:0', 'max:100000'],
        ])->validate();
        $this->estructura->validarGrafo($data['nodes'], $data['edges'], $data['rootId']);
        foreach ($mapa as $id => $row) {
            if (! in_array($id, array_column($data['nodes'], 'id'), true)) {
                throw ValidationException::withMessages(['mapa' => 'ID de importación desconocido: '.$id]);
            }
            Validator::make($row, [
                'rol_id' => ['nullable', 'integer', Rule::exists('rol_empleados', 'ID_ROL')],
                'area_id' => ['nullable', 'integer', Rule::exists('areas', 'ID_AREA')],
                'legajos' => ['sometimes', 'array'],
                'legajos.*' => ['integer', 'distinct', Rule::exists('empleados', 'LEGAJO')->where('ESTADO', 'ACTIVO')],
            ])->validate();
        }
        $hash = hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
        $report = $this->mapear($data, $mapa);
        $report['simulacion'] = $simular;
        if ($simular) {
            return $report;
        }

        return DB::transaction(function () use ($data, $report, $hash) {
            $estado = $this->estructura->bloquear();
            if ($estado->import_hash === $hash) {
                return json_decode($estado->import_report, true, flags: JSON_THROW_ON_ERROR) + ['sin_cambios' => true];
            }
            if ($estado->root_id !== null || DB::table('organigrama_posiciones')->exists()) {
                throw ValidationException::withMessages(['importacion' => 'Ya existe una estructura. Un archivo distinto no puede reemplazarla automáticamente.']);
            }
            foreach ($data['nodes'] as $n) {
                $m = $report['posiciones'][$n['id']];
                DB::table('organigrama_posiciones')->insert([
                    'id' => $n['id'], 'titulo' => $n['title'], 'responsable_texto' => $n['person'] ?? '',
                    'rol_id' => $m['rol_id'], 'area_id' => $m['area_id'], 'created_at' => now(), 'updated_at' => now(),
                ]);
                foreach ($m['legajos'] as $legajo) {
                    DB::table('organigrama_responsables')->insert(['posicion_id' => $n['id'], 'legajo' => $legajo]);
                }
            }
            foreach ($data['edges'] as $e) {
                DB::table('organigrama_dependencias')->insert(['parent_id' => $e['parentId'], 'child_id' => $e['childId'], 'tipo' => $e['relation'], 'orden' => $e['order']]);
            }
            DB::table('organigrama_estado')->where('id', 1)->update([
                'root_id' => $data['rootId'], 'revision' => $estado->revision + 1, 'import_hash' => $hash,
                'import_report' => json_encode($report, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ]);
            $this->estructura->evento('importar', null, null, null, $report);

            return $report;
        }, 3);
    }

    private function mapear(array $data, array $mapa): array
    {
        $normalizar = fn (string $s) => Str::lower(preg_replace('/\s+/u', ' ', trim(Str::ascii($s))));
        $empleados = Schema::hasTable('empleados')
            ? DB::table('empleados')->where('ESTADO', 'ACTIVO')->get(['LEGAJO', 'COLABORADOR', 'ID_ROL', 'ID_AREA']) : collect();
        $nombres = $empleados->groupBy(fn ($e) => $normalizar($e->COLABORADOR));
        $roles = Schema::hasTable('rol_empleados') ? DB::table('rol_empleados')->get(['ID_ROL', 'NOMBRE'])->keyBy('ID_ROL') : collect();
        $report = ['total_posiciones' => count($data['nodes']), 'total_dependencias' => count($data['edges']),
            'catalogos_disponibles' => ['empleados' => Schema::hasTable('empleados'), 'roles' => Schema::hasTable('rol_empleados'), 'areas' => Schema::hasTable('areas')],
            'coincidencias' => [], 'pendientes' => [], 'posiciones' => []];
        foreach ($data['nodes'] as $n) {
            $explicit = $mapa[$n['id']] ?? [];
            $legajos = $explicit['legajos'] ?? [];
            foreach (array_filter(array_map('trim', explode('·', $n['person'] ?? ''))) as $nombre) {
                $matches = $nombres->get($normalizar($nombre), collect());
                if (array_key_exists('legajos', $explicit) && $legajos === []) {
                    $report['pendientes'][] = ['id' => $n['id'], 'nombre' => $nombre, 'motivo' => 'mapa_sin_vinculo'];
                } elseif (array_key_exists('legajos', $explicit)) {
                    $report['coincidencias'][] = ['id' => $n['id'], 'nombre' => $nombre, 'legajos' => $legajos, 'criterio' => 'mapa_explicito'];
                } elseif ($matches->count() === 1) {
                    $legajos[] = (int) $matches->first()->LEGAJO;
                    $report['coincidencias'][] = ['id' => $n['id'], 'nombre' => $nombre, 'legajo' => (int) $matches->first()->LEGAJO, 'criterio' => 'nombre_completo_unico'];
                } else {
                    $report['pendientes'][] = ['id' => $n['id'], 'nombre' => $nombre, 'motivo' => $matches->isEmpty() ? 'sin_coincidencia_exacta' : 'nombre_ambiguo'];
                }
            }
            $legajos = array_values(array_unique($legajos));
            $people = $empleados->whereIn('LEGAJO', $legajos);
            $rol = $explicit['rol_id'] ?? null;
            // No se vincula una posición por título solamente: exige responsable único
            // y coincidencia con SU rol laboral existente. Resto: mapa explícito por ID.
            if ($rol === null && $people->count() === 1) {
                $candidate = $roles->get($people->first()->ID_ROL);
                if ($candidate && $normalizar($candidate->NOMBRE) === $normalizar($n['title'])) {
                    $rol = (int) $candidate->ID_ROL;
                }
            }
            $report['posiciones'][$n['id']] = ['rol_id' => $rol, 'area_id' => $explicit['area_id'] ?? null, 'legajos' => $legajos];
        }

        return $report;
    }
}

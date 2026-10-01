<?php

namespace App\Services\Organigrama;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Estructura
{
    public function disponible(): bool
    {
        return Schema::hasTable('organigrama_estado');
    }

    public function datos(): array
    {
        // La lectura también necesita un snapshot consistente durante cambios concurrentes.
        return DB::transaction(function () {
            $estado = DB::table('organigrama_estado')->where('id', 1)->sharedLock()->first();
            $responsables = DB::table('organigrama_responsables')->orderBy('legajo')->get()->groupBy('posicion_id');

            return [
                'version' => 2, 'revision' => (int) $estado->revision, 'rootId' => $estado->root_id,
                'nodes' => DB::table('organigrama_posiciones')->orderBy('created_at')->orderBy('id')->get()->map(fn ($n) => [
                    'id' => $n->id, 'title' => $n->titulo, 'person' => $n->responsable_texto,
                    'rol_id' => $n->rol_id, 'area_id' => $n->area_id,
                    'legajos' => ($responsables->get($n->id) ?? collect())->pluck('legajo')->map(fn ($v) => (int) $v)->all(),
                ])->all(),
                'edges' => DB::table('organigrama_dependencias')->orderBy('parent_id')->orderBy('orden')->orderBy('child_id')->get()->map(fn ($e) => [
                    'parentId' => $e->parent_id, 'childId' => $e->child_id, 'relation' => $e->tipo, 'order' => (int) $e->orden,
                ])->all(),
            ];
        });
    }

    public function catalogos(): array
    {
        return [
            'roles' => Schema::hasTable('rol_empleados') ? DB::table('rol_empleados')->orderBy('NOMBRE')->get(['ID_ROL as id', 'NOMBRE as nombre'])->all() : [],
            'areas' => Schema::hasTable('areas') ? DB::table('areas')->orderBy('NOMBRE')->get(['ID_AREA as id', 'NOMBRE as nombre'])->all() : [],
            'colaboradores' => Schema::hasTable('empleados') ? DB::table('empleados')->where('ESTADO', 'ACTIVO')->orderBy('COLABORADOR')->get(['LEGAJO as id', 'COLABORADOR as nombre'])->all() : [],
        ];
    }

    public function bloquear(?int $revision = null): object
    {
        $estado = DB::table('organigrama_estado')->where('id', 1)->lockForUpdate()->first();
        abort_unless($estado, 503, 'El organigrama todavía no está instalado.');
        abort_if($revision !== null && $revision !== (int) $estado->revision, 409, 'La estructura cambió en otra sesión. Recargá antes de guardar.');

        return $estado;
    }

    public function guardar(?string $id, array $input, int $actor): array
    {
        $v = Validator::make($input, [
            'revision' => ['required', 'integer', 'min:0'],
            'title' => ['required', 'string', 'max:200', 'regex:/\S/u'],
            'person' => ['nullable', 'string', 'max:1000'],
            'rol_id' => ['nullable', 'integer', Rule::exists('rol_empleados', 'ID_ROL')],
            'area_id' => ['nullable', 'integer', Rule::exists('areas', 'ID_AREA')],
            'legajos' => ['present', 'array', 'max:100'],
            'legajos.*' => ['integer', 'distinct', Rule::exists('empleados', 'LEGAJO')->where('ESTADO', 'ACTIVO')],
            'parents' => ['present', 'array', 'max:100'],
            'parents.*.parentId' => ['required', 'string', 'distinct', 'max:120'],
            'parents.*.relation' => ['required', Rule::in(['direct', 'support', 'shared'])],
            'parents.*.order' => ['required', 'integer', 'min:0', 'max:100000'],
        ])->validate();

        return DB::transaction(function () use ($id, $v, $actor) {
            $estado = $this->bloquear((int) $v['revision']);
            $antes = $this->datos();
            $crear = $id === null;
            if (! $crear) {
                abort_unless(DB::table('organigrama_posiciones')->where('id', $id)->exists(), 404);
            }
            $id ??= 'pos-'.Str::uuid();
            $nodes = $antes['nodes'];
            if ($crear) {
                $nodes[] = ['id' => $id];
            }
            $root = $estado->root_id ?? $id;
            $edges = array_values(array_filter($antes['edges'], fn ($e) => $e['childId'] !== $id));
            foreach ($v['parents'] as $edge) {
                $edges[] = ['parentId' => $edge['parentId'], 'childId' => $id, 'relation' => $edge['relation'], 'order' => (int) $edge['order']];
            }
            $this->validarGrafo($nodes, $edges, $root);
            $row = ['titulo' => trim($v['title']), 'responsable_texto' => trim($v['person'] ?? ''), 'rol_id' => $v['rol_id'] ?? null, 'area_id' => $v['area_id'] ?? null, 'updated_at' => now()];
            if ($crear) {
                DB::table('organigrama_posiciones')->insert(['id' => $id, 'created_at' => now()] + $row);
            } else {
                DB::table('organigrama_posiciones')->where('id', $id)->update($row);
            }
            DB::table('organigrama_dependencias')->where('child_id', $id)->delete();
            foreach ($v['parents'] as $e) {
                DB::table('organigrama_dependencias')->insert(['parent_id' => $e['parentId'], 'child_id' => $id, 'tipo' => $e['relation'], 'orden' => $e['order']]);
            }
            DB::table('organigrama_responsables')->where('posicion_id', $id)->delete();
            foreach ($v['legajos'] as $legajo) {
                DB::table('organigrama_responsables')->insert(['posicion_id' => $id, 'legajo' => $legajo]);
            }
            DB::table('organigrama_estado')->where('id', 1)->update(['revision' => $estado->revision + 1, 'root_id' => $root]);
            $despues = $this->datos();
            $this->evento($crear ? 'crear' : 'editar', $actor, $id, $antes, $despues);

            return ['selectedId' => $id, 'data' => $despues];
        }, 3);
    }

    public function eliminar(string $id, int $revision, int $actor): array
    {
        return DB::transaction(function () use ($id, $revision, $actor) {
            $estado = $this->bloquear($revision);
            abort_unless(DB::table('organigrama_posiciones')->where('id', $id)->exists(), 404);
            if ($estado->root_id === $id || DB::table('organigrama_dependencias')->where('parent_id', $id)->exists()) {
                throw ValidationException::withMessages(['posicion' => 'No se puede eliminar la raíz ni una posición con hijos. Reasigná primero sus dependencias.']);
            }
            $antes = $this->datos();
            DB::table('organigrama_dependencias')->where('child_id', $id)->delete();
            DB::table('organigrama_responsables')->where('posicion_id', $id)->delete();
            DB::table('organigrama_posiciones')->where('id', $id)->delete();
            DB::table('organigrama_estado')->where('id', 1)->update(['revision' => $estado->revision + 1]);
            $despues = $this->datos();
            $this->evento('eliminar', $actor, $id, $antes, $despues);

            return ['selectedId' => $estado->root_id, 'data' => $despues];
        }, 3);
    }

    public function validarGrafo(array $nodes, array $edges, string $root): void
    {
        $ids = array_column($nodes, 'id');
        $fail = fn ($message) => throw ValidationException::withMessages(['parents' => $message]);
        if (count($ids) !== count(array_unique($ids)) || ! in_array($root, $ids, true)) {
            $fail('IDs duplicados o raíz inexistente.');
        }
        $adj = array_fill_keys($ids, []);
        $indegree = array_fill_keys($ids, 0);
        $pairs = [];
        foreach ($edges as $edge) {
            $p = $edge['parentId'];
            $c = $edge['childId'];
            if (! isset($adj[$p], $adj[$c]) || $c === $root || $p === $c) {
                $fail('Dependencia inválida: revisá superiores, raíz y autorreferencias.');
            }
            $pair = $p.'|'.$c;
            if (isset($pairs[$pair])) {
                $fail('La misma dependencia está repetida.');
            }
            $pairs[$pair] = true;
            $adj[$p][] = $c;
            $indegree[$c]++;
        }
        foreach ($indegree as $id => $degree) {
            if ($id !== $root && $degree === 0) {
                $fail('Cada posición debe tener al menos un superior.');
            }
        }
        $queue = [$root];
        $visited = 0;
        while ($queue) {
            $p = array_shift($queue);
            $visited++;
            foreach ($adj[$p] as $c) {
                if (--$indegree[$c] === 0) {
                    $queue[] = $c;
                }
            }
        }
        if ($visited !== count($ids)) {
            $fail('La dependencia generaría un ciclo jerárquico.');
        }
    }

    public function evento(string $accion, ?int $actor, ?string $id, ?array $antes, array $despues): void
    {
        DB::table('organigrama_eventos')->insert([
            'posicion_id' => $id, 'accion' => $accion, 'actor_user_id' => $actor,
            'before_json' => $antes === null ? null : json_encode($antes, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'after_json' => json_encode($despues, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'request_id' => (string) Str::uuid(), 'created_at' => now(),
        ]);
    }
}

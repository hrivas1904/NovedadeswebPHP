<?php

namespace App\Services\Edd;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PlanificacionEdd
{
    public function __construct(private readonly ConfiguracionEdd $configuracion, private readonly PoblacionEdd $poblacion) {}

    public function disponible(): bool
    {
        return Schema::hasTable('edd_listas_competencias') && Schema::hasTable('edd_evaluador_areas');
    }

    public function bibliotecas(int $periodo)
    {
        return DB::table('edd_listas_competencias')->where('periodo_id', $periodo)->where('codigo', '<>', 'generales')
            ->orderBy('nombre')->orderBy('id')->get()->map(fn ($row) => $this->presentar($row));
    }

    public function lista(int $periodo, ?int $id = null, bool $generales = false): array
    {
        $query = DB::table('edd_listas_competencias')->where('periodo_id', $periodo);
        $row = $generales ? $query->where('codigo', 'generales')->first()
            : ($id ? $query->where('codigo', '<>', 'generales')->where('id', $id)->first() : null);
        abort_if($id && ! $row, 404);

        return $row ? $this->presentar($row) : ['id' => null, 'revision' => 0, 'nombre' => $generales ? 'Competencias generales' : '', 'items' => []];
    }

    private function presentar(object $row): array
    {
        return ['id' => $row->id, 'revision' => $row->revision, 'nombre' => $row->nombre,
            'items' => json_decode($row->competencias_json, true, flags: JSON_THROW_ON_ERROR)];
    }

    public function catalogo(int $periodo): array
    {
        $catalogo = config('edd_competencias');
        if ($this->disponible()) {
            foreach ($this->bibliotecas($periodo) as $lista) {
                $catalogo['lista-'.$lista['id']] = ['nombre' => 'Biblioteca: '.$lista['nombre'],
                    'competencias' => explode("\n", $this->poblacion->textoCompetencias($lista['items']))];
            }
        }

        return $catalogo;
    }

    public function competenciasDelParticipante(array $participante): array
    {
        return [
            'generales' => $this->lista($participante['periodo_id'], generales: true)['items'],
            'especificas' => $this->poblacion->competenciasEfectivas($participante),
        ];
    }

    public function guardarLista(int $periodo, array $datos, int $actor, bool $generales = false): int
    {
        return DB::transaction(function () use ($periodo, $datos, $actor, $generales) {
            $this->configuracion->bloquearPeriodo($periodo);
            $actual = $this->lista($periodo, $datos['lista_id'] ?? null, $generales);
            $this->configuracion->verificarRevision($actual['revision'], $datos['revision']);
            $antes = $actual['id'] ? (array) DB::table('edd_listas_competencias')->find($actual['id']) : null;
            $contenido = [
                'nombre' => $generales ? 'Competencias generales' : trim($datos['nombre']),
                'competencias_json' => json_encode($this->poblacion->parsearCompetencias($datos['competencias'], $actual['items']), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                'revision' => $actual['revision'] + 1, 'updated_by' => $actor, 'updated_at' => now('UTC'),
            ];
            if ($actual['id']) {
                $id = $actual['id'];
                DB::table('edd_listas_competencias')->where('id', $id)->update($contenido);
            } else {
                $id = DB::table('edd_listas_competencias')->insertGetId($contenido + [
                    'periodo_id' => $periodo, 'codigo' => $generales ? 'generales' : (string) Str::uuid(), 'created_at' => now('UTC'),
                ]);
            }
            $this->configuracion->evento($periodo, 'lista_competencias', $id, $generales ? 'guardar_generales' : 'guardar_biblioteca',
                $actor, $antes, (array) DB::table('edd_listas_competencias')->find($id));

            return $id;
        });
    }

    public function areasEvaluadores(int $periodo)
    {
        return DB::table('edd_evaluador_areas as ea')->join('users as u', 'u.id', '=', 'ea.evaluador_user_id')
            ->join('areas as a', 'a.ID_AREA', '=', 'ea.area_id')->where('ea.periodo_id', $periodo)
            ->orderBy('a.NOMBRE')->orderBy('u.name')->get(['ea.*', 'u.name', 'u.estado', 'a.NOMBRE as area_nombre']);
    }

    public function registrarArea(int $periodo, int $evaluador, int $area, int $actor): void
    {
        DB::transaction(function () use ($periodo, $evaluador, $area, $actor) {
            $this->configuracion->bloquearPeriodo($periodo);
            if (! DB::table('users')->where('id', $evaluador)->where('estado', 'ACTIVO')->exists()
                || ! DB::table('areas')->where('ID_AREA', $area)->exists()) {
                throw ValidationException::withMessages(['evaluador_user_id' => 'Elegí una cuenta activa y un área válida.']);
            }
            $clave = ['periodo_id' => $periodo, 'evaluador_user_id' => $evaluador, 'area_id' => $area];
            if (DB::table('edd_evaluador_areas')->where($clave)->exists()) {
                return;
            }
            $datos = $clave + ['created_by' => $actor, 'created_at' => now('UTC'), 'updated_at' => now('UTC')];
            $id = DB::table('edd_evaluador_areas')->insertGetId($datos);
            $this->configuracion->evento($periodo, 'evaluador_area', $id, 'registrar_evaluador_area', $actor, null, $datos);
        });
    }

    public function quitarArea(int $periodo, int $id, int $actor): void
    {
        DB::transaction(function () use ($periodo, $id, $actor) {
            $this->configuracion->bloquearPeriodo($periodo);
            $row = DB::table('edd_evaluador_areas')->where('periodo_id', $periodo)->where('id', $id)->first();
            abort_unless($row, 404);
            if (DB::table('edd_asignaciones as s')->join('edd_participantes as p', 'p.id', '=', 's.participante_id')
                ->where('p.periodo_id', $periodo)->where('p.area_id', $row->area_id)
                ->where('s.evaluador_user_id', $row->evaluador_user_id)->where('s.current_slot', 1)->exists()) {
                throw ValidationException::withMessages(['area_id' => 'Reasigná primero sus colaboradores de esta área.']);
            }
            DB::table('edd_evaluador_areas')->where('id', $id)->delete();
            $this->configuracion->evento($periodo, 'evaluador_area', $id, 'quitar_evaluador_area', $actor, (array) $row, []);
        });
    }
}

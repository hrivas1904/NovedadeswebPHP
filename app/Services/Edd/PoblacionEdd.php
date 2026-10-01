<?php

namespace App\Services\Edd;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PoblacionEdd
{
    public function __construct(private readonly ConfiguracionEdd $configuracion) {}

    public function disponible(): bool
    {
        return Schema::hasTable('edd_asignaciones');
    }

    public function areas()
    {
        return DB::table('areas')->orderBy('NOMBRE')->orderBy('ID_AREA')->get(['ID_AREA', 'NOMBRE']);
    }

    public function evaluadores()
    {
        return DB::table('users as u')->leftJoin('areas as a', 'a.ID_AREA', '=', 'u.area_id')
            ->where('u.estado', 'ACTIVO')->orderBy('u.name')
            ->get(['u.id', 'u.name', 'u.legajo', 'u.rol', 'a.NOMBRE as area_nombre']);
    }

    private function nomina()
    {
        return DB::table('empleados as e')->leftJoin('areas as a', 'a.ID_AREA', '=', 'e.ID_AREA')
            ->leftJoin('rol_empleados as r', 'r.ID_ROL', '=', 'e.ID_ROL')
            ->leftJoin('servicios as sv', 'sv.ID_SERVICIOS', '=', 'e.ID_SERVICIOS')
            ->select('e.LEGAJO', 'e.COLABORADOR', 'e.ESTADO', 'e.ID_AREA', 'e.ID_ROL', 'e.ID_SERVICIOS',
                'a.NOMBRE as area_nombre', 'r.NOMBRE as rol_nombre', 'sv.NOMBRE as servicio_nombre');
    }

    public function equipo(int $usuario)
    {
        return DB::table('edd_asignaciones as s')
            ->join('edd_participantes as p', 'p.id', '=', 's.participante_id')
            ->join('edd_periodos as e', 'e.id', '=', 'p.periodo_id')
            ->leftJoin('areas as a', 'a.ID_AREA', '=', 'p.area_id')
            ->where('s.evaluador_user_id', $usuario)->where('s.current_slot', 1)->where('p.incluido', true)
            ->orderByDesc('e.anio')->orderBy('p.legajo')
            ->get(['p.legajo', 'p.contexto_snapshot_json', 'e.nombre as periodo', 'a.NOMBRE as area'])
            ->map(fn ($row) => ['legajo' => $row->legajo, 'periodo' => $row->periodo, 'area' => $row->area,
                'nombre' => json_decode($row->contexto_snapshot_json, true, flags: JSON_THROW_ON_ERROR)['nombre']]);
    }

    public function candidatos(int $periodo, array $filtros)
    {
        $query = $this->nomina()
            ->where('e.ESTADO', 'ACTIVO')->whereNotExists(function ($query) use ($periodo) {
                $query->selectRaw('1')->from('edd_participantes as p')->where('p.periodo_id', $periodo)->whereColumn('p.legajo', 'e.LEGAJO');
            });
        if (! empty($filtros['area'])) {
            $query->where('e.ID_AREA', $filtros['area']);
        }
        if (! empty($filtros['q'])) {
            $query->where(fn ($q) => $q->where('e.COLABORADOR', 'like', '%'.$filtros['q'].'%')->orWhere('e.LEGAJO', (int) $filtros['q']));
        }

        return $query->orderBy('e.COLABORADOR')->orderBy('e.LEGAJO')
            ->paginate(25, ['*'], 'candidatos_page')->withQueryString();
    }

    public function participantes(int $periodo, array $filtros = [], bool $soloIncluidos = false)
    {
        $query = $this->consultaParticipantes($periodo);
        if ($soloIncluidos) {
            $query->where('p.incluido', true);
        }
        if (! empty($filtros['area'])) {
            $query->where('p.area_id', $filtros['area']);
        }

        // La búsqueda nominal se aplica a la nómina de candidatos. El listado de
        // participantes mantiene todos los registros del área, incluidos los excluidos.
        $pagina = $query->orderBy('p.legajo')->paginate(25, ['p.*', 'a.NOMBRE as area_nombre',
            'u.name as evaluador_nombre', 'u.estado as evaluador_estado', 's.evaluador_user_id', 's.funcion',
            'cuentas.cantidad as cuentas_activas'], 'participantes_page')->withQueryString();
        $nomina = $this->nomina()->whereIn('e.LEGAJO', $pagina->getCollection()->pluck('legajo'))->get()->groupBy('LEGAJO');

        return $pagina->through(fn ($row) => $this->presentarParticipante($row, $nomina->get($row->legajo, collect())));
    }

    public function participante(int $periodo, int $id): array
    {
        $row = $this->consultaParticipantes($periodo)->where('p.id', $id)->first([
            'p.*', 'a.NOMBRE as area_nombre', 'u.name as evaluador_nombre', 'u.estado as evaluador_estado',
            's.evaluador_user_id', 's.funcion', 'cuentas.cantidad as cuentas_activas',
        ]);
        abort_unless($row, 404);

        return $this->presentarParticipante($row, $this->nomina()->where('e.LEGAJO', $row->legajo)->get());
    }

    private function consultaParticipantes(int $periodo)
    {
        $cuentas = DB::table('users')->where('estado', 'ACTIVO')->whereNotNull('legajo')
            ->groupBy('legajo')->selectRaw('legajo, COUNT(*) as cantidad');

        return DB::table('edd_participantes as p')->where('p.periodo_id', $periodo)
            ->leftJoin('areas as a', 'a.ID_AREA', '=', 'p.area_id')
            ->leftJoin('edd_asignaciones as s', fn ($join) => $join->on('s.participante_id', '=', 'p.id')->where('s.current_slot', 1))
            ->leftJoin('users as u', 'u.id', '=', 's.evaluador_user_id')
            ->leftJoinSub($cuentas, 'cuentas', fn ($join) => $join->on('cuentas.legajo', '=', 'p.legajo'));
    }

    private function presentarParticipante(object $row, $nomina): array
    {
        $datos = (array) $row;
        $datos['contexto'] = json_decode($row->contexto_snapshot_json, true, flags: JSON_THROW_ON_ERROR);
        $datos['competencias_personales'] = $row->competencias_json === null ? null : json_decode($row->competencias_json, true, flags: JSON_THROW_ON_ERROR);
        $datos['nomina'] = $nomina->count() === 1 ? (array) $nomina->first() : null;

        return $datos;
    }

    public function competenciasArea(int $periodo, int $area): array
    {
        abort_unless(DB::table('areas')->where('ID_AREA', $area)->exists(), 404);
        $row = DB::table('edd_area_competencias')->where('periodo_id', $periodo)->where('area_id', $area)->first();

        return ['revision' => $row?->revision ?? 0, 'items' => $row ? json_decode($row->competencias_json, true, flags: JSON_THROW_ON_ERROR) : []];
    }

    public function competenciasEfectivas(array $participante): array
    {
        return $participante['competencias_personales']
            ?? $this->competenciasArea($participante['periodo_id'], $participante['area_id'])['items'];
    }

    public function textoCompetencias(array $items): string
    {
        return implode("\n", array_map(fn ($item) => $item['titulo'].(! empty($item['descripcion']) ? ': '.$item['descripcion'] : ''), $items));
    }

    public function agregar(int $periodo, array $legajos, int $actor): int
    {
        return DB::transaction(function () use ($periodo, $legajos, $actor) {
            $this->configuracion->bloquearPeriodo($periodo);
            $empleados = $this->nomina()->whereIn('e.LEGAJO', $legajos)->get();
            $agrupados = $empleados->groupBy('LEGAJO');
            foreach ($legajos as $legajo) {
                $coincidencias = $agrupados->get($legajo, collect());
                if ($coincidencias->count() !== 1 || $coincidencias->first()->ESTADO !== 'ACTIVO' || ! $coincidencias->first()->area_nombre) {
                    throw ValidationException::withMessages(['legajos' => 'La selección contiene un legajo inexistente, duplicado, inactivo o sin área válida. Actualizá la búsqueda.']);
                }
            }
            $cantidad = 0;
            foreach ($empleados as $empleado) {
                if (DB::table('edd_participantes')->where('periodo_id', $periodo)->where('legajo', $empleado->LEGAJO)->exists()) {
                    continue; // Reintento idempotente; nunca reincluye una exclusión.
                }
                $datos = [
                    'periodo_id' => $periodo, 'legajo' => $empleado->LEGAJO, 'area_id' => $empleado->ID_AREA,
                    'contexto_snapshot_json' => $this->json([
                        'nombre' => $empleado->COLABORADOR, 'area' => $empleado->area_nombre,
                        'rol_id' => $empleado->ID_ROL, 'rol' => $empleado->rol_nombre,
                        'servicio_id' => $empleado->ID_SERVICIOS, 'servicio' => $empleado->servicio_nombre, 'incorporado_at' => $this->ahora(),
                    ]),
                    'incluido' => true, 'revision' => 0, 'created_by' => $actor, 'updated_by' => $actor,
                    'created_at' => $this->ahora(), 'updated_at' => $this->ahora(),
                ];
                $id = DB::table('edd_participantes')->insertGetId($datos);
                $this->configuracion->evento($periodo, 'participante', $id, 'incluir_participante', $actor, null, $datos);
                $cantidad++;
            }

            return $cantidad;
        });
    }

    public function guardarArea(int $periodo, int $area, array $datos, int $actor): void
    {
        DB::transaction(function () use ($periodo, $area, $datos, $actor) {
            $this->configuracion->bloquearPeriodo($periodo);
            $actual = $this->competenciasArea($periodo, $area);
            $this->configuracion->verificarRevision($actual['revision'], $datos['revision']);
            $antes = DB::table('edd_area_competencias')->where('periodo_id', $periodo)->where('area_id', $area)->first();
            $contenido = ['competencias_json' => $this->json($this->parsearCompetencias($datos['competencias'], $actual['items'])),
                'revision' => $actual['revision'] + 1, 'updated_by' => $actor, 'updated_at' => $this->ahora()];
            if ($antes) {
                $id = $antes->id;
                DB::table('edd_area_competencias')->where('id', $id)->update($contenido);
            } else {
                $id = DB::table('edd_area_competencias')->insertGetId(array_merge($contenido, ['periodo_id' => $periodo, 'area_id' => $area, 'created_at' => $this->ahora()]));
            }
            $this->configuracion->evento($periodo, 'competencias_area', $id, 'guardar_competencias_area', $actor,
                $antes ? (array) $antes : null, (array) DB::table('edd_area_competencias')->find($id));
        });
    }

    public function guardarParticipante(int $periodo, int $id, array $datos, int $actor): void
    {
        DB::transaction(function () use ($periodo, $id, $datos, $actor) {
            $this->configuracion->bloquearPeriodo($periodo);
            $antes = $this->bloquearParticipante($periodo, $id, $datos['revision']);
            $base = $this->competenciasArea($periodo, $datos['area_id']);
            if ($datos['incluido'] && ! DB::table('empleados')->where('LEGAJO', $antes->legajo)->where('ESTADO', 'ACTIVO')->exists()) {
                throw ValidationException::withMessages(['incluido' => 'El colaborador ya no figura activo en la nómina.']);
            }
            $previos = $antes->competencias_json === null ? $base['items'] : json_decode($antes->competencias_json, true, flags: JSON_THROW_ON_ERROR);
            $competencias = $datos['modo_competencias'] === 'personal' ? $this->json($this->parsearCompetencias($datos['competencias'], $previos)) : null;
            DB::table('edd_participantes')->where('id', $id)->update([
                'area_id' => $datos['area_id'], 'incluido' => $datos['incluido'],
                'motivo_exclusion' => $datos['incluido'] ? null : $datos['motivo_exclusion'],
                'competencias_json' => $competencias, 'revision' => $antes->revision + 1,
                'updated_by' => $actor, 'updated_at' => $this->ahora(),
            ]);
            $paraAsignar = clone $antes;
            $paraAsignar->area_id = $datos['area_id'];
            $this->asignar($paraAsignar, $datos['incluido'] ? ($datos['evaluador_user_id'] ?? null) : null,
                $datos['funcion'], $actor, $datos['incluido'] ? 'Cambio de configuración' : $datos['motivo_exclusion']);
            $this->configuracion->evento($periodo, 'participante', $id, 'guardar_participante', $actor, (array) $antes,
                (array) DB::table('edd_participantes')->find($id));
        });
    }

    public function asignarVarios(int $periodo, array $datos, int $actor): void
    {
        DB::transaction(function () use ($periodo, $datos, $actor) {
            $this->configuracion->bloquearPeriodo($periodo);
            foreach ($datos['participantes'] as $id => $revision) {
                abort_unless(ctype_digit((string) $id) && (int) $id > 0, 422, 'Selección de colaboradores inválida.');
                $antes = $this->bloquearParticipante($periodo, (int) $id, $revision);
                if (! $antes->incluido) {
                    throw ValidationException::withMessages(['participantes' => 'La selección contiene un colaborador excluido.']);
                }
                $this->asignar($antes, $datos['evaluador_user_id'], $datos['funcion'], $actor, 'Asignación desde Evaluadores');
                DB::table('edd_participantes')->where('id', $id)->update([
                    'revision' => $antes->revision + 1, 'updated_by' => $actor, 'updated_at' => $this->ahora(),
                ]);
            }
        });
    }

    public function aplicarCompetencias(int $periodo, array $datos, int $actor): void
    {
        DB::transaction(function () use ($periodo, $datos, $actor) {
            $this->configuracion->bloquearPeriodo($periodo);
            foreach ($datos['participantes'] as $id => $revision) {
                abort_unless(ctype_digit((string) $id) && (int) $id > 0, 422, 'Selección de colaboradores inválida.');
                $antes = $this->bloquearParticipante($periodo, (int) $id, $revision);
                if (! $antes->incluido) {
                    throw ValidationException::withMessages(['participantes' => 'La selección contiene un colaborador excluido.']);
                }
                $previos = $antes->competencias_json === null ? [] : json_decode($antes->competencias_json, true, flags: JSON_THROW_ON_ERROR);
                $contenido = $datos['modo_competencias'] === 'area' ? null : $this->json($this->parsearCompetencias($datos['competencias'], $previos));
                DB::table('edd_participantes')->where('id', $id)->update([
                    'competencias_json' => $contenido, 'revision' => $antes->revision + 1,
                    'updated_by' => $actor, 'updated_at' => $this->ahora(),
                ]);
                $this->configuracion->evento($periodo, 'participante', $id, 'aplicar_competencias', $actor,
                    (array) $antes, (array) DB::table('edd_participantes')->find($id));
            }
        });
    }

    private function asignar(object $participante, ?int $evaluador, string $funcion, int $actor, string $motivo): void
    {
        if ($evaluador) {
            $usuario = DB::table('users')->where('id', $evaluador)->where('estado', 'ACTIVO')->first(['id', 'legajo']);
            if (! $usuario || ($usuario->legajo !== null && (int) $usuario->legajo === (int) $participante->legajo)) {
                throw ValidationException::withMessages(['evaluador_user_id' => 'Elegí una cuenta activa distinta del colaborador evaluado.']);
            }
            // La asignación explícita también registra al responsable en el área EDD.
            // Una inscripción en un área no otorga acceso a personas sin asignación.
            if (Schema::hasTable('edd_evaluador_areas')) {
                app(PlanificacionEdd::class)->registrarArea($participante->periodo_id, $evaluador, $participante->area_id, $actor);
            }
        }
        $actual = DB::table('edd_asignaciones')->where('participante_id', $participante->id)->where('current_slot', 1)->first();
        if (($actual ? (int) $actual->evaluador_user_id : null) === $evaluador && (! $evaluador || $actual->funcion === $funcion)) {
            return;
        }
        if ($actual) {
            DB::table('edd_asignaciones')->where('id', $actual->id)->update(['current_slot' => null, 'vigente_hasta' => $this->ahora()]);
        }
        $despues = ['evaluador_user_id' => null, 'motivo' => $motivo];
        $asignacionId = $actual?->id;
        if ($evaluador) {
            $despues = ['participante_id' => $participante->id, 'evaluador_user_id' => $evaluador,
                'funcion' => $funcion, 'current_slot' => 1, 'vigente_desde' => $this->ahora(),
                'created_by' => $actor, 'motivo' => $motivo];
            $asignacionId = DB::table('edd_asignaciones')->insertGetId($despues);
            $despues['id'] = $asignacionId;
        }
        $this->configuracion->evento($participante->periodo_id, 'asignacion', $asignacionId,
            'asignar_evaluador', $actor, $actual ? (array) $actual : null, $despues);
    }

    private function bloquearParticipante(int $periodo, int $id, int $revision): object
    {
        $row = DB::table('edd_participantes')->where('periodo_id', $periodo)->where('id', $id)->lockForUpdate()->first();
        abort_unless($row, 404);
        $this->configuracion->verificarRevision($row->revision, $revision);

        return $row;
    }

    public function parsearCompetencias(string $texto, array $anteriores): array
    {
        $lineas = array_values(array_filter(array_map('trim', preg_split('/\R/u', $texto)), fn ($linea) => $linea !== ''));
        if (count($lineas) < 1 || count($lineas) > 100) {
            throw ValidationException::withMessages(['competencias' => 'Ingresá entre 1 y 100 competencias, una por línea.']);
        }
        $codigos = array_column($anteriores, 'codigo', 'titulo');
        $titulos = [];
        $items = [];
        foreach ($lineas as $linea) {
            [$titulo, $descripcion] = array_pad(array_map('trim', explode(':', $linea, 2)), 2, '');
            $clave = mb_strtolower($titulo);
            if ($titulo === '' || mb_strlen($titulo) > 200 || mb_strlen($descripcion) > 2000 || isset($titulos[$clave])) {
                throw ValidationException::withMessages(['competencias' => 'Revisá los títulos: deben ser únicos y tener hasta 200 caracteres. Cada descripción admite hasta 2000 caracteres.']);
            }
            $titulos[$clave] = true;
            $items[] = ['codigo' => $codigos[$titulo] ?? (string) Str::uuid(), 'titulo' => $titulo, 'descripcion' => $descripcion];
        }

        return $items;
    }

    private function json(array $datos): string
    {
        return json_encode($datos, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    private function ahora(): string
    {
        return now('UTC')->format('Y-m-d H:i:s');
    }
}

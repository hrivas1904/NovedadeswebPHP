<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Carbon\Carbon;

class CronogramaController extends Controller
{
    public function viewCronograma()
    {
        return view('calendarios.cronogramaTrabajo');
    }

    public function listarCronoPeriodo()
    {
        return response()->json(DB::select('CALL SP_CRONO_PERIODO_LISTAR()'));
    }

    public function abrirCronoPeriodo(Request $request)
    {
        $data = $request->validate([
            'periodo' => 'required|date_format:Y-m',
            'visible' => 'required|boolean',
        ]);

        $existe = DB::select('SELECT periodo FROM crono_periodos WHERE periodo = ?', [$data['periodo']]);
        if ($existe) {
            return response()->json(['ok' => false, 'msg' => 'Ese período ya está abierto.'], 422);
        }

        [$anio, $mes] = explode('-', $data['periodo']);
        $dias = cal_days_in_month(CAL_GREGORIAN, (int) $mes, (int) $anio);
        $primerDia = (int) date('w', strtotime($data['periodo'] . '-01'));

        DB::statement('CALL SP_CRONO_PERIODO_ABRIR(?,?,?,?,?)', [
            $data['periodo'],
            $dias,
            $primerDia,
            $data['visible'],
            auth()->id(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function abrirAnnioPeriodo(Request $request)
    {
        $data = $request->validate(['anio' => 'required|integer|min:2020|max:2100']);
        $abiertos = [];

        DB::beginTransaction();
        try {
            for ($mes = 1; $mes <= 12; $mes++) {
                $periodo = sprintf('%d-%02d', $data['anio'], $mes);
                if (DB::select('SELECT periodo FROM crono_periodos WHERE periodo = ?', [$periodo])) {
                    continue;
                }
                $dias = cal_days_in_month(CAL_GREGORIAN, $mes, $data['anio']);
                $primerDia = (int) date('w', strtotime($periodo . '-01'));

                DB::statement('CALL SP_CRONO_PERIODO_ABRIR(?,?,?,?,?)', [
                    $periodo,
                    $dias,
                    $primerDia,
                    1,
                    auth()->id(),
                ]);
                $abiertos[] = $periodo;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'msg' => 'No se pudo completar la apertura del año.'], 500);
        }

        return response()->json(['ok' => true, 'abiertos' => $abiertos]);
    }

    public function toggleVisible(Request $request, string $periodo)
    {
        $data = $request->validate(['visible' => 'required|boolean']);
        DB::statement('CALL SP_CRONO_PERIODO_VISIBLE_TOGGLE(?,?)', [$periodo, $data['visible']]);
        return response()->json(['ok' => true]);
    }

    public function eliminar(string $periodo)
    {
        DB::statement('CALL SP_CRONO_PERIODO_ELIMINAR(?)', [$periodo]);
        return response()->json(['ok' => true]);
    }

    // ===== CT GRILLA / PUESTOS =====

    private function resolverAreaPermitida(Request $request): array
    {
        $user = Auth::user();
        $idArea = $request->input('id_area');
        $idServicio = $request->input('id_servicio');

        if ($user->rol !== 'Administrador/a') {
            $idArea = $user->area_id;

            if ($idServicio) {
                $pertenece = DB::select(
                    'SELECT ID_SERVICIOS FROM servicios WHERE ID_SERVICIOS = ? AND ID_AREA = ?',
                    [$idServicio, $idArea]
                );
                if (!$pertenece) {
                    abort(403, 'El servicio no pertenece a tu área.');
                }
            }
        } elseif (!$idArea) {
            abort(422, 'Falta el área.');
        }

        return [$idArea, $idServicio];
    }

    public function listarCronoGrilla(Request $request)
    {
        $data = $request->validate(['periodo' => 'required|date_format:Y-m']);
        [$idArea, $idServicio] = $this->resolverAreaPermitida($request);

        $filas = DB::select('CALL SP_CRONO_GRILLA_PUESTOS_LISTAR(?,?,?)', [$data['periodo'], $idArea, $idServicio]);
        return response()->json(['data' => $filas]);
    }

    public function crearCronoPuesto(Request $request)
    {
        $data = $request->validate([
            'periodo' => 'required|date_format:Y-m',
            'id_turno' => 'required|integer',
            'cantidad' => 'required|integer|min:1|max:50',
            'dotacion_minima' => 'required|integer|min:0',
        ]);

        [$idArea, $idServicio] = $this->resolverAreaPermitida($request);

        $res = DB::select('CALL SP_CRONO_PUESTO_CREAR(?,?,?,?,?,?,?)', [
            $data['periodo'],
            $idArea,
            $idServicio,
            $data['id_turno'],
            $data['cantidad'],
            $data['dotacion_minima'],
            Auth::id(),
        ]);

        if (!is_null($res[0]->error ?? null)) {
            return response()->json(['success' => false, 'message' => $res[0]->error], 422);
        }
        return response()->json(['success' => true, 'puesto_id' => $res[0]->puesto_id]);
    }

    public function ajustarCantidadCronoPuesto(Request $request)
    {
        $data = $request->validate(['puesto_id' => 'required|integer', 'delta' => 'required|integer|in:-1,1']);
        $res = DB::select('CALL SP_CRONO_PUESTO_CANTIDAD_AJUSTAR(?,?)', [$data['puesto_id'], $data['delta']]);

        if (!($res[0]->ok ?? 0)) {
            return response()->json(['success' => false, 'message' => 'No hay puestos vacantes para reducir.'], 422);
        }
        return response()->json(['success' => true]);
    }

    public function ajustarDotacionCronoPuesto(Request $request)
    {
        $data = $request->validate(['puesto_id' => 'required|integer', 'dotacion_minima' => 'required|integer|min:0']);
        DB::statement('CALL SP_CRONO_PUESTO_DOTACION_AJUSTAR(?,?)', [$data['puesto_id'], $data['dotacion_minima']]);
        return response()->json(['success' => true]);
    }

    public function eliminarCronoPuesto(Request $request)
    {
        $data = $request->validate(['puesto_id' => 'required|integer']);
        $res = DB::select('CALL SP_CRONO_PUESTO_ELIMINAR(?)', [$data['puesto_id']]);

        if (!($res[0]->ok ?? 0)) {
            return response()->json(['success' => false, 'message' => 'El puesto tiene personas asignadas, no se puede eliminar.'], 422);
        }
        return response()->json(['success' => true]);
    }

    private function validarAreaPermitida(int $idArea): void
    {
        $user = Auth::user();
        if ($user->rol !== 'Administrador/a' && (int) $idArea !== (int) $user->area_id) {
            abort(403, 'No tenés acceso a esa área.');
        }
    }

    public function listarCronoAreasActivas()
    {
        $areas = DB::select('CALL SP_CRONO_AREAS_ACTIVAS_LISTAR()');

        if (Auth::user()->rol !== 'Administrador/a') {
            $areas = array_values(array_filter($areas, fn($a) => (int) $a->ID_AREA === (int) Auth::user()->area_id));
        }

        return response()->json(['data' => $areas]);
    }

    public function listarCronoServiciosActivos(int $idArea)
    {
        $this->validarAreaPermitida($idArea);
        return response()->json(['data' => DB::select('CALL SP_CRONO_SERVICIOS_ACTIVOS_POR_AREA(?)', [$idArea])]);
    }

    public function listarCronoTurnosActivos(int $idArea)
    {
        $this->validarAreaPermitida($idArea);
        return response()->json(['data' => DB::select('CALL SP_CRONO_TURNOS_ACTIVOS_POR_AREA(?)', [$idArea])]);
    }

    public function listarCronoPeriodosVisibles()
    {
        return response()->json(['data' => DB::select('CALL SP_CRONO_PERIODOS_VISIBLES_LISTAR()')]);
    }

    // ===== CT PICKER PERSONAS =====

    private function validarSlotPermitido(int $slotId): object
    {
        $slot = DB::selectOne(
            'SELECT cs.id, cp.id_area FROM crono_slots cs
         INNER JOIN crono_puestos cp ON cp.id = cs.puesto_id
         WHERE cs.id = ?',
            [$slotId]
        );

        if (!$slot) {
            abort(404, 'Puesto no encontrado.');
        }

        $user = Auth::user();
        if ($user->rol !== 'Administrador/a' && (int) $slot->id_area !== (int) $user->area_id) {
            abort(403, 'No tenés acceso a ese puesto.');
        }

        return $slot;
    }

    public function buscarCronoEmpleados(Request $request)
    {
        $data = $request->validate([
            'periodo' => 'required|date_format:Y-m',
            'texto' => 'required|string|min:2',
        ]);

        return response()->json(['data' => DB::select('CALL SP_CRONO_EMPLEADOS_BUSCAR(?,?)', [$data['periodo'], $data['texto']])]);
    }

    public function asignarCronoSlot(Request $request)
    {
        $data = $request->validate([
            'slot_id' => 'required|integer',
            'legajo' => 'required|integer',
            'rol' => 'required|in:titular,apoyo',
        ]);

        $this->validarSlotPermitido($data['slot_id']);
        DB::statement('CALL SP_CRONO_SLOT_ASIGNAR(?,?,?)', [$data['slot_id'], $data['legajo'], $data['rol']]);

        return response()->json(['success' => true]);
    }

    public function quitarCronoSlot(Request $request)
    {
        $data = $request->validate(['slot_id' => 'required|integer']);
        $this->validarSlotPermitido($data['slot_id']);
        DB::statement('CALL SP_CRONO_SLOT_QUITAR(?)', [$data['slot_id']]);

        return response()->json(['success' => true]);
    }

    // ===== CT CELDA NOVEDAD =====

    public function listarCronoNovedadesActivas()
    {
        return response()->json(['data' => DB::select('CALL SP_CRONO_NOVEDADES_ACTIVAS_LISTAR()')]);
    }

    public function listarCronoAsignacionesDia(Request $request)
    {
        $data = $request->validate(['periodo' => 'required|date_format:Y-m']);
        [$idArea, $idServicio] = $this->resolverAreaPermitida($request);

        return response()->json(['data' => DB::select('CALL SP_CRONO_ASIGNACIONES_DIA_LISTAR(?,?,?)', [$data['periodo'], $idArea, $idServicio])]);
    }

    public function actualizarCronoCeldaNovedad(Request $request)
    {
        $data = $request->validate([
            'slot_id' => 'required|integer',
            'fecha' => 'required|date_format:Y-m-d',
            'id_novedad' => 'nullable|integer',
            'updated_at_esperado' => 'nullable|integer',
        ]);

        $this->validarSlotPermitido($data['slot_id']);

        $res = DB::select('CALL SP_CRONO_CELDA_NOVEDAD_ACTUALIZAR(?,?,?,?,?)', [
            $data['slot_id'],
            $data['fecha'],
            $data['id_novedad'] ?? null,
            Auth::id(),
            $data['updated_at_esperado'] ?? null,
        ]);

        $r = $res[0];
        if (!$r->ok) {
            return response()->json(['success' => false, 'message' => 'Alguien más modificó esta celda. Se refrescó con el valor actual.', 'updated_at' => $r->updated_at_ts], 409);
        }
        return response()->json(['success' => true, 'updated_at' => $r->updated_at_ts]);
    }

    // ===== CT FUNCIONES DIA =====

    public function listarCronoFuncionesActivas(int $idArea)
    {
        $this->validarAreaPermitida($idArea);
        return response()->json(['data' => DB::select('CALL SP_CRONO_FUNCIONES_ACTIVAS_POR_AREA(?)', [$idArea])]);
    }

    public function listarCronoSlotFuncionesDia(Request $request)
    {
        $data = $request->validate(['periodo' => 'required|date_format:Y-m']);
        [$idArea, $idServicio] = $this->resolverAreaPermitida($request);
        return response()->json(['data' => DB::select('CALL SP_CRONO_SLOT_FUNCIONES_DIA_LISTAR(?,?,?)', [$data['periodo'], $idArea, $idServicio])]);
    }

    public function actualizarCronoSlotFuncionDia(Request $request)
    {
        $data = $request->validate([
            'slot_id' => 'required|integer',
            'fecha' => 'required|date_format:Y-m-d',
            'id_funcion' => 'nullable|integer',
        ]);
        $this->validarSlotPermitido($data['slot_id']);

        $res = DB::select('CALL SP_CRONO_SLOT_FUNCION_DIA_ACTUALIZAR(?,?,?,?)', [
            $data['slot_id'],
            $data['fecha'],
            $data['id_funcion'] ?? null,
            Auth::id(),
        ]);

        if (!$res[0]->ok) {
            return response()->json(['success' => false, 'message' => 'Ese día no es un día de trabajo (DT) para este puesto.'], 422);
        }
        return response()->json(['success' => true]);
    }

    // ===== CT PINCEL =====

    public function pintarCronoSlotRango(Request $request)
    {
        $data = $request->validate([
            'slot_id' => 'required|integer',
            'fecha_desde' => 'required|date_format:Y-m-d',
            'fecha_hasta' => 'required|date_format:Y-m-d|after_or_equal:fecha_desde',
            'id_novedad' => 'nullable|integer',
            'id_funcion' => 'nullable|integer',
        ]);
        $this->validarSlotPermitido($data['slot_id']);

        DB::statement('CALL SP_CRONO_SLOT_PINTAR_RANGO(?,?,?,?,?,?)', [
            $data['slot_id'],
            $data['fecha_desde'],
            $data['fecha_hasta'],
            $data['id_novedad'] ?? null,
            $data['id_funcion'] ?? null,
            Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    // ===== CT COPIAR MES ANTERIOR =====

    public function copiarCronoMesAnterior(Request $request)
    {
        $data = $request->validate([
            'periodo_destino' => 'required|date_format:Y-m',
            'forzar' => 'nullable|boolean',
        ]);
        [$idArea, $idServicio] = $this->resolverAreaPermitida($request);

        $periodoOrigen = date('Y-m', strtotime($data['periodo_destino'] . '-01 -1 month'));

        $existeOrigen = DB::selectOne('SELECT periodo FROM crono_periodos WHERE periodo = ?', [$periodoOrigen]);
        if (!$existeOrigen) {
            return response()->json(['success' => false, 'message' => "No existe el período $periodoOrigen para copiar."], 422);
        }

        $cantidadOrigen = DB::selectOne(
            'SELECT COUNT(*) AS total FROM crono_puestos WHERE periodo = ? AND id_area = ? AND id_servicio_norm = IFNULL(?,0)',
            [$periodoOrigen, $idArea, $idServicio]
        );
        if (!$cantidadOrigen || $cantidadOrigen->total == 0) {
            return response()->json(['success' => false, 'message' => "El período $periodoOrigen no tiene puestos cargados en esta área/servicio."], 422);
        }

        $cantidadDestino = DB::selectOne(
            'SELECT COUNT(*) AS total FROM crono_puestos WHERE periodo = ? AND id_area = ? AND id_servicio_norm = IFNULL(?,0)',
            [$data['periodo_destino'], $idArea, $idServicio]
        );

        if ($cantidadDestino && $cantidadDestino->total > 0 && !($data['forzar'] ?? false)) {
            return response()->json([
                'success' => false,
                'requiereConfirmacion' => true,
                'cantidadExistente' => $cantidadDestino->total,
                'periodoOrigen' => $periodoOrigen,
            ], 409);
        }

        DB::statement('CALL SP_CRONO_PERIODO_COPIAR_ESTRUCTURA(?,?,?,?,?)', [
            $periodoOrigen,
            $data['periodo_destino'],
            $idArea,
            $idServicio,
            Auth::id(),
        ]);

        return response()->json(['success' => true, 'periodoOrigen' => $periodoOrigen]);
    }

    // ===== CT CONFLICTOS =====

    public function listarCronoConflictos(Request $request)
    {
        $data = $request->validate(['periodo' => 'required|date_format:Y-m']);
        [$idArea, $idServicio] = $this->resolverAreaPermitida($request);

        $filas = DB::select('CALL SP_CRONO_CONFLICTOS_DATOS(?,?,?)', [$data['periodo'], $idArea, $idServicio]);

        return response()->json(['data' => $this->calcularConflictosCrono($filas)]);
    }

    private function calcularConflictosCrono(array $filas): array
    {
        $porLegajo = [];
        foreach ($filas as $f) {
            $porLegajo[$f->legajo]['colaborador'] = $f->colaborador;
            $porLegajo[$f->legajo]['dias'][$f->fecha][] = $f;
        }

        $conflictos = [];

        foreach ($porLegajo as $legajo => $info) {
            $dias = $info['dias'];
            ksort($dias);

            // --- Regla 1: doble asignación el mismo día ---
            foreach ($dias as $fecha => $registros) {
                $trabajados = array_values(array_filter($registros, fn($r) => $r->codigo_novedad === null || $r->codigo_novedad === 'DT'));
                if (count($trabajados) > 1) {
                    $conflictos[] = [
                        'tipo' => 'doble_asignacion',
                        'legajo' => $legajo,
                        'colaborador' => $info['colaborador'],
                        'fecha' => $fecha,
                        'detalle' => 'Asignado a ' . count($trabajados) . ' puestos el mismo día: ' . implode(', ', array_map(fn($r) => $r->turno_nombre, $trabajados)),
                    ];
                }
            }

            // Un turno "trabajado" por fecha (si hubiera más de uno, ya quedó marcado en la regla 1)
            $trabajadosPorFecha = [];
            foreach ($dias as $fecha => $registros) {
                $t = array_filter($registros, fn($r) => $r->codigo_novedad === null || $r->codigo_novedad === 'DT');
                if ($t) $trabajadosPorFecha[$fecha] = reset($t);
            }
            $fechasTrabajadas = array_keys($trabajadosPorFecha);
            sort($fechasTrabajadas);

            // --- Regla 2: menos de 12hs de descanso entre turnos consecutivos ---
            for ($i = 0; $i < count($fechasTrabajadas) - 1; $i++) {
                $fechaHoy = $fechasTrabajadas[$i];
                $fechaManiana = $fechasTrabajadas[$i + 1];
                if ((strtotime($fechaManiana) - strtotime($fechaHoy)) !== 86400) continue; // no son días consecutivos

                $turnoHoy = $trabajadosPorFecha[$fechaHoy];
                $turnoManiana = $trabajadosPorFecha[$fechaManiana];

                $finHoy = strtotime("$fechaHoy {$turnoHoy->hora_fin}");
                if ($turnoHoy->cruza) $finHoy = strtotime('+1 day', $finHoy);
                $inicioManiana = strtotime("$fechaManiana {$turnoManiana->hora_inicio}");

                $horasDescanso = ($inicioManiana - $finHoy) / 3600;
                if ($horasDescanso < 12) {
                    $conflictos[] = [
                        'tipo' => 'descanso_insuficiente',
                        'legajo' => $legajo,
                        'colaborador' => $info['colaborador'],
                        'fecha' => $fechaManiana,
                        'detalle' => sprintf('Solo %.1fhs de descanso entre el %s (%s) y el %s (%s)', $horasDescanso, $fechaHoy, $turnoHoy->turno_nombre, $fechaManiana, $turnoManiana->turno_nombre),
                    ];
                }
            }

            // --- Regla 3: 7 días corridos de trabajo sin franco ---
            $racha = 0;
            $inicioRacha = null;
            foreach (array_keys($dias) as $fecha) {
                if (isset($trabajadosPorFecha[$fecha])) {
                    if ($racha === 0) $inicioRacha = $fecha;
                    $racha++;
                    if ($racha === 7) {
                        $conflictos[] = [
                            'tipo' => 'sin_descanso_semanal',
                            'legajo' => $legajo,
                            'colaborador' => $info['colaborador'],
                            'fecha' => $fecha,
                            'detalle' => "Lleva 7 días corridos de trabajo, desde el $inicioRacha",
                        ];
                    }
                } else {
                    $racha = 0;
                    $inicioRacha = null;
                }
            }
        }

        return $conflictos;
    }
}

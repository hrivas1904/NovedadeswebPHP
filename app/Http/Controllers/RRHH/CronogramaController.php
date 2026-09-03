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
}

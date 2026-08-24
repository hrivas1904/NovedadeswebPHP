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
        $primerDia = (int) date('w', strtotime($data['periodo'].'-01'));

        DB::statement('CALL SP_CRONO_PERIODO_ABRIR(?,?,?,?,?)', [
            $data['periodo'], $dias, $primerDia, $data['visible'], auth()->id(),
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
                $primerDia = (int) date('w', strtotime($periodo.'-01'));

                DB::statement('CALL SP_CRONO_PERIODO_ABRIR(?,?,?,?,?)', [
                    $periodo, $dias, $primerDia, 1, auth()->id(),
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
}

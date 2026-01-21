<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function colaboradoresActivos(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $result = DB::select('CALL SP_COLABS_ACTIVOS(?, ?)', [$desde, $hasta]);
        return response()->json($result[0]);
    }

    public function colaboradoresBaja(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $result = DB::select('CALL SP_COLABS_BAJA(?, ?)', [$desde, $hasta]);
        return response()->json($result[0]);
    }

    public function historicoNovedades(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $result = DB::select('CALL SP_TOTAL_NOVEDADES_HISTORICO(?, ?)', [$desde, $hasta]);
        return response()->json($result[0]);
    }

    public function novedadesMesActual()
    {
        $result = DB::select('CALL SP_NOVEDADES_MES_ACTUAL()');
        return response()->json($result[0]);
    }

    public function novedadesMasFrecuente(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $result = DB::select('CALL SP_NOVEDAD_MAS_FRECUENTE(?, ?)', [$desde, $hasta]);

        if (count($result) === 0) {
            return response()->json([
                'novedad' => null,
                'cantidad' => 0
            ]);
        }

        return response()->json($result[0]);
    }

    public function novedadesMenosFrecuente(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $result = DB::select('CALL SP_NOVEDAD_MENOS_FRECUENTE(?, ?)', [$desde, $hasta]);

        if (count($result) === 0) {
            return response()->json([
                'novedad' => null,
                'cantidad' => 0
            ]);
        }

        return response()->json($result[0]);
    }

    public function areaMasNovedades(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $result = DB::select('CALL SP_AREA_CON_MAS_NOVEDADES(?, ?)', [$desde, $hasta]);

        if (count($result) === 0) {
            return response()->json([
                'area' => null,
                'cantidad' => 0
            ]);
        }

        return response()->json($result[0]);
    }

    public function areaMenosNovedades(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $result = DB::select('CALL SP_AREA_CON_MENOS_NOVEDADES(?, ?)', [$desde, $hasta]);

        if (count($result) === 0) {
            return response()->json([
                'area' => null,
                'cantidad' => 0
            ]);
        }

        return response()->json($result[0]);
    }

    public function novedadesPorTipo(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $result = DB::select('CALL SP_NOVEDAD_POR_TIPO(?, ?)', [$desde, $hasta]);
        return response()->json($result);
    }

    public function novedadesPorArea(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $rows = DB::select('CALL SP_NOVEDADES_POR_AREA(?, ?)', [$desde, $hasta]);

        return response()->json($rows);
    }

    public function novedadesPorMes()
    {
        $rows = DB::select('CALL SP_NOVEDAD_POR_MES()');

        return response()->json($rows);
    }

    public function topEmpleadosNovedades(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $rows = DB::select(
            'CALL SP_NOVEDADESXEMPLEADOS(?, ?)',
            [$desde, $hasta]
        );

        return response()->json($rows);
    }
}

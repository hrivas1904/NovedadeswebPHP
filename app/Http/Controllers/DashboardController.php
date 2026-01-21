<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function novedadesPorTipo(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $rows = DB::select('CALL SP_NOVEDAD_POR_TIPO(?, ?)', [$desde, $hasta]);
        return response()->json($rows);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarioServController extends Controller
{
    public function calendarioServicios()
    {
        return view('calendarios.calendarioServ');
    }

    public function listarColaboradoresArea(Request $request)
    {
        $idArea = $request->idArea;

        if (!$idArea) {
            return response()->json([]);
        }

        $colaboradores = DB::select(
            'CALL SP_LISTA_COLABS_CALENDARIO(?)',
            [$idArea]
        );

        $data = [];

        foreach ($colaboradores as $c) {
            $data[] = [
                'id' => $c->legajo,
                'text' => $c->colaborador,
                'legajo' => $c->legajo,
                'servicio' => $c->nombre
            ];
        }

        return response()->json($data);
    }
}

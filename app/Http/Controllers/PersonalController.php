<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PersonalController extends Controller
{
    public function nominaPersonal()
    {
        return view('personal.nominaPersonal');
    }

    public function controlAsistencia()
    {
        return view('personal.controlAsistencia');
    }

    public function cronogramaPersonal()
    {
        return view('personal.cronogramaPersonal');
    }

    public function registroAsistencia()
    {
        return view('personal.registroAsistencia');
    }

    public function listarAreas()
    {
        try {
            $areas = DB::select('CALL SP_LISTA_AREAS()');

            return response()->json($areas);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener áreas',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}

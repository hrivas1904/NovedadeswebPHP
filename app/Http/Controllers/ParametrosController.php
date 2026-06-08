<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ParametrosController extends Controller
{
    public function configParametrosGenerales(){
        return view('ajustes.parametrizacion');
    }

    public function listarServiciosColab(){
        try {
            $servicios = DB::select('CALL SP_LISTAR_SERVICIOS_COLAB()');

            return response()->json($servicios);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener servicios',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}

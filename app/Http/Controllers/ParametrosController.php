<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ParametrosController extends Controller
{
    public function configParametrosGenerales()
    {
        return view('ajustes.parametrizacion');
    }

    public function listarServiciosColab()
    {
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

    public function crearArea(Request $request)
    {
        try {

            $request->validate([
                'nombreArea' => 'required|string|max:200'
            ]);

            DB::statement(
                'CALL SP_CREAR_NUEVA_AREA(?)',
                [$request->nombreArea]
            );

            return response()->json([
                'success' => true,
                'message' => 'Área creada correctamente.'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

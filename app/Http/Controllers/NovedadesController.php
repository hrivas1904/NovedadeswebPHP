<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class NovedadesController extends Controller
{
    public function registroNovedades()
    {
        return view('novedades.registroNovedades');
    }

    public function controlNovedades()
    {
        return view('novedades.controlNovedades');
    }

    public function configNovedades()
    {
        return view('novedades.configNovedades');
    }

    public function listarCategorias()
    {
        try {
            $categorias = DB::select('CALL SP_LISTA_CATEG_NOVEDADES()');

            return response()->json($categorias);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error al cargar categorías',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function listarNovedadesPorCategoria($id)
    {
        try {
            $novedades = DB::select('CALL SP_LISTA_NOVEDADES(?)', [$id]);

            return response()->json($novedades);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error al cargar novedades',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function cargarTablaNovedades()
    {
        try {
            // Ejecutamos el Stored Procedure
            $data = DB::select('CALL SP_TABLA_NOVEDADES()');

            // Retornamos los datos envueltos en una propiedad 'data' para DataTables
            return response()->json([
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

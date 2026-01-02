<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
            $rol = Auth::user()->rol;

            $categorias = DB::select(
                'CALL SP_LISTA_CATEG_NOVEDADES_POR_ROL(?)',
                [$rol]
            );

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
            $novedades = DB::select('CALL SP_LISTA_NOVEDADESXCATEG(?)', [$id]);

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
            $data = DB::select('CALL SP_TABLA_NOVEDADES()');
            return response()->json([
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function registrarNovedad(Request $request)
    {
        try {

            DB::statement(
                "CALL SP_REGISTRAR_NOVEDAD(
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @mensaje
            )",
                [
                    $request->legajo,
                    $request->idNovedad,
                    now(), // fecha registro
                    $request->fechaDesde,
                    $request->fechaHasta,
                    $request->fechaAplicacion,
                    $request->valor1 ?? null,
                    $request->valor2 ?? null,
                    $request->centroCosto ?? null,
                    $request->duracion,
                    auth()->user()->name,
                    $request->descripcion?? null
                ]
            );

            $resultado = DB::select("SELECT @mensaje AS mensaje");

            return response()->json([
                'success' => true,
                'mensaje' => $resultado[0]->mensaje
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al registrar la novedad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listarNovedadesPorArea(Request $request)
    {
        try {
            $areaId = ($request->area_id && $request->area_id != "") ? $request->area_id : null;

            $ListaNovedades = DB::select("CALL SP_LISTA_NOVEDADES_REGISTRADAS(?)", [$areaId]);

            return response()->json([
                'data' => $ListaNovedades
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }
}

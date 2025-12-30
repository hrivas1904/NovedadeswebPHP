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
            $data = DB::select('CALL SP_TABLA_NOVEDADES()');
            return response()->json([
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $fechaCarga = Carbon::now()->format('Y-m-d');
            $fechaDesde = Carbon::parse($request->fechaDesde)->format('Y-m-d');
            $fechaHasta = Carbon::parse($request->fechaHasta)->format('Y-m-d');

            DB::statement("CALL SP_REGISTRAR_NOVEDAD(?, ?, ?, ?, ?, ?, ?, @p_mensaje)", [
                $request->legajo,
                $request->codigoNovedad,
                $fechaCarga,
                $fechaDesde,
                $fechaHasta,
                $request->duracion,
                Auth::user()->name
            ]);

            $resultado = DB::select("SELECT @p_mensaje as mensaje")[0];

            if ($resultado->mensaje === 'Novedad registrada correctamente') {
                return response()->json(['success' => true, 'message' => $resultado->mensaje]);
            } else {
                return response()->json(['success' => false, 'message' => $resultado->mensaje], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function listarNovedadesPorArea(Request $request)
    {
        try {
            $areaId = ($request->area_id && $request->area_id != "") ? $request->area_id : null;

            $ListaNovedades = DB::select("CALL SP_LISTA_NOVEDADES(?)", [$areaId]);

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

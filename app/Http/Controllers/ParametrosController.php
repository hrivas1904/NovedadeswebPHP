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

            DB::statement("SET @codigo = 0");
            DB::statement("SET @mensaje = ''");

            DB::statement(
                "CALL SP_CREAR_NUEVA_AREA(?, @codigo, @mensaje)",
                [$request->nombreArea]
            );

            $resultado = DB::selectOne(
                "SELECT @codigo AS codigo, @mensaje AS mensaje"
            );

            return response()->json([
                'success' => true,
                'codigo' => $resultado->codigo,
                'message' => $resultado->mensaje
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'codigo' => -1,
                'message' => 'Ocurrió un error al procesar la solicitud'
            ], 500);
        }
    }

    public function verDetalleArea(Request $request)
    {

        try {
            $idArea = $request->idArea;

            $data = DB::select('CALL SP_VER_AREA(?)', [$idArea]);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function editarArea(Request $request)
    {
        try {
            DB::statement('CALL SP_MODIFICAR_AREA(?, ?)', [
                $request->idArea,
                $request->nombre
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Área actualizada correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminarArea($id)
    {
        try {
            DB::statement("CALL SP_ELIMINAR_AREA(?)", [$id]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Área eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al eliminar el área'
            ], 500);
        }
    }

    public function crearCategoria(Request $request)
    {
        try {

            DB::statement("SET @codigo = 0");
            DB::statement("SET @mensaje = ''");

            DB::statement(
                "CALL SP_CREAR_NUEVA_CATEG(?, @codigo, @mensaje)",
                [$request->nombreCateg]
            );

            $resultado = DB::selectOne(
                "SELECT @codigo AS codigo, @mensaje AS mensaje"
            );

            return response()->json([
                'success' => true,
                'codigo' => $resultado->codigo,
                'message' => $resultado->mensaje
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'codigo' => -1,
                'message' => 'Ocurrió un error al procesar la solicitud'
            ], 500);
        }
    }

    public function verDetalleCateg(Request $request)
    {

        try {
            $idCateg = $request->idCateg;

            $data = DB::select('CALL SP_VER_CATEGORIA(?)', [$idCateg]);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function editarCateg(Request $request)
    {
        try {
            DB::statement('CALL SP_MODIFICAR_CATEG(?, ?)', [
                $request->idCateg,
                $request->nombre
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoría actualizada correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminarCateg($id)
    {
        try {
            DB::statement("CALL SP_ELIMINAR_CATEGORIA(?)", [$id]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Categoría eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al eliminar la categoría'
            ], 500);
        }
    }

    public function verDetalleServicio(Request $request)
    {

        try {
            $idServ = $request->idServ;

            $data = DB::select('CALL SP_VER_SERVICIOS(?)', [$idServ]);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function editarServicio(Request $request)
    {
        try {
            DB::statement('CALL SP_MODIFICAR_SERVICIO(?, ?, ?)', [
                $request->idServ,
                $request->nombre,
                $request->idArea,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Servicio actualizado correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminarServicio($id)
    {
        try {
            DB::statement("CALL SP_ELIMINAR_SERVICIO(?)", [$id]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Servicio eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al eliminar el servicio'
            ], 500);
        }
    }

    public function crearServicio(Request $request)
    {
        try {

            DB::statement("SET @codigo = 0");
            DB::statement("SET @mensaje = ''");

            DB::statement(
                "CALL SP_CREAR_NUEVO_SERVICIO(?,?, @codigo, @mensaje)",
                [$request->servicio, $request->area]
            );

            $resultado = DB::selectOne(
                "SELECT @codigo AS codigo, @mensaje AS mensaje"
            );

            return response()->json([
                'success' => true,
                'codigo' => $resultado->codigo,
                'message' => $resultado->mensaje
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'codigo' => -1,
                'message' => 'Ocurrió un error al procesar la solicitud'
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\WebPushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class NotificacionController extends Controller
{
    public function registrarNotificacion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contenido' => 'required|string|max:5000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'mensaje' => $validator->errors()->first()
            ], 422);
        }

        try {

            DB::statement(
                "CALL SP_PUBLICAR_NOTIFICACION(?, ?, ?, @p_mensaje)",
                [
                    Auth::user()->id,
                    $request->contenido,
                    $request->titulo
                ]
            );

            $resultado = DB::select("SELECT @p_mensaje AS mensaje");

            /*$push = new WebPushService();

            $push->send(
                Auth::id(),
                "PRUEBA PUSH",
                "Si ves esto funciona",
                "/"
            );*/

            return response()->json([
                'success' => true,
                'mensaje' => $resultado[0]->mensaje
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al registrar la notificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listarNotificaciones()
    {
        try {

            $rol = Auth::user()->rol;

            $notificaciones = DB::select(
                'CALL SP_LISTA_NOTIFICACIONES(?)',
                [$rol]
            );

            return response()->json($notificaciones);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Error al obtener notificaciones'
            ], 500);
        }
    }

    public function eliminarNotificacion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idNotificacion' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'mensaje' => 'El ID de notificación es obligatorio'
            ], 422);
        }

        try {
            DB::statement(
                "CALL SP_ELIMINAR_NOTIFICACION(?, @p_mensaje)",
                [$request->idNotificacion]
            );

            $resultado = DB::select("SELECT @p_mensaje AS mensaje");

            return response()->json([
                'success' => true,
                'mensaje' => $resultado[0]->mensaje
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al eliminar la notificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerFeriados()
    {
        $year = now()->year;

        $response = Http::get(
            "https://date.nager.at/api/v3/PublicHolidays/$year/AR"
        );

        if ($response->successful()) {

            return response()->json([
                'success' => true,
                'data' => $response->json()
            ]);
        }

        return response()->json([
            'success' => false
        ], 500);
    }
}

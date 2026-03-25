<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AlertasController extends Controller
{
    public function listar()
    {
        $userId = Auth::user()->id;

        $alertas = DB::select("
            SELECT id, mensaje, modulo, idReferencia, fecha, url
            FROM alertas_usuario
            WHERE usuario_destino = ?
            AND leida = 0
            ORDER BY fecha DESC
            LIMIT 10
        ", [$userId]);

        return response()->json([
            'data' => $alertas
        ]);
    }

    public function marcarLeida(Request $request)
    {
        DB::update("
        UPDATE alertas_usuario
        SET leida = 1
        WHERE id = ?
    ", [$request->id]);

        return response()->json([
            'ok' => true
        ]);
    }

    public function limpiarTodas()
    {
        $userId = Auth::user()->id;

        DB::update("
        UPDATE alertas_usuario
        SET leida = 1
        WHERE usuario_destino = ?
        AND leida = 0
    ", [$userId]);

        return response()->json([
            'ok' => true
        ]);
    }
}

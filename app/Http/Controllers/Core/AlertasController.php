<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

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

    public function enviar(Request $request)
    {
        if (!in_array(Auth::user()->rol, ['ADMINISTRADOR', 'SUPERVISOR_CALIDAD'])) {
            return response()->json(['ok' => false, 'error' => 'No autorizado'], 403);
        }

        $request->validate([
            'mensaje' => 'required|string|max:500',
            'url' => 'nullable|string|max:500',
        ]);

        DB::insert("
            INSERT INTO alertas_usuario
                (tipo, modulo, mensaje, idReferencia, leida, fecha, usuario_destino, usuario_origen, url, push_enviado)
            SELECT
                'AVISO', 'COMUNICADOS', ?, 0, 0, NOW(), id, ?, ?, 0
            FROM users
            WHERE estado = 'ACTIVO'
        ", [
            $request->mensaje,
            Auth::id(),
            $request->url ?? '/',
        ]);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Aviso enviado a los usuarios activos'
        ]);
    }
}

<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TicketController extends Controller
{
    public function registrar(Request $request)
    {
        try {
            $request->validate([
                'tipo' => 'required|string|max:100',
                'descripcion' => 'required|string|max:5000',
                'area' => 'required|string|max:50',
            ]);

            $user = Auth::user();
            $legajo = $user->legajo;
            $idUser = $user->id;

            if (!$legajo) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'El usuario autenticado no tiene legajo asociado.'
                ], 400);
            }

            DB::statement(
                "CALL SP_REGISTRAR_TICKET(?, ?, ?, ?, ?)",
                [
                    $request->tipo,
                    $request->descripcion,
                    $legajo,
                    $request->area,
                    $idUser
                ]
            );

            return response()->json([
                'ok' => true,
                'mensaje' => 'Ticket registrado correctamente. Será revisado a la brevedad.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al registrar ticket', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al registrar ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listar()
    {
        try {

            $rol = Auth::user()->rol;
            $legajo = Auth::user()->legajo;

            $tickets = DB::select(
                "CALL SP_LISTA_TICKET(?, ?)",
                [$rol, $legajo]
            );

            return response()->json([
                'data' => $tickets
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function resolver(Request $request)
    {
        try {

            if (!$request->idTicket) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Ticket inválido'
                ]);
            }

            $idTicket = $request->idTicket;
            $quienResuelve = Auth::user()->name;
            $legajo = Auth::user()->legajo;
            $idUser = Auth::user()->id;

            DB::statement(
                "CALL SP_RESOLVER_TICKET(?, ?, ?, ?, @p_msj)",
                [
                    $idTicket,
                    $quienResuelve,
                    $legajo,
                    $idUser
                ]
            );

            $mensaje = DB::selectOne("SELECT @p_msj as mensaje");

            return response()->json([
                'ok' => true,
                'mensaje' => $mensaje->mensaje
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al resolver ticket',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function verChat(Request $request)
    {
        $id = $request->id;

        $data = DB::select('CALL SP_VER_CHAT_TICKET(?)', [$id]);

        return response()->json($data);
    }

    public function responderTicket(Request $request)
    {
        $idTicket = $request->idTicket;
        $mensaje = $request->mensaje;
        $usuarioId = Auth::user()->id;

        DB::statement('CALL SP_RESPONDER_TICKET(?, ?, ?, @p_msj)', [
            $idTicket,
            $mensaje,
            $usuarioId
        ]);

        $result = DB::select("SELECT @p_msj as mensaje");

        return response()->json([
            'ok' => true,
            'mensaje' => $result[0]->mensaje ?? ''
        ]);
    }
}

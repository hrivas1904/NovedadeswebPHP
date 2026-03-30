<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function registrar(Request $request)
    {
        try {

            $legajo = Auth::user()->legajo;
            $idUser = Auth::user()->id;

            DB::statement(
                "CALL SP_REGISTRAR_TICKET(?, ?, ?, ?, ?, @p_msj)",
                [
                    $request->tipo,
                    $request->descripcion,
                    $legajo,
                    $request->area,
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
                'mensaje' => 'Error al registrar ticket',
                'error' => $e->getMessage()
            ]);
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

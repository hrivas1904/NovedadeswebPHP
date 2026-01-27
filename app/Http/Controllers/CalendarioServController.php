<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CalendarioServController extends Controller
{
    public function calendarioServicios()
    {
        return view('calendarios.calendarioServ');
    }

    public function listarColaboradoresArea(Request $request)
    {
        $idArea = $request->idArea;

        if (!$idArea) {
            return response()->json([]);
        }

        $colaboradores = DB::select(
            'CALL SP_LISTA_COLABS_CALENDARIO(?)',
            [$idArea]
        );

        $data = [];

        foreach ($colaboradores as $c) {
            $data[] = [
                'id' => $c->legajo,
                'text' => $c->colaborador,
                'legajo' => $c->legajo,
                'servicio' => $c->nombre
            ];
        }

        return response()->json($data);
    }

    public function guardarEvento(Request $request)
    {
        try {
            $request->validate([
                'legajo' => 'required',
                'fechaDesde' => 'required|date',
                'fechaHasta' => 'required|date',
                'caja' => 'required',
                'turno' => 'required'
            ]);

            $registrante = Auth::user()->name ?? 'Sistema';

            DB::statement("CALL SP_GUARDAR_EVENTO_CALENDARIO(?, ?, ?, ?, ?, ?)", [
                $request->legajo,
                $request->fechaDesde,
                $request->fechaHasta,
                $request->caja,
                $request->turno,
                $registrante
            ]);

            return response()->json(['success' => true, 'message' => 'Evento guardado correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

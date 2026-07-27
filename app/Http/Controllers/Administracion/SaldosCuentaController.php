<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaldosCuentaController extends Controller
{
    public function index()
    {
        return view('administracion.saldosCuentas.saldosCuentas', ['mes' => now()->format('Y-m')]);
    }

    public function data(Request $request)
    {
        $mes = $request->input('mes', now()->format('Y-m'));

        $rows = collect(DB::select('CALL SP_FF_SALDOS_CUENTA_LISTAR(?)', [$mes]));

        return response()->json([
            'pesos' => $rows->where('moneda', 'PESOS')->values(),
            'usd'   => $rows->where('moneda', 'USD')->values(),
        ]);
    }

    public function guardar(Request $request)
    {
        $validated = $request->validate([
            'cuenta' => 'required|string',
            'mes'    => 'required|date_format:Y-m',
            'monto'  => 'required|numeric',
        ]);

        DB::statement('CALL SP_FF_SALDO_INICIAL_GUARDAR(?,?,?,?)', [
            $validated['cuenta'],
            $validated['mes'],
            $validated['monto'],
            auth()->id(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function eliminar(Request $request)
    {
        $validated = $request->validate([
            'cuenta' => 'required|string',
            'mes'    => 'required|date_format:Y-m',
        ]);

        DB::statement('CALL SP_FF_SALDO_INICIAL_ELIMINAR(?,?)', [
            $validated['cuenta'],
            $validated['mes'],
        ]);

        return response()->json(['ok' => true]);
    }
}
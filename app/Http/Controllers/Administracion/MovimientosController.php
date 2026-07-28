<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Support\ClasificadorOperacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimientosController extends Controller
{
    public function movimientosView()
    {
        $cuentas = collect(DB::select('SELECT id, nombre FROM ff_cuentas WHERE activo = 1 ORDER BY orden'));
        $conceptos = collect(DB::select('SELECT nombre FROM ff_conceptos WHERE activo = 1 ORDER BY orden'))->pluck('nombre');

        return view('administracion.movimientos.movimientos', compact('cuentas', 'conceptos'));
    }

    public function movimientosData(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        if ($length <= 0) {
            $length = 100000;
        }

        $rows = DB::select('CALL SP_FF_MOVIMIENTOS_LISTAR(?,?,?,?,?,?,?,?,?,?)', [
            $request->input('fecha_desde') ?: null,
            $request->input('fecha_hasta') ?: null,
            $request->input('cuenta') ?: null,
            $request->input('estado') ?: null,
            $request->input('operacion') ?: null,
            $request->input('concepto') ?: null,
            $request->input('subconcepto') ?: null,
            $request->input('buscar') ?: null,
            $length,
            $start,
        ]);

        $total = count($rows) > 0 ? $rows[0]->total_filas : 0;

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $rows,
        ]);
    }

    public function movimientosGuardarManual(Request $request)
    {
        $validated = $request->validate([
            'fecha'       => 'required|date',
            'ejecucion'   => 'required|in:EJECUTADO,PRESUPUESTO,PENDIENTE,CUMPLIDO',
            'cuenta'      => 'required|string',
            'seccion'     => 'required|in:2 INGRESOS,3 EGRESOS,4 TARJETAS D/C,5 TRANSFERENCIAS,6 SEÑAS',
            'concepto'    => 'required|string',
            'subconcepto' => 'nullable|string',
            'detalle'     => 'required|string',
            'importe'     => 'required|numeric',
        ]);

        $operacion = ClasificadorOperacion::resolver(
            $validated['cuenta'],
            $validated['concepto'],
            $validated['seccion'],
            (float) $validated['importe']
        );

        $resultado = DB::select('CALL SP_FF_MOVIMIENTO_INSERTAR(?,?,?,?,?,?,?,?,?,?,?)', [
            $validated['fecha'],
            $validated['cuenta'],
            $validated['concepto'],
            $validated['subconcepto'] ?? null,
            $validated['detalle'],
            $validated['importe'],
            $validated['ejecucion'],
            $operacion,
            $validated['seccion'],
            'MANUAL',
            auth()->id(),
        ]);

        return response()->json(['id' => $resultado[0]->id]);
    }

    public function actualizarEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'ejecucion' => 'required|in:PRESUPUESTO,CUMPLIDO',
        ]);

        DB::statement('CALL SP_FF_MOVIMIENTO_ACTUALIZAR_ESTADO(?,?)', [
            $id,
            $validated['ejecucion'],
        ]);

        return response()->json(['ok' => true]);
    }

    public function actualizarFecha(Request $request, $id)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
        ]);

        DB::statement('CALL SP_FF_MOVIMIENTO_ACTUALIZAR_FECHA(?,?)', [
            $id,
            $validated['fecha'],
        ]);

        return response()->json(['ok' => true]);
    }

    public function duplicar(Request $request, $id)
    {
        $resultado = DB::select('CALL SP_FF_MOVIMIENTO_DUPLICAR(?,?)', [
            $id,
            auth()->id(),
        ]);

        return response()->json(['id' => $resultado[0]->id]);
    }

    public function eliminar($id)
    {
        DB::statement('CALL SP_FF_MOVIMIENTO_ELIMINAR(?)', [$id]);

        return response()->json(['ok' => true]);
    }
}

<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDO;

class ConciliacionesController extends Controller
{
    public function conciliacionesHomeView()
    {
        return view('administracion.conciliaciones.conciliacionesHome', [
            'conceptos' => $this->obtenerConceptos()
        ]);
    }

    public function conciliacionesMacroView()
    {
        return view('administracion.conciliaciones.conciliacionesMacro', [
            'conceptos' => $this->obtenerConceptos(),
        ]);
    }

    public function conciliacionesNacionView()
    {
        return view('administracion.conciliaciones.conciliacionesNacion', [
            'conceptos' => $this->obtenerConceptos(),
        ]);
    }

    public function conciliacionesFrances986View()
    {
        return view('administracion.conciliaciones.conciliacionesFrances986', [
            'conceptos' => $this->obtenerConceptos(),
        ]);
    }

    public function conciliacionesFrances1001View()
    {
        return view('administracion.conciliaciones.conciliacionesFrances1001', [
            'conceptos' => $this->obtenerConceptos(),
        ]);
    }

    private function obtenerConceptos()
    {
        return collect(DB::select('SELECT nombre FROM ff_conceptos WHERE activo = 1 ORDER BY orden'))
            ->pluck('nombre');
    }

    public function data(Request $request)
    {
        $banco = $request->input('banco');
        $fechaHasta = $request->input('fechaHasta', now()->format('Y-m-d'));

        $pdo  = DB::connection()->getPdo();
        $stmt = $pdo->prepare('CALL SP_FF_CONCILIACION_LISTAR(?, ?)');
        $stmt->execute([$banco, $fechaHasta]);

        $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->nextRowset();
        $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return response()->json([
            'resumen'     => $resumen,
            'movimientos' => $movimientos,
        ]);
    }

    public function actualizarEstado(Request $request, $id)
    {
        $request->validate(['estado' => 'nullable|in:FINN,QR,']);

        DB::statement('CALL SP_FF_MOVIMIENTO_ACTUALIZAR_ESTADO_CONC(?,?)', [
            $id,
            $request->input('estado') ?: null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function actualizarEstadoMasivo(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|min:1',
            'estado' => 'nullable|in:FINN,QR,',
        ]);

        foreach ($request->input('ids') as $id) {
            DB::statement('CALL SP_FF_MOVIMIENTO_ACTUALIZAR_ESTADO_CONC(?,?)', [
                $id,
                $request->input('estado') ?: null,
            ]);
        }

        return response()->json(['ok' => true, 'actualizados' => count($request->input('ids'))]);
    }

    public function actualizarComprobante(Request $request, $id)
    {
        $request->validate(['nroComprobante' => 'nullable|string|max:50']);

        DB::statement('CALL SP_FF_MOVIMIENTO_ACTUALIZAR_COMPROBANTE(?,?)', [
            $id,
            $request->input('nroComprobante'),
        ]);

        return response()->json(['ok' => true]);
    }
}

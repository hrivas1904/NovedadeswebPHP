<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Support\ClasificadorOperacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class MovimientosController extends Controller
{
    public function movimientosView()
    {
        $cuentas = collect(DB::select('SELECT id, nombre FROM ff_cuentas WHERE activo = 1 ORDER BY orden'));
        $conceptos = collect(DB::select('SELECT nombre FROM ff_conceptos WHERE activo = 1 ORDER BY orden'))->pluck('nombre');
        $subconceptosPorConcepto = $this->obtenerSubconceptosPorConcepto();

        return view('administracion.movimientos.movimientos', compact('cuentas', 'conceptos', 'subconceptosPorConcepto'));
    }

    private function obtenerSubconceptosPorConcepto()
    {
        $rows = DB::select('CALL SP_FF_SUBCONCEPTOS_LISTAR_TODOS()');

        $out = [];
        foreach ($rows as $r) {
            $out[$r->concepto][] = $r->subconcepto;
        }
        return $out;
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
            'fecha'               => 'required|date',
            'ejecucion'           => 'required|string',
            'cuenta'              => 'required|string',
            'seccion'             => 'required|string',
            'concepto'            => 'required|string',
            'subconcepto'         => 'nullable|string',
            'detalle'             => 'required|string',
            'importe'             => 'required|numeric',
            'cuentaContrapartida' => 'nullable|string',
        ]);

        $operacion = ClasificadorOperacion::resolver(
            $validated['cuenta'],
            $validated['concepto'],
            $validated['seccion'],
            $validated['importe']
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

        // Contrapartida -- SOLO al dar de alta (esto nunca se llama en una edicion),
        // y solo si el usuario eligio explicitamente una cuenta del otro lado.
        if (!empty($validated['cuentaContrapartida'])) {
            $importeContra = -$validated['importe'];
            $seccionContra = $importeContra >= 0 ? '2 INGRESOS' : '3 EGRESOS';
            $operacionContra = ClasificadorOperacion::resolver(
                $validated['cuentaContrapartida'],
                $validated['concepto'],
                $seccionContra,
                $importeContra
            );

            DB::select('CALL SP_FF_MOVIMIENTO_INSERTAR(?,?,?,?,?,?,?,?,?,?,?)', [
                $validated['fecha'],
                $validated['cuentaContrapartida'],
                $validated['concepto'],
                $validated['subconcepto'] ?? null,
                $validated['detalle'],
                $importeContra,
                $validated['ejecucion'],
                $operacionContra,
                $seccionContra,
                'MANUAL',
                auth()->id(),
            ]);
        }

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

    public function actualizarEstadoMasivo(Request $request)
    {
        $request->validate([
            'ids'       => 'required|array|min:1',
            'ejecucion' => 'required|in:PRESUPUESTO,CUMPLIDO',
        ]);

        foreach ($request->input('ids') as $id) {
            DB::statement('CALL SP_FF_MOVIMIENTO_ACTUALIZAR_ESTADO(?,?)', [$id, $request->input('ejecucion')]);
        }

        return response()->json(['ok' => true, 'actualizados' => count($request->input('ids'))]);
    }

    public function duplicarMasivo(Request $request)
    {
        $request->validate(['ids' => 'required|array|min:1']);

        $duplicados = 0;
        foreach ($request->input('ids') as $id) {
            DB::select('CALL SP_FF_MOVIMIENTO_DUPLICAR(?,?)', [$id, auth()->id()]);
            $duplicados++;
        }

        return response()->json(['ok' => true, 'duplicados' => $duplicados]);
    }

    public function eliminarMasivo(Request $request)
    {
        $request->validate(['ids' => 'required|array|min:1']);

        foreach ($request->input('ids') as $id) {
            DB::statement('CALL SP_FF_MOVIMIENTO_ELIMINAR(?)', [$id]);
        }

        return response()->json(['ok' => true, 'eliminados' => count($request->input('ids'))]);
    }

    public function actualizarCuenta(Request $request, $id)
    {
        $request->validate(['cuenta' => 'required|string']);
        DB::statement('CALL SP_FF_MOVIMIENTO_ACTUALIZAR_CUENTA(?,?)', [$id, $request->input('cuenta')]);
        return response()->json(['ok' => true]);
    }

    public function actualizarConcepto(Request $request, $id)
    {
        $request->validate(['concepto' => 'required|string']);
        DB::statement('CALL SP_FF_MOVIMIENTO_ACTUALIZAR_CONCEPTO(?,?)', [$id, $request->input('concepto')]);
        return response()->json(['ok' => true]);
    }

    public function actualizarTexto(Request $request, $id)
    {
        $request->validate([
            'campo' => 'required|in:detalle,subconcepto,nro_comprobante',
            'valor' => 'nullable|string|max:255',
        ]);
        DB::statement('CALL SP_FF_MOVIMIENTO_ACTUALIZAR_TEXTO(?,?,?)', [
            $id,
            $request->input('campo'),
            $request->input('valor'),
        ]);
        return response()->json(['ok' => true]);
    }

    public function actualizarImporte(Request $request, $id)
    {
        $request->validate(['importe' => 'required|numeric']);
        DB::statement('CALL SP_FF_MOVIMIENTO_ACTUALIZAR_IMPORTE(?,?)', [$id, $request->input('importe')]);
        return response()->json(['ok' => true]);
    }

    public function actualizarFechaMasiva(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'fecha' => 'required|date',
        ]);

        foreach ($request->input('ids') as $id) {
            DB::statement('CALL SP_FF_MOVIMIENTO_ACTUALIZAR_FECHA(?,?)', [$id, $request->input('fecha')]);
        }

        return response()->json(['ok' => true, 'actualizados' => count($request->input('ids'))]);
    }

    public function actualizarOperacion(Request $request, $id)
    {
        $request->validate([
            'operacion' => 'required|in:INGRESOS,TRANSFERENCIAS,CHEQUES,EFECTIVO',
        ]);

        DB::select('CALL SP_FF_MOVIMIENTO_ACTUALIZAR_OPERACION(?, ?)', [
            $id,
            $request->input('operacion'),
        ]);

        return response()->json(['ok' => true]);
    }

    public function exportarSeleccionados(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            abort(400, 'No se seleccionaron movimientos.');
        }

        $movimientos = DB::table('ff_movimientos as m')
            ->join('ff_cuentas as c', 'c.id', '=', 'm.id_cuenta')
            ->join('ff_conceptos as k', 'k.id', '=', 'm.id_concepto')
            ->whereIn('m.id', $ids)
            ->whereNull('m.deleted_at')
            ->orderByDesc('m.fecha')
            ->select([
                'm.fecha',
                'm.nro_comprobante',
                'm.ejecucion',
                'm.operacion',
                'c.nombre as cuenta',
                'k.nombre as concepto',
                'm.subconcepto',
                'm.detalle',
                'm.importe',
            ])
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Movimientos');

        $encabezados = ['Fecha', 'Nro Comprobante', 'Estado', 'Operación', 'Cuenta', 'Concepto', 'Sub-concepto', 'Detalle', 'Importe'];
        $sheet->fromArray($encabezados, null, 'A1');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        $fila = 2;
        foreach ($movimientos as $m) {
            $sheet->setCellValue('A' . $fila, $m->fecha);
            $sheet->setCellValue('B' . $fila, $m->nro_comprobante);
            $sheet->setCellValue('C' . $fila, $m->ejecucion);
            $sheet->setCellValue('D' . $fila, $m->operacion);
            $sheet->setCellValue('E' . $fila, $m->cuenta);
            $sheet->setCellValue('F' . $fila, $m->concepto);
            $sheet->setCellValue('G' . $fila, $m->subconcepto);
            $sheet->setCellValue('H' . $fila, $m->detalle);
            $sheet->setCellValueExplicit('I' . $fila, $m->importe, DataType::TYPE_NUMERIC);
            $fila++;
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('I2:I' . ($fila - 1))->getNumberFormat()->setFormatCode('#,##0.00');

        $nombreArchivo = 'movimientos_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}

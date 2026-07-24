<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\ClasificadorOperacion;
use Carbon\Carbon;

class PresupuestarController extends Controller
{
    public function presupuestarView()
    {
        $conceptos = collect(DB::select('SELECT nombre FROM ff_conceptos WHERE activo=1 ORDER BY orden'))->pluck('nombre');

        return view('administracion.presupuestar.presupuestar', compact('conceptos'));
    }

    public function previewGeclisa(Request $request)
    {
        $request->validate(['contenido' => 'required|string']);
        $rows = $this->parseGeclisa($request->input('contenido'));

        return response()->json([
            'rows'    => $rows,
            'mensaje' => count($rows) > 0
                ? count($rows) . ' cobranzas detectadas.'
                : 'No se pudieron parsear filas.',
        ]);
    }

    public function confirmarGeclisa(Request $request)
    {
        $request->validate([
            'rows'            => 'required|array|min:1',
            'rows.*.fecha'    => 'required|date',
            'rows.*.detalle'  => 'required|string',
            'rows.*.importe'  => 'required|numeric',
        ]);

        $insertados = 0;
        foreach ($request->input('rows') as $r) {
            DB::select('CALL SP_FF_MOVIMIENTO_INSERTAR(?,?,?,?,?,?,?,?,?,?,?)', [
                $r['fecha'],
                'MACRO',
                'OBRAS SOCIALES',
                '2 OS-TRANSFERENCIAS',
                $r['detalle'],
                abs((float) $r['importe']),
                'PRESUPUESTO',
                'INGRESOS',
                '2 INGRESOS',
                'GECLISA',
                auth()->id(),
            ]);
            $insertados++;
        }

        return response()->json(['insertados' => $insertados]);
    }

    private function parseGeclisa(string $text): array
    {
        $lines = preg_split('/\r?\n/', trim($text));
        if (count($lines) < 2) return [];

        $header = array_map(fn($h) => mb_strtolower(trim($h)), explode("\t", $lines[0]));
        $ix = function (string $needle) use ($header): int {
            foreach ($header as $i => $h) {
                if (str_contains($h, $needle)) return $i;
            }
            return -1;
        };
        $iFecha = $ix('fecha');
        $iRazon = $ix('razonsocial');
        $iTotal = $ix('total');

        $rows = [];
        for ($i = 1; $i < count($lines); $i++) {
            $c = explode("\t", $lines[$i]);
            if (count($c) < 5) continue;

            $fechaRaw = trim($c[$iFecha] ?? '');
            $fechaRaw = preg_replace('/ .*/', '', $fechaRaw);
            $partes = explode('/', $fechaRaw);
            if (count($partes) !== 3) continue;

            $fecha = sprintf('%04d-%02d-%02d', $partes[2], $partes[1], $partes[0]);
            // CAMBIO GECLISA: la fecha de cobranza se desplaza +30 dias (vencimiento real de pago)
            $fecha = Carbon::parse($fecha)->addDays(30)->format('Y-m-d');

            $totalRaw = str_replace(['.', ','], ['', '.'], trim($c[$iTotal] ?? '0'));
            $total = (float) $totalRaw;
            if ($total == 0) continue;

            $rows[] = [
                'fecha'       => $fecha,
                'detalle'     => trim($c[$iRazon] ?? ''),
                'importe'     => abs($total),
            ];
        }
        return $rows;
    }

    public function previewFinnegans(Request $request)
    {
        $request->validate(['contenido' => 'required|string']);
        $rows = $this->parseFinnegans($request->input('contenido'));

        return response()->json([
            'rows'    => $rows,
            'mensaje' => count($rows) > 0
                ? count($rows) . ' pagos detectados.'
                : 'No se pudieron parsear filas.',
        ]);
    }

    public function confirmarFinnegans(Request $request)
    {
        $request->validate([
            'rows'            => 'required|array|min:1',
            'rows.*.fecha'    => 'required|date',
            'rows.*.detalle'  => 'required|string',
            'rows.*.concepto' => 'required|string',
            'rows.*.importe'  => 'required|numeric',
        ]);

        $insertados = 0;
        foreach ($request->input('rows') as $r) {
            DB::select('CALL SP_FF_MOVIMIENTO_INSERTAR(?,?,?,?,?,?,?,?,?,?,?)', [
                $r['fecha'],
                'MACRO',
                $r['concepto'],       // editable por fila en el preview
                'MEDICAMENTOS',       // fijo, igual que el artefacto original
                $r['detalle'],
                -abs((float) $r['importe']),
                'PRESUPUESTO',
                'TRANSFERENCIAS',
                '3 EGRESOS',
                'FINNEGANS',
                auth()->id(),
            ]);
            $insertados++;
        }

        return response()->json(['insertados' => $insertados]);
    }

    private function parseFinnegans(string $text): array
    {
        $lines = preg_split('/\r?\n/', trim($text));
        if (count($lines) < 2) return [];

        $header = array_map(fn($h) => mb_strtolower(trim($h)), explode("\t", $lines[0]));
        $ix = function (string $needle) use ($header): int {
            foreach ($header as $i => $h) {
                if (str_contains($h, $needle)) return $i;
            }
            return -1;
        };
        $iImp   = $ix('importe');
        $iOrg   = $ix('organizacion');
        $iFecha = $ix('fechavto');

        $rows = [];
        for ($i = 1; $i < count($lines); $i++) {
            $c = explode("\t", $lines[$i]);
            if (count($c) < 4) continue;

            $fechaRaw = trim($c[$iFecha] ?? '');
            $partes = explode('/', $fechaRaw);
            if (count($partes) !== 3) continue;
            $fecha = sprintf('%04d-%02d-%02d', $partes[2], $partes[1], $partes[0]);

            $impRaw = str_replace(['.', ','], ['', '.'], trim($c[$iImp] ?? '0'));
            $imp = (float) $impRaw;
            if ($imp == 0) continue;

            $rows[] = [
                'fecha'    => $fecha,
                'detalle'  => trim($c[$iOrg] ?? ''),
                'concepto' => 'FARMACIA', // default, editable en el preview antes de confirmar
                'importe'  => -abs($imp),
            ];
        }
        return $rows;
    }

    public function listarRecurrentes()
    {
        $recurrentes = DB::select('CALL SP_FF_RECURRENTE_LISTAR()');
        return response()->json($recurrentes);
    }

    public function guardarRecurrente(Request $request)
    {
        $validated = $request->validate([
            'banco'       => 'required|string',
            'concepto'    => 'required|string',
            'subconcepto' => 'nullable|string',
            'detalle'     => 'required|string',
            'importe'     => 'required|numeric',
            'seccion'     => 'required|in:2 INGRESOS,3 EGRESOS',
        ]);

        $resultado = DB::select('CALL SP_FF_RECURRENTE_INSERTAR(?,?,?,?,?,?)', [
            $validated['banco'],
            $validated['concepto'],
            $validated['subconcepto'] ?? null,
            $validated['detalle'],
            $validated['importe'],
            $validated['seccion'],
        ]);

        return response()->json(['id' => $resultado[0]->id]);
    }

    public function eliminarRecurrente($id)
    {
        DB::statement('CALL SP_FF_RECURRENTE_ELIMINAR(?)', [$id]);
        return response()->json(['ok' => true]);
    }

    public function aplicarRecurrentes(Request $request)
    {
        $request->validate(['mes' => 'required|date_format:Y-m']);
        $mes = $request->input('mes');

        $recurrentes = DB::select('CALL SP_FF_RECURRENTE_LISTAR()');

        $insertados = 0;
        foreach ($recurrentes as $r) {
            $operacion = ClasificadorOperacion::resolver(
                $r->banco,
                $r->concepto,
                $r->seccion,
                (float) $r->importe
            );

            DB::select('CALL SP_FF_MOVIMIENTO_INSERTAR(?,?,?,?,?,?,?,?,?,?,?)', [
                $mes . '-01',
                $r->banco,
                $r->concepto,
                $r->subconcepto,
                $r->detalle,
                $r->importe,
                'PRESUPUESTO',
                $operacion,
                $r->seccion,
                'RECURRENTE',
                auth()->id(),
            ]);
            $insertados++;
        }

        return response()->json(['insertados' => $insertados]);
    }
}

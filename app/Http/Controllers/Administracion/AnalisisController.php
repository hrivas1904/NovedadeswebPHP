<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDO;

class AnalisisController extends Controller
{
    public function homeAnalisisView()
    {
        return view('administracion.analisis.homeAnalisis');
    }

    public function flujoFondosView(Request $request)
    {

        $mes = $request->input('mes', now()->format('Y-m'));

        $pdo  = DB::connection()->getPdo();
        $stmt = $pdo->prepare('CALL SP_FF_ANALISIS_FLUJO_FONDOS(?)');
        $stmt->execute([$mes]);

        $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->nextRowset();
        $detalleRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $base = (float) $resumen['total_ingresos'];

        $detalle = collect($detalleRaw)->map(function ($fila) use ($base) {
            $fila['pct'] = $base > 0 ? (abs($fila['importe_neto']) / $base * 100) : 0;
            return $fila;
        })->groupBy('grupo_ff');

        $resumen['rdo_bruto_pct'] = $base > 0 ? ((float) $resumen['rdo_bruto_operativo'] / $base * 100) : 0;

        return view('administracion.analisis.flujoFondos', [
            'mes'      => $mes,
            'mesLabel' => mb_strtoupper(Carbon::parse($mes . '-01')->locale('es')->isoFormat('MMMM YYYY')),
            'resumen'  => $resumen,
            'detalle'  => $detalle,
        ]);
    }

    public function comparativaView()
    {
        $pdo  = DB::connection()->getPdo();
        $stmt = $pdo->prepare('CALL SP_FF_ANALISIS_COMPARATIVO()');
        $stmt->execute();

        $resumenRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->nextRowset();
        $detalleRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $periodos = collect($resumenRows)->pluck('periodo')->values();

        $periodoLabels = $periodos->map(function ($p) {
            return mb_strtoupper(Carbon::parse($p . '-01')->locale('es')->isoFormat('MMMM'));
        });

        $resumenPorPeriodo = collect($resumenRows)->keyBy('periodo');

        $detalle = collect($detalleRows)
            ->groupBy('grupo_ff')
            ->map(function ($filas) use ($periodos) {
                return $filas->groupBy('concepto')
                    ->map(function ($porConcepto) use ($periodos) {
                        $primero = $porConcepto->first();
                        $porPeriodo = $porConcepto->keyBy('periodo');
                        return [
                            'label'   => $primero['label'],
                            'orden'   => $primero['orden'],
                            'valores' => $periodos->map(
                                fn($p) => (float) ($porPeriodo->get($p)['importe_neto'] ?? 0)
                            )->values(),
                        ];
                    })
                    ->sortBy('orden')
                    ->values();
            });

        return view('administracion.analisis.comparativoSeis', [
            'periodos'      => $periodos,
            'periodoLabels' => $periodoLabels,
            'resumen'       => $periodos->map(fn($p) => $resumenPorPeriodo->get($p))->values(),
        ])->with('detalle', $detalle);
    }

    public function presupuestadoView(Request $request)
    {
        $mes = $request->input('mes', now()->format('Y-m'));

        $filas = DB::select('CALL SP_FF_ANALISIS_PRESUP_VS_EJECUTADO(?)', [$mes]);

        return view('administracion.analisis.presupejecutado', [
            'mes'   => $mes,
            'filas' => $filas,
        ]);
    }

    public function resumenAnualView(Request $request)
    {
        $anio = $request->input('anio', now()->format('Y'));

        $filas = collect(DB::select('CALL SP_FF_ANALISIS_RESUMEN_ANUAL(?)', [$anio]))
            ->map(function ($f) {
                $f->mes_nombre = ucfirst(
                    Carbon::create(null, (int) $f->mes_num, 1)->locale('es')->isoFormat('MMMM')
                );
                return $f;
            });

        $totales = [
            'presupuestado_ingresos' => $filas->sum('presupuestado_ingresos'),
            'presupuestado_egresos'  => $filas->sum('presupuestado_egresos'),
            'presupuestado_neto'     => $filas->sum('presupuestado_neto'),
            'ejecutado_ingresos'     => $filas->sum('ejecutado_ingresos'),
            'ejecutado_egresos'      => $filas->sum('ejecutado_egresos'),
            'ejecutado_neto'         => $filas->sum('ejecutado_neto'),
        ];

        $anios = collect(DB::select("SELECT DISTINCT LEFT(periodo,4) AS anio FROM ff_movimientos ORDER BY anio"))
            ->pluck('anio');
        if ($anios->isEmpty()) {
            $anios = collect([now()->format('Y')]);
        }

        return view('administracion.analisis.resumenAnual', [
            'anio'    => $anio,
            'anios'   => $anios,
            'meses'   => $filas,
            'totales' => $totales,
        ]);
    }
}

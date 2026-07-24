<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDO;

class DashboardController extends Controller
{
    public function homeView()
    {
        return view('administracion.posicion.dashboard');
    }

    public function operacionDiaView()
    {
        return view('administracion.posicion.operacionDia');
    }

    public function data(Request $request)
    {
        $fecha = $request->input('fecha', now()->format('Y-m-d'));

        $cuentas = collect(DB::select('CALL SP_FF_POSICION(?)', [$fecha]))
            ->map(function ($c) {
                $c->saldo = (float) $c->saldo; // <- cast explícito, antes eran strings de PDO
                return $c;
            });

        $porNombre = $cuentas->keyBy('nombre');

        $totalPesos = $cuentas->where('moneda', 'PESOS')->sum('saldo');
        $totalUsd   = $cuentas->where('moneda', 'USD')->sum('saldo');

        return response()->json([
            'fecha'       => $fecha,
            'fecha_label' => ucfirst(Carbon::parse($fecha)->locale('es')->isoFormat('dddd DD [de] MMMM [de] YYYY')),
            'cuentas'     => $porNombre,
            'total_pesos' => $totalPesos,
            'total_usd'   => $totalUsd,
        ]);
    }

    public function dataHoy(Request $request)
    {
        $hoy = now()->format('Y-m-d');
        $mes = $request->input('mes', now()->format('Y-m'));

        $pdo  = DB::connection()->getPdo();
        $stmt = $pdo->prepare('CALL SP_FF_HOY(?, ?)');
        $stmt->execute([$hoy, $mes]);

        $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->nextRowset();
        $detalle = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $agrupado = collect($detalle)->groupBy('tipo');

        return response()->json([
            'resumen' => [
                'ingresos_presupuestados' => (float) $resumen['ingresos_presupuestados'],
                'egresos_presupuestados'  => (float) $resumen['egresos_presupuestados'],
                'neto_presupuestado'      => (float) $resumen['neto_presupuestado'],
                'saldo_bancos'            => (float) $resumen['saldo_bancos'],
                'necesidad_rescate'       => (float) $resumen['necesidad_rescate'],
            ],
            'pres_ing' => $agrupado->get('PRES_ING', collect())->values(),
            'pres_egr' => $agrupado->get('PRES_EGR', collect())->values(),
            'exec_ing' => $agrupado->get('EXEC_ING', collect())->values(),
            'exec_egr' => $agrupado->get('EXEC_EGR', collect())->values(),
        ]);
    }
}

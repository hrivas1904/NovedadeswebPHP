<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Support\MotorClasificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDO;

class ConciliacionesController extends Controller
{
    public function conciliacionesHomeView()
    {
        return view('administracion.conciliaciones.conciliacionesHome', [
            'conceptos' => $this->obtenerConceptos(),
            'subconceptosPorConcepto' => $this->obtenerSubconceptosPorConcepto(),
        ]);
    }

    public function conciliacionesMacroView()
    {
        return view('administracion.conciliaciones.conciliacionesMacro', [
            'conceptos' => $this->obtenerConceptos(),
            'subconceptosPorConcepto' => $this->obtenerSubconceptosPorConcepto(),
        ]);
    }

    public function conciliacionesNacionView()
    {
        return view('administracion.conciliaciones.conciliacionesNacion', [
            'conceptos' => $this->obtenerConceptos(),
            'subconceptosPorConcepto' => $this->obtenerSubconceptosPorConcepto(),
        ]);
    }

    public function conciliacionesFrances986View()
    {
        return view('administracion.conciliaciones.conciliacionesFrances986', [
            'conceptos' => $this->obtenerConceptos(),
            'subconceptosPorConcepto' => $this->obtenerSubconceptosPorConcepto(),
        ]);
    }

    public function conciliacionesFrances1001View()
    {
        return view('administracion.conciliaciones.conciliacionesFrances1001', [
            'conceptos' => $this->obtenerConceptos(),
            'subconceptosPorConcepto' => $this->obtenerSubconceptosPorConcepto(),
        ]);
    }

    private function obtenerConceptos()
    {
        return collect(DB::select('SELECT nombre FROM ff_conceptos WHERE activo = 1 ORDER BY orden'))
            ->pluck('nombre');
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

    public function previewPagos(Request $request)
    {
        $request->validate(['contenido' => 'required|string']);
        $pagos = $this->parsePagosMacro($request->input('contenido'));

        if (!count($pagos)) {
            return response()->json(['resultados' => [], 'mensaje' => 'No se detectaron pagos.']);
        }

        $candidatos = collect(DB::select("
        SELECT m.id, m.detalle, m.importe, m.fecha, m.ejecucion, k.nombre AS concepto
        FROM ff_movimientos m
        JOIN ff_conceptos k ON k.id = m.id_concepto
        WHERE m.ejecucion IN ('PRESUPUESTO','PENDIENTE') AND m.deleted_at IS NULL
    "));

        $motor = new MotorClasificacion();

        $resultados = [];
        foreach ($pagos as $pago) {
            $matches = $candidatos->filter(function ($c) use ($pago) {
                $nombreOk = $this->matchNombrePago($pago['nombre'], $c->detalle ?? '');

                // Tolerancia de retencion: el pago real puede ser hasta un 10%
                // menor al presupuestado (empresas agentes de retencion). Se
                // acepta entre el 90% y el 100% del monto presupuestado.
                // (+$1 de margen extra solo para redondeos de centavos)
                $presupuestado = abs((float) $c->importe);
                $pagado = $pago['importe'];
                $importeOk = $presupuestado > 0
                    && $pagado <= ($presupuestado + 1)
                    && $pagado >= ($presupuestado * 0.90);

                return $nombreOk && $importeOk;
            })->values();

            $hayMatch = $matches->count() > 0;

            $clasif = $motor->clasificar($pago['nombre'], $pago['nombre'], fn() => $motor->mapConcepto($pago['nombre']));

            $resultados[] = [
                'pago'        => $pago,
                'matches'     => $matches,
                'confirmado'  => $hayMatch, // por defecto todo tildado (con match: aplica; sin match: se crea EJECUTADO)
                'concepto'    => $clasif['concepto'],
                'subconcepto' => $clasif['subconcepto'],
            ];
        }

        $nM = collect($resultados)->filter(fn($r) => count($r['matches']) > 0)->count();
        $mensaje = count($pagos) . ' pagos · ' . $nM . ' con match · ' . (count($pagos) - $nM) . ' sin match.';

        return response()->json(['resultados' => $resultados, 'mensaje' => $mensaje]);
    }

    public function confirmarPagos(Request $request)
    {
        $request->validate(['resultados' => 'required|array|min:1']);

        $actualizados = 0;
        $duplicados = 0;
        $insertados = 0;

        foreach ($request->input('resultados') as $r) {
            $hayMatch = !empty($r['matches']) && count($r['matches']) > 0;
            $incluir = !empty($r['confirmado']); // si esta tildada o no

            if (!$incluir) continue; // destildada = se omite, sea con match o sin match

            if ($hayMatch) {
                $idMatch = $r['matches'][0]['id'];

                // 1) El movimiento original pasa a CUMPLIDO
                DB::statement('CALL SP_FF_MOVIMIENTO_ACTUALIZAR_ESTADO(?,?)', [$idMatch, 'CUMPLIDO']);
                $actualizados++;

                // 2) Se duplica como EJECUTADO -- la transaccion real que ya paso
                // por el banco. Se relee de la base (no se confia en lo que
                // mando el navegador), por seguridad con datos financieros.
                $original = DB::selectOne("
                SELECT c.nombre AS banco, k.nombre AS concepto, m.subconcepto,
                       m.detalle, m.importe, m.seccion, m.operacion, m.fecha
                FROM ff_movimientos m
                JOIN ff_cuentas c ON c.id = m.id_cuenta
                JOIN ff_conceptos k ON k.id = m.id_concepto
                WHERE m.id = ? AND m.deleted_at IS NULL
            ", [$idMatch]);

                if ($original) {
                    DB::select('CALL SP_FF_MOVIMIENTO_INSERTAR(?,?,?,?,?,?,?,?,?,?,?)', [
                        $original->fecha,
                        $original->banco,
                        $original->concepto,
                        $original->subconcepto,
                        $original->detalle,
                        $original->importe,
                        'EJECUTADO',
                        $original->operacion,
                        $original->seccion,
                        'CONCILIACION_PAGOS',
                        auth()->id(),
                    ]);
                    $duplicados++;
                }

                continue;
            }

            // Sin match: SIEMPRE se crea como EJECUTADO (nunca CUMPLIDO -- no hay
            // forma manual de revertir eso). La aux despues busca el presupuesto
            // a mano si corresponde y lo marca CUMPLIDO ella misma.
            $importe = -abs((float) $r['pago']['importe']);
            $operacion = \App\Support\ClasificadorOperacion::resolver('MACRO', $r['concepto'], '3 EGRESOS', $importe);

            DB::select('CALL SP_FF_MOVIMIENTO_INSERTAR(?,?,?,?,?,?,?,?,?,?,?)', [
                $r['pago']['fechaPago'] ?: $r['pago']['fechaEmision'],
                'MACRO',
                $r['concepto'],
                $r['subconcepto'] ?? null,
                $r['pago']['nombre'],
                $importe,
                'EJECUTADO',
                $operacion,
                '3 EGRESOS',
                'CONCILIACION_PAGOS',
                auth()->id(),
            ]);
            $insertados++;
        }

        return response()->json([
            'ok' => true,
            'actualizados' => $actualizados,
            'duplicados' => $duplicados,
            'insertados' => $insertados,
        ]);
    }

    // Misma heuristica de matching por nombre que el artefacto original
    // (coincidencia exacta, substring en cualquier direccion, o primeros 5 caracteres iguales)
    private function matchNombrePago(string $p, string $d): bool
    {
        $p = mb_strtolower(trim($p));
        $d = mb_strtolower(trim($d));
        if ($p === '' || $d === '') return false;
        if ($p === $d) return true;
        if (mb_strlen($d) > 4 && str_contains($p, $d)) return true;
        if (mb_strlen($p) > 4 && str_contains($d, $p)) return true;
        if (mb_strlen($p) > 5 && mb_strlen($d) > 5 && mb_substr($p, 0, 5) === mb_substr($d, 0, 5)) return true;
        return false;
    }

    // Parser del listado de pagos efectivizados (mismo formato que parsePagosMacro original)
    private function parsePagosMacro(string $text): array
    {
        $lines = preg_split('/\r?\n/', trim($text));
        if (count($lines) < 2) return [];

        // Normaliza sacando espacios, "/", Y ACENTOS -- antes solo sacaba
        // espacios y "/", por eso "emisión" nunca matcheaba contra "emision".
        $normalizar = function (string $h): string {
            $h = mb_strtolower(preg_replace('/[\s\/]/', '', trim($h)));
            return str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $h);
        };

        $header = array_map($normalizar, explode("\t", $lines[0]));
        $ix = function (string $needle) use ($header): int {
            foreach ($header as $i => $h) {
                if (str_contains($h, $needle)) return $i;
            }
            return -1;
        };

        $iEstado   = $ix('estado');
        $iNombre   = $ix('nombre');
        $iDoc      = $ix('documento');
        $iFechaEm  = $ix('emision');

        // "Fecha de pago" necesita las DOS palabras juntas -- si buscara solo
        // "pago", agarraria "Orden de pago" o "Forma de pago" antes que esta.
        $iFechaPag = -1;
        foreach ($header as $i => $h) {
            if (str_contains($h, 'fecha') && str_contains($h, 'pago')) {
                $iFechaPag = $i;
                break;
            }
        }

        $iImporte  = $ix('importe');

        $pagos = [];
        for ($i = 1; $i < count($lines); $i++) {
            $c = explode("\t", $lines[$i]);
            if (count($c) < 4) continue;

            $nombre = trim($c[$iNombre] ?? '');
            $impRaw = preg_replace('/\$|\s/', '', trim($c[$iImporte] ?? ''));
            $importe = abs((float) str_replace(['.', ','], ['', '.'], $impRaw));
            if (!$nombre || $importe == 0) continue;

            $pagos[] = [
                'estado'       => trim($c[$iEstado] ?? ''),
                'nombre'       => $nombre,
                'documento'    => trim($c[$iDoc] ?? ''),
                'fechaEmision' => $this->parseFecha(trim($c[$iFechaEm] ?? '')),
                'fechaPago'    => $this->parseFecha(trim($c[$iFechaPag] ?? '')),
                'importe'      => $importe,
            ];
        }

        return $pagos;
    }

    // Reusa el mismo parser de fecha que ya tenes en ImportacionController --
    // si tu ConciliacionesController no tiene uno propio, copialo de ahi:
    private function parseFecha(string $raw): ?string
    {
        $s = preg_replace('/ .*/', '', trim($raw));
        $p = explode('/', $s);
        if (count($p) !== 3) return null;
        return sprintf('%04d-%02d-%02d', $p[2], $p[1], $p[0]);
    }
}

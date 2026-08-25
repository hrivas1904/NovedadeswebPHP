<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Support\MotorClasificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportacionController extends Controller
{
    public function importacionView()
    {
        $conceptos = collect(DB::select('SELECT nombre FROM ff_conceptos WHERE activo = 1 ORDER BY orden'))->pluck('nombre');
        $subconceptosPorConcepto = $this->obtenerSubconceptosPorConcepto();

        return view('administracion.importacion.importacion', compact('conceptos', 'subconceptosPorConcepto'));
    }

    public function importMovBancariosView()
    {
        return view('administracion.importacion.importacionBancos');
    }

    public function importMovCajaView()
    {
        return view('administracion.importacion.importacionCaja');
    }

    public function importTsvView()
    {
        return view('administracion.importacion.importacionTsv');
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

    // -------------------------------------------------------------
    // BANCOS - Pegado directo del homebanking
    // -------------------------------------------------------------
    public function previewBancos(Request $request)
    {
        $request->validate([
            'contenido' => 'nullable|string',
            'archivo'   => 'nullable|file|mimes:xlsx,xls',
            'banco'     => 'required|string',
            'formato'   => 'required|in:PEGADO,CONCILIACION',
        ]);

        $contenido = $request->hasFile('archivo')
            ? $this->excelToTsv($request->file('archivo'))
            : (string) $request->input('contenido', '');

        if (trim($contenido) === '') {
            return response()->json(['rows' => [], 'mensaje' => 'No se recibió contenido ni archivo.']);
        }

        $banco = $request->input('banco');
        $motor = new MotorClasificacion();

        if ($request->input('formato') === 'PEGADO') {
            $rows = match ($banco) {
                'MACRO' => $this->parseMacroWeb($contenido, $motor),
                'NACION' => $this->parseNacionWeb($contenido, $motor),
                'FRANCES (986)', 'FRANCES (1001)' => $this->parseFrancesWeb($contenido, $banco, $motor),
                default => [],
            };
        } else {
            $rows = $this->parseConciliacion($contenido, $banco, $motor);
        }

        return response()->json([
            'rows'    => $rows,
            'mensaje' => count($rows) > 0
                ? count($rows) . ' movimientos detectados.'
                : 'No se pudieron parsear filas (o el banco/formato elegido todavía no está conectado).',
        ]);
    }

    public function confirmarBancos(Request $request)
    {
        $request->validate([
            'rows'              => 'required|array|min:1',
            'rows.*.fecha'      => 'required|date',
            'rows.*.banco'      => 'required|string',
            'rows.*.concepto'   => 'required|string',
            'rows.*.detalle'    => 'required|string',
            'rows.*.importe'    => 'required|numeric',
            'rows.*.seccion'    => 'required|string',
            'rows.*.operacion'  => 'required|string',
            'rows.*.sugerido_concepto'    => 'nullable|string',
            'rows.*.sugerido_subconcepto' => 'nullable|string',
            'origenConciliacion' => 'nullable|boolean',
        ]);

        $origenConciliacion = (bool) $request->input('origenConciliacion', false);
        $origen = $origenConciliacion ? 'CONCILIACION_EXTRACTO' : 'IMPORTACION_BANCO';

        $insertados = 0;
        $chequesMatcheados = 0;

        foreach ($request->input('rows') as $r) {
            $matchCheque = $r['matchCheque'] ?? null;
            $confirmarMatch = !empty($r['confirmarMatchCheque']);

            // Match de cheque confirmado: el candidato pasa a CUMPLIDO, y se
            // genera el EJECUTADO con los datos REALES de esta fila del
            // extracto (no los del candidato -- mismo criterio que en Pagos
            // a Proveedores).
            if ($matchCheque && $confirmarMatch) {
                DB::statement('CALL SP_FF_MOVIMIENTO_ACTUALIZAR_ESTADO(?,?)', [$matchCheque['id'], 'CUMPLIDO']);
                $chequesMatcheados++;
            }

            $resultado = DB::select('CALL SP_FF_MOVIMIENTO_INSERTAR(?,?,?,?,?,?,?,?,?,?,?)', [
                $r['fecha'],
                $r['banco'],
                $r['concepto'],
                $r['subconcepto'] ?? null,
                $r['detalle'],
                $r['importe'],
                'EJECUTADO',
                $r['operacion'],
                $r['seccion'],
                $origen,
                auth()->id(),
            ]);
            $insertados++;

            if (!empty($resultado[0]->id) && !empty($r['nro_comprobante'])) {
                DB::statement('UPDATE ff_movimientos SET nro_comprobante = ? WHERE id = ?', [
                    $r['nro_comprobante'],
                    $resultado[0]->id,
                ]);
            }

            if ($origenConciliacion && !empty($resultado[0]->id)) {
                DB::statement('UPDATE ff_movimientos SET nuevo_en_conciliacion = 1 WHERE id = ?', [$resultado[0]->id]);
            }

            $conceptoFinal = $r['concepto'];
            $subFinal = $r['subconcepto'] ?? '';
            $sugConcepto = $r['sugerido_concepto'] ?? null;
            $sugSub = $r['sugerido_subconcepto'] ?? null;

            if ($sugConcepto !== null && ($conceptoFinal !== $sugConcepto || $subFinal !== $sugSub)) {
                MotorClasificacion::aprender($r['detalle'], $conceptoFinal, $subFinal);
            }
        }

        return response()->json(['insertados' => $insertados, 'chequesMatcheados' => $chequesMatcheados]);
    }
    // -------------------------------------------------------------
    // Parser: MACRO, pegado directo del homebanking
    // Columnas esperadas (tab-separadas): Fecha | Columna2 | CodOp | Descripcion | Importe
    // -------------------------------------------------------------

    private function parseMacroWeb(string $text, MotorClasificacion $motor): array
    {
        $lines = preg_split('/\r?\n/', trim($text));
        $rows = [];

        // Candidatos de cheque: PRESUPUESTO/PENDIENTE con operacion CHEQUES,
        // indexados por nro_comprobante (unico) para matcheo directo O(1).
        $candidatosCheque = collect(DB::select("
        SELECT id, nro_comprobante, detalle, importe, fecha
        FROM ff_movimientos
        WHERE operacion = 'CHEQUES' AND ejecucion IN ('PRESUPUESTO','PENDIENTE')
          AND nro_comprobante IS NOT NULL AND nro_comprobante <> ''
          AND deleted_at IS NULL
    "))->keyBy('nro_comprobante');

        foreach ($lines as $line) {
            $c = explode("\t", $line);
            if (count($c) < 5) continue;

            $fecha = $this->parseFecha(trim($c[0] ?? ''));
            if (!$fecha) continue;

            $columna2 = trim($c[1] ?? '');  // Nro de comprobante
            $desc = trim($c[3] ?? '');
            $importe = $this->parseImporteAR(trim($c[4] ?? ''));
            if ($importe == 0) continue;

            $descLower = mb_strtolower($desc);
            $col2Lower = mb_strtolower($columna2);
            $esQR = str_contains($descLower, 'qr') || str_contains($col2Lower, 'qr');
            $detalleAjustado = $desc . ($esQR && !str_contains($descLower, 'qr') ? ' QR' : '');

            $clasif = $motor->clasificar($detalleAjustado, $desc, fn() => $motor->mapConceptoMacro($desc, ''), $importe);
            $cat = $clasif['concepto'];
            $sub = $clasif['subconcepto'];

            if ($importe > 0 && $cat === 'GTOS, COMIS, IMP.') {
                $cat = 'OBRAS SOCIALES';
            }
            
            if ($cat === 'OBRAS SOCIALES' && !str_contains(mb_strtolower($desc), 'acreditacion cheque rem')) {
                $sub = '2 OS-TRANSFERENCIAS';
            }

            $seccion = $cat === 'TRANSF. ENTRE CUENTAS' ? '5 TRANSFERENCIAS'
                : ($cat === 'TARJETAS D/C' ? '4 TARJETAS D/C' : ($importe >= 0 ? '2 INGRESOS' : '3 EGRESOS'));
            $operacion = $motor->clasificarOperacionBancaria($desc, $columna2, $importe);

            if ($operacion !== 'CHEQUES') {
                if ($cat === 'TRANSF. ENTRE CUENTAS') {
                    $operacion = 'TRANSFERENCIAS';
                } elseif ($importe > 0) {
                    $operacion = 'INGRESOS';
                } elseif ($cat === 'GTOS, COMIS, IMP.') {
                    $operacion = 'TRANSFERENCIAS';
                }
            }

            // Match de cheque: si el numero de comprobante de esta linea
            // coincide con un cheque PRESUPUESTO/PENDIENTE existente.
            $matchCheque = null;
            if ($columna2 !== '' && $candidatosCheque->has($columna2)) {
                $cand = $candidatosCheque->get($columna2);
                $matchCheque = [
                    'id'      => $cand->id,
                    'detalle' => $cand->detalle,
                    'fecha'   => $cand->fecha,
                    'importe' => (float) $cand->importe,
                ];
            }

            $rows[] = [
                'fecha' => $fecha,
                'banco' => 'MACRO',
                'seccion' => $seccion,
                'concepto' => $cat,
                'subconcepto' => $sub,
                'detalle' => $detalleAjustado,
                'importe' => $importe,
                'operacion' => $operacion,
                'nro_comprobante' => $columna2 !== '' ? $columna2 : null,
                'sugerido_concepto' => $cat,
                'sugerido_subconcepto' => $sub,
                'matchCheque' => $matchCheque,
                'confirmarMatchCheque' => $matchCheque !== null, // auto-tildado si hay match
            ];

            if ($motor->esFCI($desc) && $cat === 'TRANSF. ENTRE CUENTAS') {
                $catContra = 'TRANSF. ENTRE CUENTAS';
                $subContra = $motor->resolverSubconcepto($desc, $catContra);
                $rows[] = [
                    'fecha' => $fecha,
                    'banco' => 'FONDO COMUN DE INVERSION',
                    'seccion' => (-$importe >= 0) ? '2 INGRESOS' : '3 EGRESOS',
                    'concepto' => $catContra,
                    'subconcepto' => $subContra,
                    'detalle' => $desc,
                    'importe' => -$importe,
                    'operacion' => 'TRANSFERENCIAS',
                    'sugerido_concepto' => $catContra,
                    'sugerido_subconcepto' => $subContra,
                    'matchCheque' => null,
                    'confirmarMatchCheque' => false,
                ];
            }
        }

        return $rows;
    }

    // -------------------------------------------------------------
    // Parser: NACION, pegado directo del homebanking
    // Columnas: Fecha(+Hora en linea separada a veces) | Descripcion | Credito | Debito
    // -------------------------------------------------------------
    private function parseNacionWeb(string $text, MotorClasificacion $motor): array
    {
        $raw = trim($text);
        // Si la fecha y la hora quedaron en lineas separadas al pegar, se unen en una sola
        $raw = preg_replace('/(\d{2}\/\d{2}\/\d{4})\n(\d{2}:\d{2}:\d{2})/', '$1 $2', $raw);
        $lines = preg_split('/\r?\n/', $raw);
        $rows = [];

        foreach ($lines as $line) {
            $c = explode("\t", $line);
            if (count($c) < 3) continue;

            $fechaRaw = preg_replace('/ \d{2}:\d{2}:\d{2}/', '', trim($c[0] ?? ''));
            $fecha = $this->parseFecha($fechaRaw);
            if (!$fecha) continue;

            $desc = trim($c[1] ?? '');
            if ($desc === '') continue;

            // Columnas del formato Pegado de NACION: Fecha | Descripcion | Debito | Credito
            // (antes estaba al reves -- confirmado por la aux de administracion)
            $debRaw = trim($c[2] ?? '');
            $credRaw = trim($c[3] ?? '');
            $importe = 0.0;
            if ($credRaw !== '' && $credRaw !== '-') $importe = abs($this->parseImporteAR($credRaw));
            if ($debRaw !== '' && $debRaw !== '-') $importe = -abs($this->parseImporteAR($debRaw));
            if ($importe == 0) continue;

            $clasif = $motor->clasificar($desc, $desc, fn() => $motor->mapConcepto($desc), $importe);
            $cat = $clasif['concepto'];
            $sub = $clasif['subconcepto'];

            $seccion = $cat === 'TRANSF. ENTRE CUENTAS' ? '5 TRANSFERENCIAS'
                : ($cat === 'TARJETAS D/C' ? '4 TARJETAS D/C' : ($importe >= 0 ? '2 INGRESOS' : '3 EGRESOS'));
            $operacion = $motor->clasificarOperacionBancaria($desc, '', $importe);

            if ($operacion !== 'CHEQUES' && in_array($cat, ['TRANSF. ENTRE CUENTAS', 'GTOS, COMIS, IMP.'])) {
                $operacion = 'TRANSFERENCIAS';
            }

            $rows[] = [
                'fecha' => $fecha,
                'banco' => 'NACION',
                'seccion' => $seccion,
                'concepto' => $cat,
                'subconcepto' => $sub,
                'detalle' => $desc,
                'importe' => $importe,
                'operacion' => $operacion,
                'sugerido_concepto' => $cat,
                'sugerido_subconcepto' => $sub,
            ];

            // Contrapartida automatica a FCI (misma logica que ya teniamos para
            // MACRO -- antes no estaba en Nacion, era el hueco real).
            if ($motor->esFCI($desc) && $cat === 'TRANSF. ENTRE CUENTAS') {
                $catContra = 'TRANSF. ENTRE CUENTAS';
                $subContra = $motor->resolverSubconcepto($desc, $catContra);
                $rows[] = [
                    'fecha' => $fecha,
                    'banco' => 'FONDO COMUN DE INVERSION',
                    'seccion' => (-$importe >= 0) ? '2 INGRESOS' : '3 EGRESOS',
                    'concepto' => $catContra,
                    'subconcepto' => $subContra,
                    'detalle' => $desc,
                    'importe' => -$importe,
                    'operacion' => 'TRANSFERENCIAS',
                    'sugerido_concepto' => $catContra,
                    'sugerido_subconcepto' => $subContra,
                ];
            }
        }

        return $rows;
    }

    // -------------------------------------------------------------
    // Parser: FRANCES (986/1001), pegado directo del homebanking
    // Columnas: Fecha | Descripcion | CodOp | Credito | Debito | Saldo
    // -------------------------------------------------------------
    private function parseFrancesWeb(string $text, string $banco, MotorClasificacion $motor): array
    {
        $lines = preg_split('/\r?\n/', trim($text));
        $rows = [];

        foreach ($lines as $line) {
            if (str_contains(mb_strtolower($line), 'no hay más')) continue;

            $c = explode("\t", $line);
            if (count($c) < 5) continue;

            $fechaRaw = str_replace('-', '/', trim($c[0] ?? ''));
            $fecha = $this->parseFecha($fechaRaw);
            if (!$fecha) continue;

            $desc = trim($c[1] ?? '');
            if ($desc === '') continue;

            $credRaw = trim($c[3] ?? '');
            $debRaw = trim($c[4] ?? '');
            $importe = 0.0;
            if ($credRaw !== '' && $credRaw !== '-') $importe = abs($this->parseImporteAR($credRaw));
            if ($debRaw !== '' && $debRaw !== '-') $importe = -abs($this->parseImporteAR($debRaw));
            if ($importe == 0) continue;

            $clasif = $motor->clasificar($desc, $desc, fn() => $motor->mapConcepto($desc), $importe);
            $cat = $clasif['concepto'];
            $sub = $clasif['subconcepto'];

            $seccion = $cat === 'TRANSF. ENTRE CUENTAS' ? '5 TRANSFERENCIAS'
                : ($cat === 'TARJETAS D/C' ? '4 TARJETAS D/C' : ($importe >= 0 ? '2 INGRESOS' : '3 EGRESOS'));
            $operacion = $motor->clasificarOperacionBancaria($desc, '', $importe);

            if ($operacion !== 'CHEQUES' && in_array($cat, ['TRANSF. ENTRE CUENTAS', 'GTOS, COMIS, IMP.'])) {
                $operacion = 'TRANSFERENCIAS';
            }

            $rows[] = [
                'fecha' => $fecha,
                'banco' => $banco,
                'seccion' => $seccion,
                'concepto' => $cat,
                'subconcepto' => $sub,
                'detalle' => $desc,
                'importe' => $importe,
                'operacion' => $operacion,
                'sugerido_concepto' => $cat,
                'sugerido_subconcepto' => $sub,
            ];
        }

        return $rows;
    }

    // -------------------------------------------------------------
    // Parser: CONCILIACION EXCEL (los 4 bancos)
    // MACRO usa columnas 0,1,2,3,5 (Fecha, Col2, Concepto, Detalle, Importe)
    // Los demas usan columnas 0,1,2,3,4 (Fecha, Col2, Concepto, Detalle, Importe)
    // -------------------------------------------------------------
    private function parseConciliacion(string $text, string $banco, MotorClasificacion $motor): array
    {
        $lines = preg_split('/\r?\n/', trim($text));
        $rows = [];

        foreach ($lines as $line) {
            $c = explode("\t", $line);
            if (count($c) < 4) continue;

            $fecha = $this->parseFecha(trim($c[0] ?? ''));
            if (!$fecha) continue;

            $concepto = trim($c[2] ?? '');
            $detalle = trim($c[3] ?? '');
            $impRaw = $banco === 'MACRO' ? trim($c[5] ?? '') : trim($c[4] ?? '');
            // Solo el formato Excel de MACRO trae columna de numero (Nro) en el indice 4.
            // Nacion/Frances (Excel) no tienen esta columna en su formato.
            $nroComprobante = $banco === 'MACRO' ? trim($c[4] ?? '') : '';

            $importe = $this->parseImporteAR($impRaw);
            if ($importe == 0) continue;

            $columna2 = trim($c[1] ?? '');
            $esQR = str_contains(mb_strtolower($columna2), 'qr');
            $detalleAjustado = $detalle . ($esQR && !str_contains(mb_strtolower($detalle), 'qr') ? ' QR' : '');

            // OJO: la regla de concepto se busca por la columna CONCEPTO, no por detalle
            // (a diferencia de los otros parsers, que usan un solo texto para todo)
            $override = $motor->aplicarOverrideFijo($concepto . ' ' . $detalle, $importe);

            if ($override) {
                $cat = $override['concepto'];
            } else {
                $cat = $motor->reglaConceptoPara($concepto)
                    ?? ($banco === 'MACRO' ? $motor->mapConceptoMacro($concepto, $detalle) : $motor->mapConcepto($concepto . ' ' . $detalle));
            }

            $seccion = $cat === 'TRANSF. ENTRE CUENTAS' ? '5 TRANSFERENCIAS'
                : ($cat === 'TARJETAS D/C' ? '4 TARJETAS D/C' : ($importe >= 0 ? '2 INGRESOS' : '3 EGRESOS'));

            if ($override) {
                $sub = $override['sub'];
            } else {
                $sub = $motor->reglaSubconceptoPara($detalleAjustado) ?: $motor->subconceptoFrecuentePara($cat);
            }
            if ($esQR) {
                $sub = $sub ? 'QR - ' . $sub : 'QR';
            }

            // clasificarOperacion original solo mira la columna CONCEPTO para "reintegro"
            $operacion = $motor->clasificarOperacionBancaria($concepto, $columna2, $importe);

            if ($operacion !== 'CHEQUES' && in_array($cat, ['TRANSF. ENTRE CUENTAS', 'GTOS, COMIS, IMP.'])) {
                $operacion = 'TRANSFERENCIAS';
            }

            $rows[] = [
                'fecha' => $fecha,
                'banco' => $banco,
                'seccion' => $seccion,
                'concepto' => $cat,
                'subconcepto' => $sub,
                'detalle' => $detalleAjustado,
                'importe' => $importe,
                'operacion' => $operacion,
                'nro_comprobante' => $nroComprobante !== '' ? $nroComprobante : null,
                'sugerido_concepto' => $cat,
                'sugerido_subconcepto' => $sub,
            ];

            // Contrapartida a FCI (igual que en pegado directo, solo aplica a MACRO)
            if ($banco === 'MACRO' && $motor->esFCI($concepto . ' ' . $detalle)) {
                $catContra = 'TRANSF. ENTRE CUENTAS';
                $subContra = $motor->resolverSubconcepto($detalleAjustado, $catContra);
                $rows[] = [
                    'fecha' => $fecha,
                    'banco' => 'FONDO COMUN DE INVERSION',
                    'seccion' => (-$importe >= 0) ? '2 INGRESOS' : '3 EGRESOS',
                    'concepto' => $catContra,
                    'subconcepto' => $subContra,
                    'detalle' => $detalle !== '' ? $detalle : $concepto,
                    'importe' => -$importe,
                    'operacion' => 'TRANSFERENCIAS',
                    'sugerido_concepto' => $catContra,
                    'sugerido_subconcepto' => $subContra,
                ];
            }
        }

        return $rows;
    }

    // -------------------------------------------------------------
    // CAJA
    // -------------------------------------------------------------
    public function previewCaja(Request $request)
    {
        $request->validate([
            'contenido' => 'nullable|string',
            'archivo'   => 'nullable|file|mimes:xlsx,xls',
        ]);

        $contenido = $request->hasFile('archivo')
            ? $this->excelToTsv($request->file('archivo'))
            : (string) $request->input('contenido', '');

        if (trim($contenido) === '') {
            return response()->json(['rows' => [], 'mensaje' => 'No se recibió contenido ni archivo.']);
        }

        $motor = new MotorClasificacion();
        $rows = $this->parseCaja($contenido, $motor);

        return response()->json([
            'rows'    => $rows,
            'mensaje' => count($rows) > 0
                ? count($rows) . ' movimientos de caja detectados.'
                : 'No se pudieron parsear filas.',
        ]);
    }

    public function confirmarCaja(Request $request)
    {
        $request->validate([
            'rows'             => 'required|array|min:1',
            'rows.*.fecha'     => 'required|date',
            'rows.*.concepto'  => 'required|string',
            'rows.*.detalle'   => 'required|string',
            'rows.*.importe'   => 'required|numeric',
            'rows.*.seccion'   => 'required|string',
        ]);

        $insertados = 0;
        foreach ($request->input('rows') as $r) {
            DB::select('CALL SP_FF_MOVIMIENTO_INSERTAR(?,?,?,?,?,?,?,?,?,?,?)', [
                $r['fecha'],
                'CAJA',
                $r['concepto'],
                $r['subconcepto'] ?? null,
                $r['detalle'],
                $r['importe'],
                'EJECUTADO',
                'EFECTIVO',
                $r['seccion'],
                'IMPORTACION_CAJA',
                auth()->id(),
            ]);
            $insertados++;

            $sugConcepto = $r['sugerido_concepto'] ?? null;
            $sugSub = $r['sugerido_subconcepto'] ?? null;
            $subFinal = $r['subconcepto'] ?? '';

            if ($sugConcepto !== null && ($r['concepto'] !== $sugConcepto || $subFinal !== $sugSub)) {
                MotorClasificacion::aprender($r['detalle'], $r['concepto'], $subFinal);
            }
        }

        return response()->json(['insertados' => $insertados]);
    }

    // -------------------------------------------------------------
    // Parser: CAJA
    // Columnas: Fecha | Comprobante | Caja | Tercero-Ing | Tercero-Egr | Moneda | Ingreso | Egreso | Saldo | Empresa
    // -------------------------------------------------------------
    private function parseCaja(string $text, MotorClasificacion $motor): array
    {
        $lines = preg_split('/\r?\n/', trim($text));
        $rows = [];

        foreach ($lines as $line) {
            $c = explode("\t", $line);
            if (count($c) < 8) continue;

            $fecha = $this->parseFecha(trim($c[0] ?? ''));
            if (!$fecha) continue;

            $comprobante = trim($c[1] ?? '');
            $terceroIng = trim($c[3] ?? '');
            $terceroEgr = trim($c[4] ?? '');

            $detalle = $terceroIng !== '' ? $terceroIng : ($terceroEgr !== '' ? $terceroEgr : $comprobante);
            $detalleCompleto = $detalle . ($terceroEgr !== '' && $terceroEgr !== $detalle ? ' - ' . $terceroEgr : '');

            $ingreso = $this->parseImporteAR(trim($c[6] ?? ''));
            $egreso = $this->parseImporteAR(trim($c[7] ?? ''));
            $importe = $ingreso > 0 ? $ingreso : ($egreso > 0 ? -$egreso : 0);
            if ($importe == 0) continue;

            // Caja NO usa la heuristica por palabras clave (mapConcepto esta pensada
            // para texto bancario) -- si no hay regla ni override, fallback neutro por signo.
            $override = $motor->aplicarOverrideFijo($detalleCompleto);

            if ($override) {
                $cat = $override['concepto'];
            } else {
                $cat = $motor->reglaConceptoPara($detalleCompleto)
                    ?? $motor->reglaConceptoPara($detalle)
                    ?? ($importe >= 0 ? 'OTROS INGRESOS' : 'GASTOS VARIOS');
            }

            $seccion = $cat === 'TRANSF. ENTRE CUENTAS' ? '5 TRANSFERENCIAS'
                : ($cat === 'TARJETAS D/C' ? '4 TARJETAS D/C' : ($importe >= 0 ? '2 INGRESOS' : '3 EGRESOS'));

            $sub = $override ? $override['sub'] : ($motor->reglaSubconceptoPara($detalleCompleto) ?: $motor->subconceptoFrecuentePara($cat));

            $rows[] = [
                'fecha' => $fecha,
                'banco' => 'CAJA',
                'seccion' => $seccion,
                'concepto' => $cat,
                'subconcepto' => $sub,
                'detalle' => $detalleCompleto,
                'importe' => $importe,
                'operacion' => 'EFECTIVO',
                'sugerido_concepto' => $cat,
                'sugerido_subconcepto' => $sub,
            ];
        }

        return $rows;
    }

    // -------------------------------------------------------------
    // TSV
    // -------------------------------------------------------------
    public function previewTsv(Request $request)
    {
        $request->validate([
            'contenido' => 'nullable|string',
            'archivo'   => 'nullable|file|mimes:xlsx,xls',
        ]);

        $contenido = $request->hasFile('archivo')
            ? $this->excelToTsv($request->file('archivo'))
            : (string) $request->input('contenido', '');

        if (trim($contenido) === '') {
            return response()->json(['rows' => [], 'mensaje' => 'No se recibió contenido ni archivo.']);
        }

        $rows = $this->parseTSV($contenido);
        $validas = collect($rows)->where('valido', true)->count();
        $invalidas = count($rows) - $validas;

        $mensaje = count($rows) === 0
            ? 'No se pudieron parsear filas (revisá que la primera línea tenga los headers).'
            : $validas . ' movimientos listos para importar' . ($invalidas > 0 ? ', ' . $invalidas . ' con errores (marcados abajo).' : '.');

        return response()->json(['rows' => $rows, 'mensaje' => $mensaje]);
    }

    public function confirmarTsv(Request $request)
    {
        $request->validate(['rows' => 'required|array|min:1']);

        $cuentasValidas = collect(DB::select('SELECT nombre FROM ff_cuentas WHERE activo=1'))->pluck('nombre')->toArray();
        $conceptosValidos = collect(DB::select('SELECT nombre FROM ff_conceptos WHERE activo=1'))->pluck('nombre')->toArray();
        $operacionesValidas = ['INGRESOS', 'TRANSFERENCIAS', 'CHEQUES', 'EFECTIVO'];
        $seccionesValidas = ['2 INGRESOS', '3 EGRESOS', '4 TARJETAS D/C', '5 TRANSFERENCIAS', '6 SEÑAS'];
        $ejecucionesValidas = ['EJECUTADO', 'PRESUPUESTO', 'PENDIENTE', 'CUMPLIDO'];

        $insertados = 0;
        $omitidos = 0;

        // Se revalida server-side (no se confia en el 'valido' que mando el cliente):
        // son movimientos financieros, no vale la pena arriesgarse a un dato corrupto.
        foreach ($request->input('rows') as $r) {
            $valido = !empty($r['fecha']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $r['fecha'])
                && in_array($r['banco'] ?? '', $cuentasValidas)
                && in_array($r['concepto'] ?? '', $conceptosValidos)
                && in_array($r['operacion'] ?? '', $operacionesValidas)
                && in_array($r['seccion'] ?? '', $seccionesValidas)
                && in_array($r['ejecucion'] ?? '', $ejecucionesValidas)
                && (float) ($r['importe'] ?? 0) != 0;

            if (!$valido) {
                $omitidos++;
                continue;
            }

            DB::select('CALL SP_FF_MOVIMIENTO_INSERTAR(?,?,?,?,?,?,?,?,?,?,?)', [
                $r['fecha'],
                $r['banco'],
                $r['concepto'],
                $r['subconcepto'] ?? null,
                $r['detalle'] ?? '',
                $r['importe'],
                $r['ejecucion'],
                $r['operacion'],
                $r['seccion'],
                'IMPORTACION_TSV',
                auth()->id(),
            ]);
            $insertados++;
        }

        return response()->json(['insertados' => $insertados, 'omitidos' => $omitidos]);
    }

    // -------------------------------------------------------------
    // Parser: TSV generico. NO pasa por el motor de clasificacion --
    // el archivo ya trae operacion/ejecucion/banco/seccion/concepto resueltos.
    // -------------------------------------------------------------
    private function parseTSV(string $text): array
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

        $iDate = $ix('fecha');
        $iOp = $ix('operaci');
        $iEjec = $ix('ejecuci');
        $iBanco = $ix('banco');
        $iIE = $ix('i/e');
        $iConc = $ix('concepto');
        $iSub = $ix('sub-concepto');
        $iDet = $ix('detalle');
        $iImp = $ix('importe');

        $cuentasValidas = collect(DB::select('SELECT nombre FROM ff_cuentas WHERE activo=1'))->pluck('nombre')->toArray();
        $conceptosValidos = collect(DB::select('SELECT nombre FROM ff_conceptos WHERE activo=1'))->pluck('nombre')->toArray();
        $operacionesValidas = ['INGRESOS', 'TRANSFERENCIAS', 'CHEQUES', 'EFECTIVO'];
        $seccionesValidas = ['2 INGRESOS', '3 EGRESOS', '4 TARJETAS D/C', '5 TRANSFERENCIAS', '6 SEÑAS'];

        $rows = [];
        for ($i = 1; $i < count($lines); $i++) {
            $c = explode("\t", $lines[$i]);
            if (count($c) < 5) continue;

            $rawImp = preg_replace('/\x{00a0}|\s/u', '', trim($c[$iImp] ?? ''));
            if ($rawImp === '') continue;
            $neg = (bool) preg_match('/^-|\$-|-\$/', $rawImp);
            $cl = str_replace(['$', '-'], '', $rawImp);
            $num = str_contains($cl, ',') ? (float) str_replace(['.', ','], ['', '.'], $cl) : (float) $cl;
            $importe = $neg ? -abs($num) : abs($num);

            $fechaRaw = trim($c[$iDate] ?? '');
            $fecha = preg_replace_callback('/(\d+)\/(\d+)\/(\d+)/', function ($m) {
                return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
            }, $fechaRaw);

            $ejec = mb_strtoupper(trim($c[$iEjec] ?? ''));
            if ($ejec === 'PRESUPUESTO/EJECUTADO') $ejec = 'CUMPLIDO';

            $banco = mb_strtoupper(trim($c[$iBanco] ?? ''));
            $operacion = mb_strtoupper(trim($c[$iOp] ?? ''));
            $seccion = trim($c[$iIE] ?? '');
            $concepto = trim($c[$iConc] ?? '');
            $subconcepto = trim($c[$iSub] ?? '');
            $detalle = trim($c[$iDet] ?? '');

            $errores = [];
            if (!in_array($banco, $cuentasValidas)) $errores[] = "Cuenta \"$banco\" no existe";
            if (!in_array($concepto, $conceptosValidos)) $errores[] = "Concepto \"$concepto\" no existe";
            if (!in_array($operacion, $operacionesValidas)) $errores[] = "Operación \"$operacion\" inválida";
            if (!in_array($seccion, $seccionesValidas)) $errores[] = "Sección \"$seccion\" inválida";
            if (!$fecha || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) $errores[] = 'Fecha inválida';
            if ($importe == 0) $errores[] = 'Importe en 0';

            $rows[] = [
                'fecha' => $fecha,
                'operacion' => $operacion,
                'ejecucion' => $ejec,
                'banco' => $banco,
                'seccion' => $seccion,
                'concepto' => $concepto,
                'subconcepto' => $subconcepto,
                'detalle' => $detalle,
                'importe' => $importe,
                'valido' => empty($errores),
                'errores' => $errores,
            ];
        }

        return $rows;
    }

    /**
     * Convierte un archivo Excel (.xlsx/.xls) en texto separado por tabs,
     * exactamente como si el contenido se hubiera pegado del homebanking.
     * Reutiliza el valor FORMATEADO de cada celda (no el crudo/serial), para
     * que fechas y numeros salgan con el mismo formato visual que en Excel.
     */
    private function excelToTsv($archivo): string
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $lineas = [];

        foreach ($sheet->getRowIterator() as $row) {
            $celdas = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                $celdas[] = trim((string) $cell->getFormattedValue());
            }

            $lineas[] = implode("\t", $celdas);
        }

        return implode("\n", $lineas);
    }

    private function parseFecha(string $raw): ?string
    {
        $s = preg_replace('/ .*/', '', trim($raw));
        $p = explode('/', $s);
        if (count($p) !== 3) return null;
        return sprintf('%04d-%02d-%02d', $p[2], $p[1], $p[0]);
    }

    private function parseImporteAR(string $raw): float
    {
        $s = str_replace(['$', ' '], '', trim($raw));
        if ($s === '') return 0.0;
        $neg = str_starts_with($s, '-');
        $s = str_replace('-', '', $s);
        $s = str_replace(['.', ','], ['', '.'], $s);
        $v = (float) $s;
        return $neg ? -$v : $v;
    }
}

<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Log;

class CopagosController extends Controller
{
    private array $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    // ---------- vista ----------

    public function vistaCopagos()
    {
        return view('administracion.cobranzas.copagosIps');
    }

    // ---------- liquidación IPS ----------

    public function cargarLiquidacion(Request $request)
    {
        $request->validate(['archivo' => 'required|file|mimes:xlsx,xls']);

        $ruta = $request->file('archivo')->getRealPath();

        try {
            $reader = IOFactory::createReaderForFile($ruta);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($ruta);
        } catch (\Throwable $e) {
            Log::error('Error leyendo archivo de liquidación: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo leer el archivo. Verificá que sea un Excel válido.'], 422);
        }

        $filas = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetName = $sheet->getTitle();
            $grid = $this->hojaAGrid($sheet);

            $headerRow = null;
            foreach (array_slice($grid, 0, 10, true) as $r => $row) {
                if ($this->normalizarNombre($row[0] ?? '') === 'FIN') {
                    $headerRow = $r;
                    break;
                }
            }
            if ($headerRow === null) continue;

            $periodoRaw = null;
            foreach (array_slice($grid, 0, 6, true) as $r => $row) {
                if ($this->normalizarNombre($row[0] ?? '') === 'PERIODO') $periodoRaw = $row[1] ?? null;
            }
            $periodoLabel = $this->formatearPeriodo($periodoRaw, $sheetName);

            foreach ($grid as $r => $row) {
                if ($r <= $headerRow) continue;
                $fin = $row[0] ?? null;
                $nombre = $row[1] ?? null;
                $practicas = $row[2] ?? null;
                $internac = $row[3] ?? null;
                $total = $row[4] ?? null;

                if ($fin === null && $nombre === null && $total === null) break;
                if ($this->normalizarNombre($fin) === 'TOTAL') continue;
                if (!$nombre) continue;

                $filas[] = [
                    'fin' => (string) $fin,
                    'nombre' => (string) $nombre,
                    'nombre_norm' => $this->normalizarNombre($nombre),
                    'periodo' => $periodoLabel,
                    'hoja_origen' => $sheetName,
                    'practicas' => $this->parseNumeroArg($practicas),
                    'internacion' => $this->parseNumeroArg($internac),
                    'total' => $this->parseNumeroArg($total),
                ];
            }
        }

        if (!$filas) {
            return response()->json(['message' => "No se encontraron filas con columna 'FIN' en el archivo."], 422);
        }

        DB::statement('CALL SP_COPAGOS_LIQUIDACION_CARGAR(?, ?, ?)', [
            json_encode($filas, JSON_UNESCAPED_UNICODE),
            $request->file('archivo')->getClientOriginalName(),
            auth()->id(),
        ]);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return response()->json(['message' => count($filas) . ' filas cargadas.', 'filas' => count($filas)]);
    }

    public function listarLiquidacion()
    {
        return response()->json(['data' => DB::select('CALL SP_COPAGOS_LIQUIDACION_LISTAR()')]);
    }

    // ---------- caja ----------

    public function cargarCaja(Request $request)
    {
        set_time_limit(300);

        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls'
        ]);

        $archivo = $request->file('archivo');
        $ruta = $archivo->getRealPath();

        try {
            $reader = IOFactory::createReaderForFile($ruta);
            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($ruta);
            $sheet = $spreadsheet->getSheet(0);
        } catch (\Throwable $e) {
            Log::error('Error leyendo archivo de caja: ' . $e->getMessage());

            return response()->json([
                'message' => 'No se pudo leer el archivo.'
            ], 422);
        }

        /*
     * LEER ENCABEZADOS
     */
        $headers = [];

        foreach ($sheet->getRowIterator(1, 1) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $indice = \PhpOffice\PhpSpreadsheet\Cell\Coordinate
                    ::columnIndexFromString($cell->getColumn()) - 1;

                $headers[$indice] = $this->normalizarNombre(
                    $cell->getValue() ?? ''
                );
            }
        }

        $requeridos = [
            'me_fecha',
            'me_ape',
            'imp',
            'conccaja_nombre',
            'mve_anulado',
            'osplan'
        ];

        $idx = [];

        foreach ($requeridos as $campo) {

            $pos = array_search(
                $this->normalizarNombre($campo),
                $headers,
                true
            );

            if ($pos === false) {
                return response()->json([
                    'message' => "Falta la columna {$campo}."
                ], 422);
            }

            /*
         * PhpSpreadsheet trabaja desde columna 1.
         */
            $idx[$campo] = $pos + 1;
        }

        /*
     * RECORRER DIRECTAMENTE EL EXCEL
     */
        $filas = [];

        $ultimaFila = $sheet->getHighestDataRow();

        for ($r = 2; $r <= $ultimaFila; $r++) {

            $conc = $sheet
                ->getCell([$idx['conccaja_nombre'], $r])
                ->getValue();

            if (
                $this->normalizarNombre($conc)
                !== 'FACTURACION COPAGO (EXENTO)'
            ) {
                continue;
            }

            $anulado = $sheet
                ->getCell([$idx['mve_anulado'], $r])
                ->getValue();

            if (!empty($anulado)) {
                continue;
            }

            $nombre = $sheet
                ->getCell([$idx['me_ape'], $r])
                ->getValue();

            if (!$nombre) {
                continue;
            }

            $celdaFecha = $sheet->getCell([
                $idx['me_fecha'],
                $r
            ]);

            $fecha = $celdaFecha->getValue();

            if (
                $fecha !== null &&
                Date::isDateTime($celdaFecha)
            ) {
                $fecha = Date::excelToDateTimeObject($fecha);
            }

            $importe = $sheet
                ->getCell([$idx['imp'], $r])
                ->getValue();

            $osplan = $sheet
                ->getCell([$idx['osplan'], $r])
                ->getValue();

            $filas[] = [
                'fecha' => $this->parsearFecha($fecha),
                'nombre' => (string) $nombre,
                'nombre_norm' => $this->normalizarNombre($nombre),

                // IMPORTANTE: importe, NO imp
                'importe' => $this->parseNumeroArg($importe),

                'osplan' => $osplan,
            ];
        }

        if (!$filas) {
            return response()->json([
                'message' =>
                'No se encontraron pagos de "Facturación Copago (exento)".'
            ], 422);
        }

        DB::statement(
            'CALL SP_COPAGOS_CAJA_CARGAR(?, ?, ?)',
            [
                json_encode(
                    $filas,
                    JSON_UNESCAPED_UNICODE
                ),
                $archivo->getClientOriginalName(),
                auth()->id(),
            ]
        );

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return response()->json([
            'message' => count($filas) . ' pagos cargados.',
            'filas' => count($filas)
        ]);
    }

    public function listarCaja()
    {
        return response()->json(['data' => DB::select('CALL SP_COPAGOS_CAJA_LISTAR()')]);
    }

    // ---------- cruce ----------

    public function obtenerCruce()
    {
        return response()->json([
            'data' => $this->construirCruce()
        ]);
    }

    public function construirCruce(): array
    {
        $liqRows = DB::select('CALL SP_COPAGOS_LIQUIDACION_LISTAR()');
        $cajaRows = DB::select('CALL SP_COPAGOS_CAJA_LISTAR()');
        $notas = collect(DB::select('CALL SP_COPAGOS_NOTAS_LISTAR()'))->keyBy('nombre_norm');

        $cajaConPalabras = array_map(
            fn($c) => ['row' => $c, 'words' => array_flip(explode(' ', $c->nombre_norm))],
            $cajaRows
        );

        $porNombre = [];
        foreach ($liqRows as $r) $porNombre[$r->nombre_norm][] = $r;

        $telefonos = $this->buscarTelefonos(array_keys($porNombre));

        $pacientes = [];
        foreach ($porNombre as $nombreNorm => $filas) {
            $liqWords = array_flip(explode(' ', $nombreNorm));
            $totalLiq = array_sum(array_map(fn($f) => (float) $f->total, $filas));

            $matched = array_values(array_filter($cajaConPalabras, function ($c) use ($liqWords) {
                foreach ($liqWords as $w => $_) if (!isset($c['words'][$w])) return false;
                return true;
            }));

            $totalCobrado = array_sum(array_map(fn($m) => (float) $m['row']->importe, $matched));
            $diferencia = round($totalLiq - $totalCobrado, 2);
            $nota = $notas->get($nombreNorm);

            $pacientes[] = [
                'nombreNorm' => $nombreNorm,
                'nombreDisplay' => $filas[0]->nombre,
                'telefono' => $telefonos[$nombreNorm] ?? null,
                'fins' => array_map(fn($f) => $f->fin, $filas),
                'periodos' => array_values(array_unique(array_map(fn($f) => $f->periodo, $filas))),
                'totalLiquidado' => round($totalLiq, 2),
                'totalCobrado' => round($totalCobrado, 2),
                'diferencia' => $diferencia,
                'estado' => $diferencia <= 1000 ? 'COBRADO' : ($totalCobrado == 0.0 ? 'NO COBRADO' : 'COBRO PARCIAL'),
                'nota' => $nota->nota ?? null,
                'resuelto' => (bool) ($nota->resuelto ?? false),
                'pagos' => array_map(fn($m) => [
                    'fecha' => $m['row']->fecha,
                    'nombre' => $m['row']->nombre,
                    'importe' => (float) $m['row']->importe,
                ], $matched),
            ];
        }

        usort(
            $pacientes,
            fn($a, $b) => $b['diferencia'] <=> $a['diferencia']
        );

        return $pacientes;
    }

    public function guardarSnapshot(Request $request)
    {
        $data = $request->validate([
            'mes' => 'required|date_format:Y-m',
        ]);

        $pacientes = $this->construirCruce();

        $totalLiquidado = array_sum(
            array_column($pacientes, 'totalLiquidado')
        );

        $totalCobrado = array_sum(
            array_column($pacientes, 'totalCobrado')
        );

        $diferencia = $totalLiquidado - $totalCobrado;

        $pendientes = count(
            array_filter(
                $pacientes,
                fn($p) =>
                $p['estado'] !== 'COBRADO'
                    && !$p['resuelto']
            )
        );

        DB::statement(
            'CALL SP_COPAGOS_SNAPSHOT_GUARDAR(?, ?, ?, ?, ?, ?)',
            [
                $data['mes'],
                round($totalLiquidado, 2),
                round($totalCobrado, 2),
                round($diferencia, 2),
                $pendientes,
                auth()->id(),
            ]
        );

        return response()->json([
            'message' => 'Snapshot mensual guardado correctamente.'
        ]);
    }

    public function guardarNota(Request $request)
    {
        $data = $request->validate([
            'nombre_norm' => 'required|string|max:150',
            'nota' => 'nullable|string|max:500',
        ]);

        DB::statement('CALL SP_COPAGOS_NOTA_GUARDAR(?, ?, ?)', [
            $data['nombre_norm'],
            $data['nota'] ?? null,
            auth()->id(),
        ]);

        return response()->json(['message' => 'Nota guardada.']);
    }

    public function marcarResuelto(Request $request)
    {
        $data = $request->validate([
            'nombre_norm' => 'required|string|max:150',
            'resuelto' => 'required|boolean',
        ]);

        DB::statement('CALL SP_COPAGOS_RESUELTO_MARCAR(?, ?, ?)', [
            $data['nombre_norm'],
            $data['resuelto'] ? 1 : 0,
            auth()->id(),
        ]);

        return response()->json(['message' => 'Estado actualizado.']);
    }

    // ---------- pacientes ----------

    public function cargarPacientes(Request $request)
    {
        $request->validate(['archivo' => 'required|file|mimes:xlsx,xls']);
        $file = $request->file('archivo');

        $resultado = $this->parsearPacientesExcel($file->getRealPath());
        if (isset($resultado['error'])) {
            return response()->json(['message' => $resultado['error']], 422);
        }

        return $this->guardarPacientes($resultado, $file->getClientOriginalName());
    }

    public function cargarPacientesPdf(Request $request)
    {
        $data = $request->validate([
            'nombre_archivo' => 'required|string|max:255',
            'filas' => 'required|array|min:1',
            'filas.*.nombre' => 'required|string|max:150',
            'filas.*.telefono' => 'required|string|max:30',
            'filas.*.dni' => 'nullable|string|max:20',
        ]);

        $filas = array_map(fn($f) => [
            'dni' => $f['dni'] ? preg_replace('/\D/', '', $f['dni']) : null,
            'nombre' => $f['nombre'],
            'nombre_norm' => $this->normalizarNombre($f['nombre']),
            'telefono' => trim($f['telefono']),
        ], $data['filas']);

        return $this->guardarPacientes($filas, $data['nombre_archivo']);
    }

    private function guardarPacientes(array $filas, string $nombreArchivo)
    {
        $total = 0;
        foreach (array_chunk($filas, 2000) as $lote) {
            DB::statement('CALL SP_COPAGOS_PACIENTES_CARGAR_LOTE(?)', [json_encode($lote, JSON_UNESCAPED_UNICODE)]);
            $total += count($lote);
        }

        DB::statement('CALL SP_COPAGOS_CARGA_REGISTRAR(?, ?, ?, ?)', [
            'pacientes',
            $nombreArchivo,
            $total,
            auth()->id(),
        ]);

        return response()->json([
            'message' => $total . ' contactos procesados (nuevos + actualizados).',
            'filas' => $total,
        ]);
    }

    private function parsearPacientesExcel(string $ruta): array
    {
        $reader = IOFactory::createReaderForFile($ruta);
        $reader->setReadDataOnly(true);
        $grid = $this->hojaAGrid($reader->load($ruta)->getSheet(0));
        if (!$grid) return ['error' => 'El archivo está vacío.'];

        $normHeader = function ($h) {
            $h = mb_strtolower(trim((string) ($h ?? '')), 'UTF-8');
            $h = preg_replace('/\p{Mn}/u', '', \Normalizer::normalize($h, \Normalizer::FORM_D));
            return trim(preg_replace('/\s+/', ' ', str_replace('.', '', $h)));
        };
        $esTelefono = fn($h) => (bool) preg_match('/^(cel|celular|tel|telefono|contacto)$/', $h);
        $esNombre = fn($h) => (bool) preg_match('/nombre|apellido|paciente/', $h);

        $headerRowIdx = null;
        $headers = [];
        $normed = [];
        foreach (array_slice($grid, 0, 8, true) as $r => $row) {
            $h = array_map($normHeader, $row);
            if (array_filter($h, $esTelefono) && array_filter($h, $esNombre)) {
                $headerRowIdx = $r;
                $headers = $row;
                $normed = $h;
                break;
            }
        }
        if ($headerRowIdx === null) {
            return ['error' => 'No pude identificar la fila de encabezados (busqué columna de teléfono/celular y de nombre en las primeras 8 filas).'];
        }

        $telIdx = null;
        foreach ($normed as $i => $h) if ($esTelefono($h)) {
            $telIdx = $i;
            break;
        }
        $dniIdx = null;
        foreach ($normed as $i => $h) if (preg_match('/dni|documento/', $h)) {
            $dniIdx = $i;
            break;
        }
        $fullNameIdx = null;
        foreach (['nombre y apellido', 'apellido y nombre', 'paciente', 'nombre completo'] as $cand) {
            $pos = array_search($cand, $normed, true);
            if ($pos !== false) {
                $fullNameIdx = $pos;
                break;
            }
        }
        $apellidoIdx = array_search('apellido', $normed, true);
        $nombreIdx = array_search('nombre', $normed, true);

        if ($telIdx === null) {
            return ['error' => 'No encontré columna de teléfono/celular. Encabezados: ' . implode(', ', array_map('strval', $headers))];
        }
        if ($fullNameIdx === null && !($apellidoIdx !== false && $nombreIdx !== false)) {
            return ['error' => 'No encontré columna de nombre reconocible. Encabezados: ' . implode(', ', array_map('strval', $headers))];
        }

        $filas = [];
        foreach ($grid as $r => $row) {
            if ($r <= $headerRowIdx) continue;
            $fullName = $fullNameIdx !== null ? ($row[$fullNameIdx] ?? null)
                : trim(($row[$apellidoIdx] ?? '') . ' ' . ($row[$nombreIdx] ?? ''));
            if (!$fullName) continue;
            $telefono = $row[$telIdx] ?? null;
            if (!$telefono) continue;

            $dni = $dniIdx !== null ? preg_replace('/\D/', '', (string) ($row[$dniIdx] ?? '')) : null;
            if ($dni === '') $dni = null;

            $filas[] = [
                'dni' => $dni,
                'nombre' => (string) $fullName,
                'nombre_norm' => $this->normalizarNombre($fullName),
                'telefono' => trim((string) $telefono),
            ];
        }
        return $filas;
    }

    private function buscarTelefonos(array $nombresNorm): array
    {
        if (!$nombresNorm) return [];

        $placeholders = implode(',', array_fill(0, count($nombresNorm), '?'));
        $exactos = collect(DB::select(
            "SELECT nombre_norm, telefono FROM copagos_pacientes_contacto WHERE nombre_norm IN ($placeholders)",
            $nombresNorm
        ))->keyBy('nombre_norm');

        $telefonos = [];
        $sinMatch = [];
        foreach ($nombresNorm as $n) {
            if ($exactos->has($n)) $telefonos[$n] = $exactos[$n]->telefono;
            else $sinMatch[] = $n;
        }

        foreach ($sinMatch as $nombreNorm) {
            $candidatos = DB::select(
                'SELECT nombre_norm, telefono FROM copagos_pacientes_contacto WHERE nombre_norm LIKE ? LIMIT 200',
                [strtok($nombreNorm, ' ') . '%']
            );
            $liqWords = array_flip(explode(' ', $nombreNorm));
            foreach ($candidatos as $c) {
                $cWords = array_flip(explode(' ', $c->nombre_norm));
                $allInPat = true;
                $allInLiq = true;
                foreach ($liqWords as $w => $_) if (!isset($cWords[$w])) {
                    $allInPat = false;
                    break;
                }
                foreach ($cWords as $w => $_) if (!isset($liqWords[$w])) {
                    $allInLiq = false;
                    break;
                }
                if ($allInPat || $allInLiq) {
                    $telefonos[$nombreNorm] = $c->telefono;
                    break;
                }
            }
        }

        return $telefonos;
    }

    // ---------- helpers ----------

    private function hojaAGrid(Worksheet $sheet): array
    {
        $grid = [];

        foreach ($sheet->getRowIterator() as $row) {
            $r = $row->getRowIndex() - 1;
            $fila = [];

            foreach ($row->getCellIterator() as $cell) {

                $c = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
                    $cell->getColumn()
                ) - 1;

                /*
             * Si la celda contiene una fórmula, necesitamos
             * el RESULTADO calculado y no "=C10+D10".
             */
                if (
                    $cell->getDataType() ===
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA
                ) {
                    $value = $cell->getCalculatedValue();
                } else {
                    $value = $cell->getValue();
                }

                if ($value !== null && Date::isDateTime($cell)) {
                    $value = Date::excelToDateTimeObject($value);
                }

                $fila[$c] = $value;
            }

            $grid[$r] = $fila;
        }

        return $grid;
    }

    private function normalizarNombre(?string $s): string
    {
        if (!$s) return '';
        $s = mb_strtoupper(trim($s), 'UTF-8');
        $s = \Normalizer::normalize($s, \Normalizer::FORM_D);
        $s = preg_replace('/\p{Mn}/u', '', $s);
        return trim(preg_replace('/\s+/', ' ', $s));
    }

    private function formatearPeriodo($periodoRaw, string $sheetName): string
    {
        if ($periodoRaw instanceof \DateTimeInterface) {
            return $this->meses[(int) $periodoRaw->format('n') - 1] . '-' . $periodoRaw->format('y');
        }
        if (is_string($periodoRaw) && trim($periodoRaw) !== '') {
            $val = trim($periodoRaw);
            if (preg_match('/^[a-záéíóúñ]{3}-\d{2}$/iu', $val)) {
                return mb_strtolower($val, 'UTF-8');
            }
            if (preg_match('/([a-záéíóúñ]+)\D*(\d{2,4})/iu', $val, $m)) {
                return $this->abreviarMes($m[1]) . '-' . substr($m[2], -2);
            }
        }
        $clean = trim(preg_replace('/copagos/i', '', $sheetName));
        if (preg_match('/([a-záéíóúñ]+)\D*(\d{2,4})/iu', $clean, $m)) {
            return $this->abreviarMes($m[1]) . '-' . substr($m[2], -2);
        }
        return $sheetName;
    }

    private function abreviarMes(string $mes): string
    {
        $m = preg_replace('/\p{Mn}/u', '', \Normalizer::normalize(mb_strtolower($mes, 'UTF-8'), \Normalizer::FORM_D));
        return mb_substr($m, 0, 3);
    }

    private function esVerdadero($valor): bool
    {
        if ($valor === null || $valor === '') return false;
        $s = mb_strtoupper(trim((string) $valor), 'UTF-8');
        return in_array($s, ['VERDADERO', 'TRUE', '1', 'S', 'SI', 'X'], true);
    }

    private function parseNumeroArg($valor): float
    {
        if ($valor === null || $valor === '') return 0.0;
        if (is_int($valor) || is_float($valor)) return (float) $valor;
        $s = str_replace(['.', ' '], '', trim((string) $valor));
        $s = str_replace(',', '.', $s);
        return is_numeric($s) ? (float) $s : 0.0;
    }

    private function parsearFecha($valor): ?string
    {
        if ($valor === null || $valor === '') return null;
        if ($valor instanceof \DateTimeInterface) return $valor->format('Y-m-d');
        $s = trim((string) $valor);
        foreach (['d/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y', 'Y-m-d H:i:s', 'Y-m-d'] as $formato) {
            $d = \DateTime::createFromFormat($formato, $s);
            if ($d !== false) return $d->format('Y-m-d');
        }
        return null;
    }
}

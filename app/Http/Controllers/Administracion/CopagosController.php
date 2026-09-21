<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Support\ClasificadorOperacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CopagosController extends Controller
{

    public function vistaCopagos()
    {
        return view('administracion.cobranzas.copagosIps');
    }

    private array $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    public function cargarLiquidacion(Request $request)
    {
        $request->validate(['archivo' => 'required|file|mimes:xlsx,xls']);

        $spreadsheet = IOFactory::load($request->file('archivo')->getRealPath());
        $filas = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetName = $sheet->getTitle();
            $grid = $this->hojaAGrid($sheet);

            $headerRow = null;
            foreach (array_slice($grid, 0, 10, true) as $r => $row) {
                if (($row[0] ?? null) === 'FIN') {
                    $headerRow = $r;
                    break;
                }
            }
            if ($headerRow === null) continue;

            $periodoRaw = null;
            foreach (array_slice($grid, 0, 6, true) as $r => $row) {
                if (($row[0] ?? null) === 'Período') $periodoRaw = $row[1] ?? null;
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
                if ($fin === 'TOTAL') continue;
                if (!$nombre) continue;

                $filas[] = [
                    'fin' => (string) $fin,
                    'nombre' => (string) $nombre,
                    'nombre_norm' => $this->normalizarNombre($nombre),
                    'periodo' => $periodoLabel,
                    'hoja_origen' => $sheetName,
                    'practicas' => (float) ($practicas ?? 0),
                    'internacion' => (float) ($internac ?? 0),
                    'total' => (float) ($total ?? 0),
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

        return response()->json(['message' => count($filas) . ' filas cargadas.', 'filas' => count($filas)]);
    }

    public function listarLiquidacion()
    {
        return response()->json(['data' => DB::select('CALL SP_COPAGOS_LIQUIDACION_LISTAR()')]);
    }

    private function hojaAGrid(Worksheet $sheet): array
    {
        $grid = [];
        foreach ($sheet->getRowIterator() as $row) {
            $r = $row->getRowIndex() - 1;
            $fila = [];
            foreach ($row->getCellIterator() as $cell) {
                $c = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($cell->getColumn()) - 1;
                $value = $cell->getValue();
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
        $clean = trim(preg_replace('/copagos/i', '', $sheetName));
        if (preg_match('/([a-záéíóúñ]+)\D*(\d{2,4})/iu', $clean, $m)) {
            $mes = \Normalizer::normalize(mb_strtolower($m[1], 'UTF-8'), \Normalizer::FORM_D);
            $mes = preg_replace('/\p{Mn}/u', '', $mes);
            return mb_substr($mes, 0, 3) . '-' . substr($m[2], -2);
        }
        return $sheetName;
    }

    public function cargarCaja(Request $request)
    {
        $request->validate(['archivo' => 'required|file|mimes:xlsx,xls']);

        $spreadsheet = IOFactory::load($request->file('archivo')->getRealPath());
        $grid = $this->hojaAGrid($spreadsheet->getSheet(0));

        if (!$grid) {
            return response()->json(['message' => 'El archivo está vacío.'], 422);
        }

        $headers = array_map(fn($h) => $h === null ? '' : trim((string) $h), $grid[0]);
        $necesarias = ['me_fecha', 'me_ape', 'imp', 'conccaja_nombre', 'mve_anulado', 'osplan'];
        $idx = [];
        $faltantes = [];
        foreach ($necesarias as $h) {
            $pos = array_search($h, $headers, true);
            if ($pos === false) {
                $faltantes[] = $h;
                continue;
            }
            $idx[$h] = $pos;
        }
        if ($faltantes) {
            return response()->json([
                'message' => 'Faltan columnas esperadas en el archivo de caja: ' . implode(', ', $faltantes) . '.',
            ], 422);
        }

        $filas = [];
        foreach ($grid as $r => $row) {
            if ($r === 0) continue;
            $conc = $row[$idx['conccaja_nombre']] ?? null;
            if ($conc !== 'Facturación Copago (exento)') continue;
            if (!empty($row[$idx['mve_anulado']])) continue;
            $nombre = $row[$idx['me_ape']] ?? null;
            if (!$nombre) continue;

            $fecha = $row[$idx['me_fecha']] ?? null;
            if ($fecha instanceof \DateTimeInterface) $fecha = $fecha->format('Y-m-d');
            elseif ($fecha !== null) $fecha = substr((string) $fecha, 0, 10);

            $filas[] = [
                'fecha' => $fecha,
                'nombre' => (string) $nombre,
                'nombre_norm' => $this->normalizarNombre($nombre),
                'importe' => (float) ($row[$idx['imp']] ?? 0),
                'osplan' => $row[$idx['osplan']] !== null ? (string) $row[$idx['osplan']] : null,
            ];
        }

        DB::statement('CALL SP_COPAGOS_CAJA_CARGAR(?, ?, ?)', [
            json_encode($filas, JSON_UNESCAPED_UNICODE),
            $request->file('archivo')->getClientOriginalName(),
            auth()->id(),
        ]);

        return response()->json([
            'message' => count($filas) . ' pagos "Facturación Copago (exento)" identificados.',
            'filas' => count($filas),
        ]);
    }

    public function listarCaja()
    {
        return response()->json(['data' => DB::select('CALL SP_COPAGOS_CAJA_LISTAR()')]);
    }

    public function obtenerCruce()
    {
        $liqRows = DB::select('CALL SP_COPAGOS_LIQUIDACION_LISTAR()');
        $cajaRows = DB::select('CALL SP_COPAGOS_CAJA_LISTAR()');
        $pacientesRows = DB::select('CALL SP_COPAGOS_PACIENTES_LISTAR()');
        $notas = collect(DB::select('CALL SP_COPAGOS_NOTAS_LISTAR()'))->keyBy('nombre_norm');

        $cajaConPalabras = array_map(fn($c) => ['row' => $c, 'words' => array_flip(explode(' ', $c->nombre_norm))], $cajaRows);
        $pacientesConPalabras = array_map(fn($p) => ['row' => $p, 'words' => array_flip(explode(' ', $p->nombre_norm))], $pacientesRows);

        $porNombre = [];
        foreach ($liqRows as $r) $porNombre[$r->nombre_norm][] = $r;

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

            $telefono = null;
            foreach ($pacientesConPalabras as $p) {
                $allInPat = true;
                $allInLiq = true;
                foreach ($liqWords as $w => $_) if (!isset($p['words'][$w])) {
                    $allInPat = false;
                    break;
                }
                foreach ($p['words'] as $w => $_) if (!isset($liqWords[$w])) {
                    $allInLiq = false;
                    break;
                }
                if ($allInPat || $allInLiq) {
                    $telefono = $p['row']->telefono;
                    break;
                }
            }

            $nota = $notas->get($nombreNorm);

            $pacientes[] = [
                'nombreNorm' => $nombreNorm,
                'nombreDisplay' => $filas[0]->nombre,
                'telefono' => $telefono,
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

        usort($pacientes, fn($a, $b) => $b['diferencia'] <=> $a['diferencia']);

        return response()->json(['data' => $pacientes]);
    }

    public function guardarNota(Request $request)
    {
        $data = $request->validate(['nombre_norm' => 'required|string|max:150', 'nota' => 'nullable|string|max:500']);
        DB::statement('CALL SP_COPAGOS_NOTA_GUARDAR(?, ?, ?)', [$data['nombre_norm'], $data['nota'] ?? null, auth()->id()]);
        return response()->json(['message' => 'Nota guardada.']);
    }

    public function marcarResuelto(Request $request)
    {
        $data = $request->validate(['nombre_norm' => 'required|string|max:150', 'resuelto' => 'required|boolean']);
        DB::statement('CALL SP_COPAGOS_RESUELTO_MARCAR(?, ?, ?)', [$data['nombre_norm'], $data['resuelto'] ? 1 : 0, auth()->id()]);
        return response()->json(['message' => 'Estado actualizado.']);
    }

    public function cargarPacientes(Request $request)
    {
        $request->validate(['archivo' => 'required|file|mimes:xlsx,xls,pdf']);
        $file = $request->file('archivo');
        $esPdf = strtolower($file->getClientOriginalExtension()) === 'pdf';

        $resultado = $esPdf
            ? $this->parsearPacientesPdf($file->getRealPath())
            : $this->parsearPacientesExcel($file->getRealPath());

        if (isset($resultado['error'])) {
            return response()->json(['message' => $resultado['error']], 422);
        }

        $total = 0;
        foreach (array_chunk($resultado, 2000) as $lote) {
            DB::statement('CALL SP_COPAGOS_PACIENTES_CARGAR_LOTE(?)', [json_encode($lote, JSON_UNESCAPED_UNICODE)]);
            $total += count($lote);
        }

        DB::statement('CALL SP_COPAGOS_CARGA_REGISTRAR(?, ?, ?, ?)', ['pacientes', $file->getClientOriginalName(), $total, auth()->id()]);

        return response()->json(['message' => $total . ' contactos procesados (nuevos + actualizados).', 'filas' => $total]);
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

        if ($telIdx === null) return ['error' => 'No encontré columna de teléfono/celular. Encabezados: ' . implode(', ', array_map('strval', $headers))];
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
}

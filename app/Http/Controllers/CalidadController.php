<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CalidadController extends Controller
{
    public function encuestasCalidad()
    {
        return view('calidad.encuestas');
    }

    public function dashboardCalidad()
    {
        return view('calidad.dashboardCalidad');
    }

    public function cmiCalidad()
    {
        return view('calidad.cmi');
    }

    public function listarTiposEncuestas()
    {
        try {

            $tipos = DB::select('CALL SP_OBTENER_TIPO_ENCUESTAS()');

            return response()->json($tipos);
        } catch (\Exception $e) {

            return response()->json([
                'error' => true,
                'mensaje' => 'Error al obtener tipos de encuestas',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function importarExcel(Request $request)
    {
        $file = $request->file('excel');

        $sheet = IOFactory::load($file->getPathname())->getActiveSheet();
        $rows = $sheet->toArray();

        return response()->json([
            'headers' => $rows[0],
            'rows' => array_slice($rows, 1)
        ]);
    }

    private function parseFecha($valor)
    {
        if (!$valor) return null;

        try {
            return \Carbon\Carbon::createFromFormat('d/m/Y H:i', trim($valor))
                ->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null; // o log si querés
        }
    }

    public function procesarExcel(Request $request)
    {
        $file = $request->file('excel');
        $idTipoEncuesta = (int) $request->input('idTipoEncuesta');

        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) < 2) {
            return response()->json([
                'error' => true,
                'mensaje' => 'El archivo no contiene datos suficientes'
            ], 422);
        }

        $headers = array_map(fn($h) => strtoupper(trim($h)), $rows[0]);

        // 🔎 Obtener tipo encuesta (para saber si es guardia o internacion)
        $tipoEncuesta = DB::table('tipos_encuestas')
            ->where('idTipoEncuesta', $idTipoEncuesta)
            ->first();

        if (!$tipoEncuesta) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Tipo de encuesta inválido'
            ], 422);
        }

        $esInternacion = $tipoEncuesta->grupo === 'INTERNACION';

        // 📌 Definir columnas base
        $colInicioPreguntas = $esInternacion ? 6 : 4;

        // 📌 Obtener preguntas BD
        $preguntasDB = DB::table('preguntas_encuestas')
            ->where('idTipoEncuesta', $idTipoEncuesta)
            ->orderBy('orden')
            ->get()
            ->values();

        if ($preguntasDB->count() === 0) {
            return response()->json([
                'error' => true,
                'mensaje' => 'No hay preguntas configuradas'
            ], 422);
        }

        // 🔥 VALIDACIÓN HEADER P1, P2...
        foreach ($preguntasDB as $i => $preg) {
            $esperado = 'P' . ($i + 1);
            if (($headers[$colInicioPreguntas + $i] ?? '') !== $esperado) {
                return response()->json([
                    'error' => true,
                    'mensaje' => "Se esperaba columna $esperado"
                ], 422);
            }
        }

        DB::beginTransaction();

        try {

            // 🧾 Crear importación
            $idImportacion = DB::table('importaciones_encuestas')->insertGetId([
                'idTipoEncuesta' => $idTipoEncuesta,
                'nombreArchivo' => $file->getClientOriginalName(),
                'usuario' => auth()->id() ?? null,
                'fechaImportacion' => now()
            ]);

            $registros = [];

            // 🔁 Recorrer filas
            for ($i = 1; $i < count($rows); $i++) {

                $fila = $rows[$i];
                if (empty(array_filter($fila))) continue;

                $numeroAtencion = $fila[0] ?? null;
                $fechaAtencion = $this->parseFecha($fila[1] ?? null);
                $fechaEncuesta = $this->parseFecha($fila[2] ?? null);
                $paciente = $fila[3] ?? null;
                $tipoInternacion = $esInternacion ? ($fila[4] ?? null) : null;
                $area = $esInternacion ? ($fila[5] ?? null) : null;

                foreach ($preguntasDB as $idx => $preg) {

                    $colIndex = $colInicioPreguntas + $idx;
                    $valorRaw = trim((string)($fila[$colIndex] ?? ''));

                    $registros[] = [
                        'idImportacion' => $idImportacion,
                        'idTipoEncuesta' => $idTipoEncuesta,
                        'idPregunta' => $preg->idPregunta,

                        'numeroAtencion' => $numeroAtencion,
                        'fechaAtencion' => $fechaAtencion,
                        'fechaEncuesta' => $fechaEncuesta,
                        'paciente' => $paciente,
                        'tipoInternacion' => $tipoInternacion,
                        'area' => $area,

                        'valorNumerico' => $preg->tipoRespuesta === 'NUMERICA' && is_numeric($valorRaw)
                            ? (int)$valorRaw
                            : null,

                        'valorTexto' => $preg->tipoRespuesta === 'SI_NO'
                            ? strtoupper($valorRaw)
                            : null,
                    ];
                }
            }

            // 🚀 Insert masivo en chunks
            foreach (array_chunk($registros, 1000) as $chunk) {
                DB::table('respuestas_encuestas')->insert($chunk);
            }

            // actualizar cantidad
            DB::table('importaciones_encuestas')
                ->where('idImportacion', $idImportacion)
                ->update(['cantidadRegistros' => count($registros)]);

            DB::commit();

            return response()->json([
                'ok' => true,
                'idImportacion' => $idImportacion,
                'filasProcesadas' => count($rows) - 1,
                'registrosInsertados' => count($registros)
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => true,
                'mensaje' => 'Error al procesar archivo',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function resultados($idImportacion)
    {
        try {

            $resultados = DB::table('respuestas_encuestas as r')
                ->join('preguntas_encuestas as p', 'p.idPregunta', '=', 'r.idPregunta')
                ->select(
                    'r.idPregunta',
                    'p.texto',
                    DB::raw('SUM(CASE WHEN r.valorNumerico >= 4 THEN 1 ELSE 0 END) as positivas'),
                    DB::raw('SUM(CASE WHEN r.valorNumerico BETWEEN 1 AND 3 THEN 1 ELSE 0 END) as negativas'),
                    DB::raw('SUM(CASE WHEN r.valorNumerico = 0 THEN 1 ELSE 0 END) as no_aplica'),
                    DB::raw('COUNT(r.valorNumerico) as total'),
                    DB::raw('ROUND(
                    SUM(CASE WHEN r.valorNumerico >= 4 THEN 1 ELSE 0 END) / 
                    NULLIF(COUNT(r.valorNumerico), 0) * 100, 2
                ) as porc_positivas')
                )
                ->where('r.idImportacion', $idImportacion)
                ->whereNotNull('r.valorNumerico') // excluye SI/NO
                ->groupBy('r.idPregunta', 'p.texto', 'p.orden')
                ->orderBy('p.orden')
                ->get();

            // 🔹 análisis SI/NO (última pregunta)
            $sino = DB::table('respuestas_encuestas as r')
                ->join('preguntas_encuestas as p', 'p.idPregunta', '=', 'r.idPregunta')
                ->select(
                    'r.idPregunta',
                    'p.texto',
                    DB::raw("SUM(CASE WHEN UPPER(r.valorTexto) = 'SI' THEN 1 ELSE 0 END) as si"),
                    DB::raw("SUM(CASE WHEN UPPER(r.valorTexto) = 'NO' THEN 1 ELSE 0 END) as no")
                )
                ->where('r.idImportacion', $idImportacion)
                ->whereNotNull('r.valorTexto')
                ->groupBy('r.idPregunta', 'p.texto', 'p.orden')
                ->orderBy('p.orden')
                ->get();

            return response()->json([
                'ok' => true,
                'idImportacion' => $idImportacion,
                'resultados' => $resultados,
                'sino' => $sino
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => true,
                'mensaje' => 'Error al obtener resultados',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function dashboardResultados(Request $request)
    {
        $desde = $request->desde === "null" || $request->desde === "" ? null : $request->desde;
        $hasta = $request->hasta === "null" || $request->hasta === "" ? null : $request->hasta;
        $tipo  = $request->tipo === "null" || $request->tipo === "" ? null : $request->tipo;

        $resultados = DB::select('CALL SP_DASHBOARD_RESULTADOS(?, ?, ?)', [
            $desde,
            $hasta,
            $tipo
        ]);

        return response()->json($resultados);
    }

    public function dashboardCompleto(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $tipo  = $request->query('tipo'); // opcional

        try {

            return response()->json([

                // KPI
                'kpi' => DB::select(
                    'CALL SP_KPI_GENERAL(?, ?, ?)',
                    [$desde, $hasta, $tipo]
                ),

                // Guardia adulto / pediatrica
                'guardias' => DB::select(
                    'CALL SP_TABLA_GUARDIAS(?, ?, ?)',
                    [$desde, $hasta, $tipo]
                ),

                // Internación por área
                'areas' => DB::select(
                    'CALL SP_TABLA_AREAS(?, ?, ?)',
                    [$desde, $hasta, $tipo]
                ),

                // UTI detalle
                'uti' => DB::select(
                    'CALL SP_TABLA_UTI(?, ?, ?)',
                    [$desde, $hasta, $tipo]
                ),

                // Internación ambulatoria vs no
                'internacion' => DB::select(
                    'CALL SP_TABLA_INTERNACION(?, ?, ?)',
                    [$desde, $hasta, $tipo]
                ),

            ]);

        } catch (\Exception $e) {

            return response()->json([
                'error' => true,
                'mensaje' => 'Error en dashboard',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}

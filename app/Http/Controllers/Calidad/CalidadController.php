<?php

namespace App\Http\Controllers\Calidad;

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

    public function dashboardCalidadPublico()
    {
        return view('calidad.dashboardPublico');
    }

    public function cmiCalidad()
    {
        return view('calidad.cmi');
    }

    public function respuestasEncuestas()
    {
        return view('calidad.respuestasEncuestas');
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

        \Log::debug('parseFecha input', [
            'valor' => $valor,
            'tipo'  => gettype($valor),
            'es_datetime' => $valor instanceof \DateTime,
        ]);

        try {
            if ($valor instanceof \DateTime) {
                return \Carbon\Carbon::instance($valor)->format('Y-m-d H:i:s');
            }

            if (is_numeric($valor)) {
                return \Carbon\Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valor)
                )->format('Y-m-d H:i:s');
            }

            $valor = trim((string)$valor);

            foreach (['d/m/Y H:i:s', 'd/m/Y H:i'] as $formato) {
                try {
                    return \Carbon\Carbon::createFromFormat($formato, $valor)
                        ->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    continue;
                }
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('parseFecha error', [
                'valor' => $valor,
                'mensaje' => $e->getMessage()
            ]);

            return null;
        }
    }

    public function procesarExcel(Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '1024M');

        $file = $request->file('excel');
        $idTipoEncuesta = (int) $request->input('idTipoEncuesta');

        try {

            // =========================
            // LEER EXCEL
            // =========================

            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (count($rows) < 2) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'El archivo no contiene datos suficientes'
                ], 422);
            }

            // =========================
            // VALIDAR TIPO ENCUESTA
            // =========================

            $tipoEncuesta = DB::table('tipos_encuestas')
                ->where('idTipoEncuesta', $idTipoEncuesta)
                ->first();

            if (!$tipoEncuesta) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Tipo de encuesta inválido'
                ], 422);
            }

            $headers = array_values(
                array_map(
                    fn($h) => strtoupper(trim((string)$h)),
                    $rows[0]
                )
            );

            $esInternacion = $tipoEncuesta->grupo === 'INTERNACION';

            // =========================
            // BUSCAR P1
            // =========================

            $colInicioPreguntas = array_search('P1', $headers, true);

            if ($colInicioPreguntas === false) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'No se encontró la columna P1 en el archivo'
                ], 422);
            }

            // =========================
            // OBTENER PREGUNTAS
            // =========================

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

            // =========================
            // VALIDAR COLUMNAS P1..Pn
            // =========================

            foreach ($preguntasDB as $i => $preg) {

                $esperado = 'P' . ($i + 1);

                if (($headers[$colInicioPreguntas + $i] ?? '') !== $esperado) {

                    return response()->json([
                        'error' => true,
                        'mensaje' => "Se esperaba columna $esperado"
                    ], 422);
                }
            }

            // =========================
            // TRANSACCION
            // =========================

            DB::beginTransaction();

            // =========================
            // CREAR IMPORTACION
            // =========================

            DB::statement(
                "CALL SP_GUARDAR_ENCUESTAS_IMPORTACION(?,?,?,?,@idImportacion)",
                [
                    $idTipoEncuesta,
                    $file->getClientOriginalName(),
                    auth()->id() ?? null,
                    0
                ]
            );

            $idImportacion = DB::select(
                "SELECT @idImportacion as id"
            )[0]->id;

            $registrosInsertados = 0;
            $filasProcesadas = 0;

            // BATCH DE RESPUESTAS
            $respuestasBatch = [];

            // =========================
            // RECORRER FILAS
            // =========================

            for ($i = 1; $i < count($rows); $i++) {

                $fila = $rows[$i];

                // SALTAR FILAS VACIAS
                if (empty(array_filter($fila))) {
                    continue;
                }

                // =========================
                // DATOS ENCABEZADO
                // =========================

                $numeroAtencion = $fila[0] ?? null;

                $fechaAtencion = $this->parseFecha(
                    $fila[1] ?? null
                );

                $fechaEncuesta = $this->parseFecha(
                    $fila[2] ?? null
                );

                $paciente = $fila[3] ?? null;
                $os = $fila[4] ?? null;
                $plan = $fila[5] ?? null;

                $area = $esInternacion
                    ? ($fila[6] ?? null)
                    : null;

                $cama = $esInternacion
                    ? ($fila[7] ?? null)
                    : null;

                $tipoInternacion = $esInternacion
                    ? ($fila[8] ?? null)
                    : null;

                // =========================
                // INSERT ENCABEZADO
                // =========================

                DB::statement(
                    "CALL SP_GUARDAR_ENCUESTAS_ENC(
                    ?,?,?,?,?,?,?,?,?,?,?,?,@idEncuesta
                )",
                    [
                        $idImportacion,
                        $idTipoEncuesta,
                        $numeroAtencion,
                        $fechaAtencion,
                        $fechaEncuesta,
                        $paciente,
                        $os,
                        $plan,
                        $area,
                        $cama,
                        $tipoInternacion,
                        null
                    ]
                );

                $idEncuesta = DB::select(
                    "SELECT @idEncuesta as id"
                )[0]->id;

                // =========================
                // ARMAR RESPUESTAS
                // =========================

                foreach ($preguntasDB as $idx => $preg) {

                    $colIndex = $colInicioPreguntas + $idx;

                    $valorRaw = trim(
                        (string)($fila[$colIndex] ?? '')
                    );

                    $valorNumerico = null;
                    $valorTexto = null;

                    // NUMERICA
                    if (
                        $preg->tipoRespuesta === 'NUMERICA'
                        && is_numeric($valorRaw)
                    ) {

                        $valorNumerico = (int)$valorRaw;
                    }

                    // SI/NO
                    if ($preg->tipoRespuesta === 'SI_NO') {

                        $valorTexto = strtoupper($valorRaw);
                    }

                    // AGREGAR AL BATCH
                    $respuestasBatch[] = [
                        'idEncabezadoEncuesta' => $idEncuesta,
                        'idPregunta' => $preg->idPregunta,
                        'valorNumerico' => $valorNumerico,
                        'valorTexto' => $valorTexto
                    ];

                    $registrosInsertados++;
                }

                // =========================
                // INSERT MASIVO CADA 1000
                // =========================

                if (count($respuestasBatch) >= 1000) {

                    DB::table('respuestas_encuestas')
                        ->insert($respuestasBatch);

                    $respuestasBatch = [];
                }

                $filasProcesadas++;
            }

            // =========================
            // INSERT FINAL
            // =========================

            if (!empty($respuestasBatch)) {

                DB::table('respuestas_encuestas')
                    ->insert($respuestasBatch);
            }

            // =========================
            // ACTUALIZAR IMPORTACION
            // =========================

            DB::table('importaciones_encuestas')
                ->where('idImportacion', $idImportacion)
                ->update([
                    'cantidadRegistros' => $registrosInsertados
                ]);

            DB::commit();

            return response()->json([
                'ok' => true,
                'idImportacion' => $idImportacion,
                'filasProcesadas' => $filasProcesadas,
                'registrosInsertados' => $registrosInsertados
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('ERROR IMPORTANDO ENCUESTAS', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile()
            ]);

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

    public function dashboardCompleto(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $tipo  = $request->query('tipo');

        try {

            return response()->json([

                // KPI
                'kpi' => DB::select(
                    'CALL SP_KPI_GENERAL(?, ?, ?)',
                    [$desde, $hasta, $tipo]
                ),

                'kpiGuardiaGeneral' => DB::select(
                    'CALL SP_KPI_PROMEDIO_GUARDIAS(?, ?)',
                    [$desde, $hasta]
                ),

                'kpiInternacionGeneral' => DB::select(
                    'CALL SP_KPI_PROMEDIO_INTERNACION(?, ?)',
                    [$desde, $hasta]
                ),

                'kpiInternacionAmbulatoria' => DB::select(
                    'CALL SP_KPI_PROMEDIO_AMBULATORIAS(?, ?)',
                    [$desde, $hasta]
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

                // Expectivas UTI's
                'expectativasAmbulatoria' => DB::select(
                    'CALL SP_TABLA_AMBULATORIA(?, ?, ?)',
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

                'guardia_adulto_preguntas' => DB::select(
                    'CALL SP_ANALISIS_GUARDIA_ADULTO_PREGUNTAS(?, ?)',
                    [$desde, $hasta]
                ),

                'guardia_pediatrica_preguntas' => DB::select(
                    'CALL SP_ANALISIS_GUARDIA_PED_PREGUNTAS(?, ?)',
                    [$desde, $hasta]
                ),

                'guardia_general_preguntas' => DB::select(
                    'CALL SP_ANALISIS_GUARDIA_GENERAL_PREGUNTAS(?, ?)',
                    [$desde, $hasta]
                ),

                'internacion_preguntas' => DB::select(
                    'CALL SP_ANALISIS_INTERNACION_EST_PREGUNTAS(?, ?)',
                    [$desde, $hasta]
                ),

                'internacion_preguntas_design' => DB::select(
                    'CALL SP_ANALISIS_INTERNACION_EST_PREGUNTAS_DESIGN(?, ?)',
                    [$desde, $hasta]
                ),

                'internacion_preguntas_standard' => DB::select(
                    'CALL SP_ANALISIS_INTERNACION_EST_PREGUNTAS_STANDARD(?, ?)',
                    [$desde, $hasta]
                ),

                'internacion_preguntas_utis' => DB::select(
                    'CALL SP_ANALISIS_INTERNACION_EST_PREGUNTAS_UTIS(?, ?)',
                    [$desde, $hasta]
                ),

                'internacion_preguntas_utia' => DB::select(
                    'CALL SP_ANALISIS_INTERNACION_EST_PREGUNTAS_UTIA(?, ?)',
                    [$desde, $hasta]
                ),

                'internacion_preguntas_utip' => DB::select(
                    'CALL SP_ANALISIS_INTERNACION_EST_PREGUNTAS_UTIP(?, ?)',
                    [$desde, $hasta]
                ),
                
                'internacion_preguntas_utineo' => DB::select(
                    'CALL SP_ANALISIS_INTERNACION_EST_PREGUNTAS_UTINEO(?, ?)',
                    [$desde, $hasta]
                ),

                'internacion_preguntas_otros' => DB::select(
                    'CALL SP_ANALISIS_INTERNACION_EST_PREGUNTAS_OTROS(?, ?)',
                    [$desde, $hasta]
                ),

                'internacion_preguntas_general' => DB::select(
                    'CALL SP_ANALISIS_INTERNACION_EST_PREGUNTAS_GENERAL(?, ?)',
                    [$desde, $hasta]
                ),

                'internacion_amb_preguntas' => DB::select(
                    'CALL SP_ANALISIS_INTERNACION_AMB_PREGUNTAS(?, ?)',
                    [$desde, $hasta]
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

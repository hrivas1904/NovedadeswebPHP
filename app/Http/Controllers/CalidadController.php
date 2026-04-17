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

        $headers = array_values(array_map(fn($h) => strtoupper(trim((string)$h)), $rows[0]));

        $esInternacion = $tipoEncuesta->grupo === 'INTERNACION';

        // Buscar automáticamente dónde empieza P1
        $colInicioPreguntas = array_search('P1', $headers, true);

        if ($colInicioPreguntas === false) {
            return response()->json([
                'error' => true,
                'mensaje' => 'No se encontró la columna P1 en el archivo'
            ], 422);
        }

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

            //CREAR BLOQUE DE IMPORTACION DE ENCABEZADO+DETALLE DE LA ENCUESTA
            DB::statement("CALL SP_GUARDAR_ENCUESTAS_IMPORTACION(?,?,?,?,@idImportacion)", [
                $idTipoEncuesta,
                $file->getClientOriginalName(),
                auth()->id() ?? null,
                0
            ]);

            $idImportacion = DB::select("SELECT @idImportacion as id")[0]->id;

            $registrosInsertados = 0;

            //RECORRER FILAS DEL DATATABLE DE PREVIEW
            for ($i = 1; $i < count($rows); $i++) {

                $fila = $rows[$i];
                if (empty(array_filter($fila))) continue;

                $numeroAtencion = $fila[0] ?? null;
                $fechaAtencion = $this->parseFecha($fila[1] ?? null);
                $fechaEncuesta = $this->parseFecha($fila[2] ?? null);
                $paciente = $fila[3] ?? null;
                $os = $fila[4] ?? null;
                $plan = $fila[5] ?? null;

                $area = $esInternacion ? ($fila[6] ?? null) : null;
                $cama = $esInternacion ? ($fila[7] ?? null) : null;
                $tipoInternacion = $esInternacion ? ($fila[8] ?? null) : null;

                //INSERT ENCABEZADO DE LA ENCUESTA
                DB::statement("CALL SP_GUARDAR_ENCUESTAS_ENC(?,?,?,?,?,?,?,?,?,?,?,?,@idEncuesta)", [
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
                ]);

                $idEncuesta = DB::select("SELECT @idEncuesta as id")[0]->id;

                //INSERTAR DETALLE DE LA ENCUESTA --> RESPUESTAS
                foreach ($preguntasDB as $idx => $preg) {

                    $colIndex = $colInicioPreguntas + $idx;
                    $valorRaw = trim((string)($fila[$colIndex] ?? ''));

                    $valorNumerico = null;
                    $valorTexto = null;

                    if ($preg->tipoRespuesta === 'NUMERICA' && is_numeric($valorRaw)) {
                        $valorNumerico = (int)$valorRaw;
                    }

                    if ($preg->tipoRespuesta === 'SI_NO') {
                        $valorTexto = strtoupper($valorRaw);
                    }

                    DB::statement("CALL SP_GUARDAR_ENCUESTAS_DET(?,?,?,?)", [
                        $idEncuesta,
                        $preg->idPregunta,
                        $valorNumerico,
                        $valorTexto
                    ]);

                    $registrosInsertados++;
                }
            }

            //ACTUALIZAR CANTIDAD DE REGISTROS IMPORTADOS
            DB::table('importaciones_encuestas')
                ->where('idImportacion', $idImportacion)
                ->update([
                    'cantidadRegistros' => $registrosInsertados
                ]);

            DB::commit();

            return response()->json([
                'ok' => true,
                'idImportacion' => $idImportacion,
                'filasProcesadas' => count($rows) - 1,
                'registrosInsertados' => $registrosInsertados
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => true,
                'mensaje' => 'Error al procesar archivo',
                'detalle' => $e->getMessage()
            ], 500);
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

                'guardia_adulto_preguntas' => DB::select(
                    'CALL SP_ANALISIS_GUARDIA_ADULTO_PREGUNTAS(?, ?)',
                    [$desde, $hasta]
                ),

                'internacion_preguntas' => DB::select(
                    'CALL SP_ANALISIS_INTERNACION_EST_PREGUNTAS(?, ?)',
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

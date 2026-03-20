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
        $colInicioPreguntas = $esInternacion ? 5 : 4;

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
                $fechaAtencion = $fila[1] ?? null;
                $fechaEncuesta = $fila[2] ?? null;
                $paciente = $fila[3] ?? null;
                $tipoInternacion = $esInternacion ? ($fila[4] ?? null) : null;

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
}

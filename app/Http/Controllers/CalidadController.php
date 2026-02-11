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
            $tiposEncuestas = DB::select('CALL SP_OBTENER_TIPO_ENCUESTAS()');

            return response()->json($tiposEncuestas);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener los tipos de encuestas',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function importarExcel(Request $request)
    {
        if (!$request->hasFile('excel')) {
            return response()->json(['error' => 'No se recibió archivo'], 400);
        }

        $file = $request->file('excel');

        // Cargar Excel
        $spreadsheet = IOFactory::load($file->getPathname());

        // Hoja activa
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Convertir a array
        $data = $sheet->toArray();

        return response()->json([
            'headers' => $rows[0],
            'rows' => array_slice($rows, 1)
        ]);
    }

    public function procesarExcel(Request $request)
    {
        $file = $request->file('excel');

        $sheet = IOFactory::load($file)->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) < 2) {
            return response()->json([
                'error' => true,
                'mensaje' => 'El archivo no contiene datos suficientes'
            ]);
        }

        // Normalizar headers
        $headers = array_map(function ($h) {
            return trim(mb_strtoupper($h, 'UTF-8'));
        }, $rows[0]);

        // Mapeo de columnas por nombre
        $map = [
            'numero_atencion' => array_search('Nº ATENCIÓN', $headers),
            'fecha_atencion' => array_search('FECHA ATENCIÓN', $headers),
            'fecha_encuesta' => array_search('FECHA ENCUESTA', $headers),
            'paciente' => array_search('PACIENTE', $headers),
            'obra_social' => array_search('OBRA SOCIAL', $headers),
            'plan' => array_search('PLAN', $headers),
            'area' => array_search('AREA', $headers),
            'cama' => array_search('CAMA', $headers),
            'tipo_internacion' => array_search('TIPO INTERNACIÓN', $headers),
        ];

        $columnasContexto = array_filter($map, fn($v) => $v !== false);

        sort($columnasContexto);

        $ultimoIndiceContexto = max($columnasContexto);

        $indicesPreguntas = [];

        for ($i = $ultimoIndiceContexto + 1; $i < count($headers); $i++) {
            $indicesPreguntas[] = $i;
        }

        $indicesPreguntas = [];
        foreach ($headers as $i => $h) {
            if (!in_array($i, $columnasContexto)) {
                $indicesPreguntas[] = $i;
            }
        }

        $idTipoEncuesta = (int) $request->input('idTipoEncuesta');

        $preguntasDB = DB::table('preguntas_encuestas')
            ->where('idTipoEncuesta', $idTipoEncuesta)
            ->orderBy('orden')
            ->get();

        if ($preguntasDB->count() === 0) {
            return response()->json([
                'error' => true,
                'mensaje' => 'No hay preguntas configuradas para este tipo de encuesta'
            ], 422);
        }

        if (count($indicesPreguntas) !== $preguntasDB->count()) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Cantidad de preguntas del Excel (' . count($indicesPreguntas) . ') no coincide con la configuración (' . $preguntasDB->count() . ').'
            ], 422);
        }

        $registros = [];

        for ($i = 1; $i < count($rows); $i++) {

            $fila = $rows[$i];
            if (empty(array_filter($fila))) continue;

            // contexto
            $contexto = [
                'numeroAtencion' => $fila[$map['numero_atencion']] ?? null,
                'fechaAtencion' => $fila[$map['fecha_atencion']] ?? null,
                'fechaEncuesta' => $fila[$map['fecha_encuesta']] ?? null,
                'paciente' => $fila[$map['paciente']] ?? null,
                'obraSocial' => $fila[$map['obra_social']] ?? null,
                'plan' => $fila[$map['plan']] ?? null,
                'area' => $map['area'] !== false ? ($fila[$map['area']] ?? null) : null,
                'cama' => $map['cama'] !== false ? ($fila[$map['cama']] ?? null) : null,
                'tipoInternacion' => $map['tipo_internacion'] !== false ? ($fila[$map['tipo_internacion']] ?? null) : null,
            ];

            // respuestas (1 registro por pregunta)
            foreach ($indicesPreguntas as $idx => $colIndex) {

                $preg = $preguntasDB[$idx]; // mismo orden

                $valorRaw = trim((string)($fila[$colIndex] ?? ''));

                $registros[] = [
                    'idPregunta' => $preg->idPregunta,
                    'numeroAtencion' => $contexto['numeroAtencion'],
                    'fechaAtencion' => $contexto['fechaAtencion'],
                    'fechaEncuesta' => $contexto['fechaEncuesta'],
                    'paciente' => $contexto['paciente'],
                    'obraSocial' => $contexto['obraSocial'],
                    'plan' => $contexto['plan'],
                    'area' => $contexto['area'],
                    'cama' => $contexto['cama'],
                    'tipoInternacion' => $contexto['tipoInternacion'],
                    'valorNumerico' => $preg->tipoRespuesta === 'NUMERICA' && is_numeric($valorRaw) ? (int)$valorRaw : null,
                    'valorTexto' => $preg->tipoRespuesta === 'TEXTO' ? $valorRaw : null,
                ];
            }
        }

        return response()->json([
            'ok' => true,
            'idTipoEncuesta' => $idTipoEncuesta,
            'filasExcel' => count($rows) - 1,
            'preguntasDetectadas' => count($indicesPreguntas),
            'registrosGenerados' => count($registros),
            'muestra' => array_slice($registros, 0, 5),
        ]);
    }
}

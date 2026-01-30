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

        $headers = $rows[0];

        $resultados = [];

        foreach ($headers as $col => $pregunta) {

            // limpiar encabezado
            $pregunta = trim($pregunta);

            if ($pregunta == "") continue;

            $positivas = 0;
            $negativas = 0;
            $sinResp = 0;

            for ($i = 1; $i < count($rows); $i++) {

                $valor = strtoupper(trim($rows[$i][$col]));

                if ($valor == "SI") {
                    $positivas++;
                } else if ($valor == "NO") {
                    $negativas++;
                } else {
                    $sinResp++;
                }
            }

            $resultados[] = [
                'pregunta' => $pregunta,
                'positivas' => $positivas,
                'negativas' => $negativas,
                'sinResp' => $sinResp
            ];
        }

        return response()->json([
            'data' => $resultados
        ]);
    }
}

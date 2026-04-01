<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CalendarioServController extends Controller
{
    public function calendarioServicios()
    {
        return view('calendarios.calendarioServ');
    }

    public function listarColaboradoresArea(Request $request)
    {
        $idArea = $request->idArea;
        $colaboradores = DB::select('CALL SP_LISTA_COLABS_CALENDARIO(?)', [$idArea]);
        if (empty($colaboradores)) {
            return response()->json(['error' => 'No hay datos', 'id_recibido' => $idArea]);
        }
        $data = collect($colaboradores)->map(function ($c) {
            return [
                'id' => $c->legajo,
                'text' => $c->colaborador,
                'legajo' => $c->legajo,
                'servicio' => $c->servicio
            ];
        });
        return response()->json($data);
    }

    public function guardarEvento(Request $request)
    {
        try {
            $eventos = $request->eventos;

            if (!$eventos || count($eventos) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay eventos para guardar'
                ], 400);
            }

            $registrante = Auth::user()->name ?? 'Sistema';

            DB::beginTransaction();

            foreach ($eventos as $ev) {

                // Validación por cada fila
                if (
                    empty($ev['legajo']) ||
                    empty($ev['fechaDesde']) ||
                    empty($ev['fechaHasta']) ||
                    empty($ev['turno'])
                ) {
                    throw new \Exception("Datos incompletos en una fila.");
                }

                DB::statement("CALL SP_GUARDAR_EVENTO_CALENDARIO(?, ?, ?, ?, ?, ?, ?)", [
                    $ev['legajo'],
                    $ev['fechaDesde'],
                    $ev['fechaHasta'],
                    $ev['caja'],
                    $ev['turno'],
                    $registrante,
                    $ev['horas']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Eventos guardados correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerEventos($idArea)
    {
        try {
            // Ejecutamos el SP pasando el parámetro
            $eventos = DB::select("CALL SP_COMPLETAR_CALENDARIO(?)", [$idArea]);

            return response()->json($eventos);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function modificarEvento(Request $request)
    {
        $request->validate([
            'idEvento' => 'required|integer',
            'fechaInterrupcion' => 'required|date'
        ]);

        try {
            DB::beginTransaction();

            DB::statement(
                "CALL SP_MODIFICAR_EVENTO_CALENDARIO(?, ?, ?, @p_mensaje)",
                [
                    $request->idEvento,
                    $request->fechaInterrupcion,
                    auth()->user()->name
                ]
            );

            $mensaje = DB::selectOne("SELECT @p_mensaje AS mensaje");

            DB::commit();

            return response()->json([
                'ok' => true,
                'mensaje' => $mensaje->mensaje
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al modificar el evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportarReporte(Request $request)
    {
        try {
            $desde = $request->fechaDesde ?: null;
            $hasta = $request->fechaHasta ?: null;

            $data = DB::select("CALL SP_REPORTE_SERVICIOS(?, ?)", [
                $desde,
                $hasta
            ]);

            // 🔥 Crear Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezados
            $sheet->fromArray([
                [
                    'Legajo',
                    'Colaborador',
                    'Desde',
                    'Hasta',
                    'Días',
                    'Horas',
                    'Caja',
                    'Turno'
                ]
            ], null, 'A1');

            // Datos
            $fila = 2;
            foreach ($data as $row) {
                $sheet->setCellValue("A{$fila}", $row->legajo);
                $sheet->setCellValue("B{$fila}", $row->colaborador);
                $sheet->setCellValue("C{$fila}", $row->fechaDesde);
                $sheet->setCellValue("D{$fila}", $row->fechaHasta);
                $sheet->setCellValue("E{$fila}", $row->cantidad_dias);
                $sheet->setCellValue("F{$fila}", $row->horas);
                $sheet->setCellValue("G{$fila}", $row->caja ? 'SI' : 'NO');
                $sheet->setCellValue("H{$fila}", $row->turno);
                $fila++;
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = 'reporte_calendario.xlsx';
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($temp_file);

            return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

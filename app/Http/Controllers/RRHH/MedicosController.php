<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Illuminate\Support\Facades\Storage;

class MedicosController extends Controller
{
    public function medicosView()
    {
        return view('medicos.medicos');
    }

    public function obtenerListaMedicos(Request $request)
    {
        $especialidad = $request->especialidad;

        try {
            $medicos = DB::select("CALL SP_OBTENER_MEDICOS(?)", [$especialidad]);
            return response()->json($medicos);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener médicos',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerLegajoMedico(int $idLegajo)
    {
        try {
            $medico = DB::select(("CALL SP_VER_LEGAJO_MEDICO(?)"), [$idLegajo]);
            return response()->json($medico[0]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => "Error al obtener el legajo del médico ${idLegajo}",
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerServiciosMedicos()
    {
        try {
            $servicios = DB::select(("select distinct(servicio) from medicos;"));
            return response()->json($servicios);
        } catch (\Exception $e) {
            return response()->json([
                'error' => "Error al obtener servicios médicos",
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function registrarNuevoMedico(Request $request)
    {
        $idUser = Auth::id();
        $nombreMedico = $request->nombreMedico;
        $cuitMedico = $request->cuitMedico;
        $matriculaMedico = $request->matriculaMedico;
        $domicilioMedico = $request->domicilioMedico;
        $correoMedico = $request->correoMedico;
        $telefonoMedico = $request->telefonoMedico;
        $servicioMedico = $request->servicioMedico;
        $razonSocialMedico = $request->razonSocialMedico;

        try {
            DB::statement('CALL SP_NUEVO_MEDICO(?,?,?,?,?,?,?,?,?)', [
                $nombreMedico,
                $matriculaMedico,
                $cuitMedico,
                $domicilioMedico,
                $correoMedico,
                $telefonoMedico,
                $servicioMedico,
                $razonSocialMedico,
                $idUser
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al guardar el médico',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonalController extends Controller
{
    public function nominaPersonal()
    {
        return view('personal.nominaPersonal');
    }

    public function controlAsistencia()
    {
        return view('personal.controlAsistencia');
    }

    public function cronogramaPersonal()
    {
        return view('personal.cronogramaPersonal');
    }

    public function registroAsistencia()
    {
        return view('personal.registroAsistencia');
    }

    public function listarAreas()
    {
        try {
            $areas = DB::select('CALL SP_LISTA_AREAS()');

            return response()->json($areas);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener áreas',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function listarCategorias()
    {
        try {
            $categorias = DB::select('CALL SP_LISTA_CATEG_EMPLEADOS()');

            return response()->json($categorias);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener categorías',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function listarRolesXCategoria($id)
    {
        try {
            $roles = DB::select(
                'CALL SP_LISTA_ROLESXCATEG_EMP(?)',
                [$id]
            );

            return response()->json($roles);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener roles',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function listarObraSocial()
    {
        try {
            $obras = DB::select('CALL SP_LISTA_OBRA_SOCIAL()');

            return response()->json($obras);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error al obtener obras sociales',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Contamos: Hay 28 signos de interrogación para los parámetros IN 
            // y el parámetro @mensaje para el OUT. Total argumentos: 29.
            DB::statement("
            CALL SP_INSERTAR_EMPLEADO(
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, ?, ?, @mensaje
            )
        ", [
                $request->legajo,               // 1: p_LEGAJO
                $request->cuil,                 // 2: p_CUIL
                $request->dni,                  // 3: p_DNI
                $request->nombre_completo,      // 4: p_COLABORADOR
                $request->domicilio,            // 5: p_DOMICILIO
                $request->localidad,            // 6: p_LOCALIDAD
                $request->estado_civil,         // 7: p_ESTADO_CIVIL
                $request->genero,               // 8: p_GENERO
                $request->fecha_nacimiento,     // 9: p_FECHA_NAC
                $request->fecha_ingreso,        // 10: p_FECHA_INGRESO
                $request->fecha_fin_prueba,     // 11: p_FECHA_FIN_PRUEBA
                $request->posee_convenio,       // 12: p_CONVENIO (En el HTML es 'posee_convenio')
                $request->area_id,              // 13: p_ID_AREA
                $request->servicio_id,          // 14: p_ID_SERVICIOS
                $request->categoria_id,         // 15: p_ID_CATEG
                $request->rol_interno_id,       // 16: p_ID_ROL
                $request->es_coordinador,       // 17: p_COORDINADOR
                $request->regimen_horas,        // 18: p_REGIMEN
                $request->horas_diarias,        // 19: p_HORAS_DIARIAS
                $request->obra_social_id,       // 20: p_ID_OS
                $request->posee_titulo,         // 21: p_TITULO
                $request->titulo_descripcion,   // 22: p_DESCRIP_TITULO
                $request->matricula_profesional, // 23: p_MAT_PROF
                $request->email,                // 24: p_CORREO
                $request->telefono,             // 25: p_TELEFONO
                $request->es_afiliado,          // 26: p_AFILIADO
                $request->estado,               // 27: p_ESTADO
                $request->tipo_contrato         // 28: p_TIPO_CONTRATO
            ]);

            $resultado = DB::select("SELECT @mensaje AS mensaje");

            return response()->json([
                'success' => true,
                'mensaje' => $resultado[0]->mensaje
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al registrar el colaborador',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function listar(Request $request)
    {
        try {
            $areaId = ($request->area_id && $request->area_id != "") ? $request->area_id : null;

            $empleados = DB::select("CALL SP_LISTA_EMPLEADOS(?)", [$areaId]);

            return response()->json([
                'data' => $empleados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function verLegajo($legajo)
    {
        try {
            $data = DB::select("CALL SP_VER_LEGAJO(?)", [$legajo]);

            if (count($data) === 0) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se encontró el legajo'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $data[0]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener el legajo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bajaEmpleado($legajo)
    {
        try {
            DB::statement("CALL SP_DAR_BAJA_EMPLEADO(?, @mensaje)", [$legajo]);

            $res = DB::select("SELECT @mensaje AS mensaje");

            return response()->json([
                'success' => true,
                'mensaje' => $res[0]->mensaje
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al dar de baja',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function infoMinimaEmpleado($legajo)
    {
        $data = DB::select("CALL SP_INFO_MINIMA_EMPLEADO(?)", [$legajo]);

        return response()->json($data[0] ?? null);
    }

    public function historialNovedades($legajo)
    {
        try {
            $data = DB::select(
                "CALL SP_LISTA_NOVEDADESXCOLABORADOR(?)",
                [$legajo]
            );

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error al cargar historial',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}

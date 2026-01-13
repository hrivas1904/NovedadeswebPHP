<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

            // Normalización de opcionales
            $fechaFinPrueba = $request->fecha_fin_prueba ?: null;
            $idOS           = $request->obra_social_id ?: null;

            DB::statement("
            CALL SP_INSERTAR_EMPLEADO(
                ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, ?, ?, @mensaje
            )
        ", [
                // 01
                $request->cuil,
                // 02
                $request->dni,
                // 03
                $request->nombre_completo,
                // 04
                $request->domicilio,
                // 05
                $request->localidad,
                // 06
                $request->estado_civil,
                // 07
                $request->genero,
                // 08
                $request->fecha_nacimiento,
                // 09
                $request->fecha_ingreso,
                // 10
                $fechaFinPrueba,
                // 11
                $request->posee_convenio,
                // 12
                $request->area_id,
                // 13
                $request->servicio_id,
                // 14
                $request->categoria_id,
                // 15
                $request->rol_interno_id,
                // 16
                $request->es_coordinador,
                // 17
                $request->regimen_horas,
                // 18
                $request->horas_diarias,
                // 19
                $idOS,
                // 20
                $request->posee_titulo,
                // 21
                $request->titulo_descripcion,
                // 22
                $request->matricula_profesional,
                // 23
                $request->email,
                // 24
                $request->telefono,
                // 25
                $request->es_afiliado,
                // 26
                $request->estado,
                // 27
                $request->tipo_contrato
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

    public function listarServiciosxArea($id)
    {
        try {
            $servicios = DB::select(
                'CALL SP_LISTA_SERVICIOS(?)',
                [$id]
            );

            return response()->json($servicios);
        } catch (\Throwable $e) {

            return response()->json([
                'error' => true,
                'mensaje' => 'Error al cargar servicios',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function show($legajo)
    {
        $empleado = DB::table('empleados')
            ->where('LEGAJO', $legajo)
            ->first();

        return response()->json($empleado);
    }

    public function update(Request $request, $legajo)
    {
        DB::table('empleados')
            ->where('LEGAJO', $legajo)
            ->update(
                $request->except(['_token', '_method', 'legajo'])
            );

        return response()->json(['success' => true]);
    }
}

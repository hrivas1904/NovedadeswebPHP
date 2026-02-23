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

    public function Configuraciones()
    {
        return view('personal.configuraciones');
    }

    public function nominaPersonalBaja()
    {
        return view('personal.personalBaja');
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

    public function listarTipoConvenio()
    {
        try {
            $convenios = DB::select('CALL SP_TIPOS_CONVENIO()');

            return response()->json($convenios);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener convenios',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function listarRegimenes()
    {
        try {
            $regimen = DB::select('CALL SP_LISTA_REGIMEN()');

            return response()->json($regimen);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener regimenes',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function listarEstados()
    {
        try {
            $estados = DB::select('CALL SP_TIPO_ESTADOS()');

            return response()->json($estados);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener estados',
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
        // Iniciamos la transacción para asegurar que si fallan los hijos, 
        // no se cree el empleado a medias.
        DB::beginTransaction();

        try {
            // Normalización de opcionales
            $fechaFinPrueba = $request->fecha_fin_prueba ?: null;
            $idOS           = $request->obra_social_id ?: null;

            DB::statement("
            CALL SP_INSERTAR_EMPLEADO(
                ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, ?, ?,
                @legajo, @mensaje
            )
        ", [
                $request->cuil,
                $request->dni,
                $request->nombre_completo,
                $request->domicilio,
                $request->localidad,
                $request->estado_civil,
                $request->genero,
                $request->fecha_nacimiento,
                $request->fecha_ingreso,
                $fechaFinPrueba,
                $request->posee_convenio,
                $request->area_id,
                $request->servicio_id,
                $request->categoria_id,
                $request->rol_interno_id,
                $request->es_coordinador,
                $request->regimen_horas,
                $request->horas_diarias,
                $idOS,
                $request->posee_titulo,
                $request->titulo,
                $request->matricula_profesional,
                $request->email,
                $request->telefono,
                $request->es_afiliado,
                $request->estado,
                $request->tipo_contrato,
                $request->persona_emerg1,
                $request->contacto_emerg1,
                $request->parentesco_emerg1,
                $request->persona_emerg2,
                $request->contacto_emerg2,
                $request->parentesco_emerg2,
                $request->padreColaborador,
                $request->madreColaborador
            ]);

            // ... después del CALL ...
            $res = DB::select("SELECT @legajo AS legajo, @mensaje AS mensaje");
            $nuevoLegajo = $res[0]->legajo;
            $mensajeSP = $res[0]->mensaje;

            if ($mensajeSP === 'Colaborador registrado correctamente') {
                // AQUÍ es donde insertas los hijos usando $nuevoLegajo
                if ($request->has('hijos')) {
                    foreach ($request->hijos as $hijo) {
                        DB::statement("CALL SP_INSERTAR_FAMILIAR(?, ?, ?)", [
                            $nuevoLegajo,
                            $hijo['nombre'],
                            'Hijo/a'
                        ]);
                    }
                }

                DB::commit();
                return response()->json([
                    'success' => true,
                    'mensaje' => "Empleado registrado con Legajo: " . $nuevoLegajo
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => $mensajeSP // Aquí saldría "El colaborador ya existe" o similar
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
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
            $areaId    = $request->area_id ?: null;
            $categId   = $request->categ_id ?: null;
            $convenio  = $request->p_convenio ?: null;
            $regimen   = $request->p_regimen ?: null;

            $empleados = DB::select(
                "CALL SP_LISTA_EMPLEADOS(?, ?, ?, ?)",
                [$areaId, $categId, $convenio, $regimen]
            );

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

    public function listarPersonalBaja(Request $request)
    {
        try {
            $areaId    = $request->area_id ?: null;
            $categId   = $request->categ_id ?: null;
            $convenio  = $request->p_convenio ?: null;
            $regimen   = $request->p_regimen ?: null;

            $empleados = DB::select(
                "CALL SP_LISTA_EMPLEADOS_BAJA(?, ?, ?, ?)",
                [$areaId, $categId, $convenio, $regimen]
            );

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
    try {
        // Datos del empleado
        $empleadoData = DB::select("CALL SP_VER_LEGAJO(?)", [$legajo]);
        
        // Datos de los familiares
        $familiares = DB::select("CALL SP_LISTAR_FAMILIARES(?)", [$legajo]);

        if (!empty($empleadoData)) {
            return response()->json([
                'success' => true,
                'data' => $empleadoData[0],
                'familiares' => $familiares // Enviamos el array de hijos aquí
            ]);
        }

        return response()->json(['success' => false, 'mensaje' => 'No se encontró el legajo']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 500);
    }
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

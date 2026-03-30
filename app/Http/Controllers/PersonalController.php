<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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

    public function solicitudes()
    {
        return view('personal.solicitudes');
    }

    public function miLegajo()
    {
        return view('personal.miLegajo');
    }

    public function administrarUsuarios()
    {
        return view('personal.administrarUsuarios');
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
        $obras = DB::select('CALL SP_LISTA_OBRA_SOCIAL()');

        $data = collect($obras)->map(function ($o) {
            return [
                'id' => $o->id,
                'text' => $o->nombre,
                'codigo' => $o->codigo
            ];
        });

        return response()->json($data);
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
            $uti   = $request->p_uti ?: null;
            $noche   = $request->p_noche ?: null;

            $empleados = DB::select(
                "CALL SP_LISTA_EMPLEADOS(?, ?, ?, ?, ?, ?)",
                [$areaId, $categId, $convenio, $regimen, $uti, $noche]
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

    public function listarCargaMasiva(Request $request)
    {
        try {
            $areaId    = $request->area_id ?: null;
            $categId   = $request->categ_id ?: null;
            $convenio  = $request->p_convenio ?: null;
            $regimen   = $request->p_regimen ?: null;
            $uti   = $request->p_uti ?: null;
            $noche   = $request->p_noche ?: null;

            $empleados = DB::select(
                "CALL SP_LISTA_EMPLEADOS_CARGA_MASIVA(?, ?, ?, ?, ?, ?)",
                [$areaId, $categId, $convenio, $regimen, $uti, $noche]
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
            $empleadoData = DB::select("CALL SP_VER_LEGAJO(?)", [$legajo]);
            DB::statement("SET @dummy = 1"); // 🔥 importante para liberar resultados

            $familiares = DB::select("CALL SP_LISTAR_FAMILIARES(?)", [$legajo]);
            DB::statement("SET @dummy2 = 1");

            if (empty($empleadoData)) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se encontró el legajo'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $empleadoData[0],
                'familiares' => $familiares // 🔥 ACÁ ESTÁ LA CLAVE
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
            $empleadoData = DB::select("CALL SP_VER_LEGAJO(?)", [$legajo]);
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

    public function registrarSolicitud(Request $request)
    {
        try {

            DB::statement(
                "CALL SP_REGISTRAR_SOLICITUD(?, ?, ?, ?, ?, @p_mensaje)",
                [
                    $request->tipoSolicitud,
                    $request->legajo,
                    $request->registrante,
                    $request->observ,
                    $request->monto
                ]
            );

            $mensaje = DB::selectOne("SELECT @p_mensaje as mensaje");

            return response()->json([
                'ok' => true,
                'mensaje' => $mensaje->mensaje
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al registrar solicitud',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function listarSolicitudes(Request $request)
    {
        try {
            $fechaDesde    = $request->fechaDesde ?: null;
            $fechaHasta    = $request->fechaHasta ?: null;
            $areaId    = $request->area_id ?: null;
            $estado   = $request->estado ?: null;
            $legajo   = Auth::user()->legajo ?: null;
            $rol   = Auth::user()->rol ?: null;

            $solicitudes = DB::select(
                "CALL SP_LISTA_SOLICITUDES(?, ?, ?, ?, ?, ?)",
                [$fechaDesde, $fechaHasta, $estado, $areaId, $legajo, $rol]
            );

            return response()->json([
                'data' => $solicitudes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function aprobarSolicitud(Request $request)
    {
        try {
            $request->validate([
                'idSolicitud' => 'required|integer'
            ]);

            $idSolicitud = $request->idSolicitud;

            DB::statement("CALL SP_APROBAR_SOLICITUD(?, @p_msj)", [$idSolicitud]);
            $mensaje = DB::selectOne("SELECT @p_msj as mensaje");

            return response()->json([
                'ok' => true,
                'mensaje' => $mensaje->mensaje
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al aprobar solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function rechazarSolicitud(Request $request)
    {
        try {
            $request->validate([
                'idSolicitud' => 'required|integer'
            ]);

            $idSolicitud = $request->idSolicitud;
            $idUser = Auth::user()->id;

            DB::statement("CALL SP_RECHAZAR_SOLICITUD(?, ?, @p_msj)", [$idSolicitud, $idUser]);

            $mensaje = DB::selectOne("SELECT @p_msj as mensaje");

            return response()->json([
                'ok' => true,
                'mensaje' => $mensaje->mensaje
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al rechazar solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function anularSolicitud(Request $request)
    {
        try {
            $request->validate([
                'idSolicitud' => 'required|integer'
            ]);

            $idSolicitud = $request->idSolicitud;

            DB::statement("CALL SP_ANULAR_SOLICITUD(?, @p_msj)", [$idSolicitud]);

            $mensaje = DB::selectOne("SELECT @p_msj as mensaje");

            return response()->json([
                'ok' => true,
                'mensaje' => $mensaje->mensaje
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al anular solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verMiLegajo()
    {
        try {
            $legajo = Auth::user()->legajo;

            if (!$legajo) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El usuario no tiene un legajo asignado'
                ], 400);
            }

            $empleadoData = DB::select("CALL SP_VER_LEGAJO(?)", [$legajo]);
            DB::statement("SET @dummy = 1");

            $familiares = DB::select("CALL SP_LISTAR_FAMILIARES(?)", [$legajo]);
            DB::statement("SET @dummy2 = 1");

            if (empty($empleadoData)) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se encontró el legajo'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $empleadoData[0],
                'familiares' => $familiares
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener el legajo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function actualizarMiLegajo(Request $request)
    {
        try {

            $legajo = Auth::user()->legajo;

            DB::statement("CALL SP_ACTUALIZAR_MI_LEGAJO(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", [
                $legajo,
                $request->correo,
                $request->telefono,
                $request->domicilio,
                $request->localidad,
                $request->estadoCivil,
                $request->genero,
                $request->personaEmerg1,
                $request->telefEmerg1,
                $request->parentesco1,
                $request->personaEmerg2,
                $request->telefEmerg2,
                $request->parentesco2,
                $request->padre,
                $request->madre
            ]);

            if ($request->hijosEdit) {

                foreach ($request->hijosEdit as $hijo) {

                    DB::statement("CALL SP_INSERTAR_FAMILIAR(?,?,?)", [
                        $legajo,
                        $hijo['nombre'],
                        'HIJO/A'
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Legajo actualizado correctamente'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function actualizarLegajoColaborador(Request $request, $legajo)
    {
        try {
            DB::statement("CALL SP_ACTUALIZAR_LEGAJO_COLABORADOR(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", [
                $legajo,
                $request->correo,
                $request->telefono,
                $request->domicilio,
                $request->localidad,
                $request->estadoCivil,
                $request->genero,
                $request->obra_social_id,
                $request->descrip_titulo,
                $request->mat_prof,
                $request->personaEmerg1,
                $request->telefEmerg1,
                $request->parentesco1,
                $request->personaEmerg2,
                $request->telefEmerg2,
                $request->parentesco2,
                $request->padre,
                $request->madre,
                $request->tipo_contrato,
                $request->area_id,
                $request->servicio_id,
                $request->convenio,
                $request->categoria_id,
                $request->rol_interno_id,
                $request->regimen_horas,
                $request->horas_diarias,
                $request->es_coordinador,
                $request->es_afiliado,
                $request->uti,
                $request->noche
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Legajo actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al actualizar el legajo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listarUsuarios(Request $request)
    {
        $area = $request->input('area');     // puede venir null
        $estado = $request->input('estado'); // puede venir null

        $usuarios = DB::select('CALL SP_LISTA_USUARIOS(?, ?)', [
            $area,
            $estado
        ]);

        return response()->json([
            'data' => $usuarios
        ]);
    }

    public function crearUsuario(Request $request)
    {
        try {

            $name = $request->name;
            $legajo = $request->legajo;
            $username = $request->username ?: $legajo;
            $password = bcrypt($request->password);
            $rol = $request->rol;
            $area = $request->area;

            DB::statement('CALL SP_REGISTRAR_USUARIO(?, ?, ?, ?, ?, ?, @p_mensaje)', [
                $name,
                $legajo,
                $username,
                $password,
                $rol,
                $area
            ]);

            $resultado = DB::select('SELECT @p_mensaje as mensaje');
            $mensaje = $resultado[0]->mensaje ?? 'ERROR';

            if ($mensaje !== 'OK') {
                return response()->json([
                    'ok' => false,
                    'mensaje' => $mensaje
                ], 400);
            }

            return response()->json([
                'ok' => true,
                'mensaje' => 'Usuario creado correctamente'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'ok' => false,
                'mensaje' => 'Error en el servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function obtener($legajo)
    {
        $usuario = DB::table('users')
            ->where('legajo', $legajo)
            ->first();

        return response()->json($usuario);
    }

    public function actualizar(Request $request)
    {
        try {

            $data = [
                'name' => $request->name,
                'username' => $request->username ?: $request->legajo,
                'rol' => $request->rol,
                'area_id' => $request->area
            ];

            // solo actualiza password si viene
            if ($request->password) {
                $data['password'] = bcrypt($request->password);
            }

            DB::table('users')
                ->where('legajo', $request->legajo)
                ->update($data);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Usuario actualizado correctamente'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al actualizar'
            ], 500);
        }
    }

    public function baja(Request $request)
    {
        try {

            $legajo = $request->legajo;

            DB::statement('CALL SP_BAJA_USUARIO(?, @p_mensaje)', [$legajo]);

            $resultado = DB::select('SELECT @p_mensaje as mensaje');
            $mensaje = $resultado[0]->mensaje ?? 'ERROR';

            if ($mensaje !== 'OK') {
                return response()->json([
                    'ok' => false,
                    'mensaje' => $mensaje
                ], 400);
            }

            return response()->json([
                'ok' => true,
                'mensaje' => 'Usuario dado de baja correctamente'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al dar de baja'
            ], 500);
        }
    }
}

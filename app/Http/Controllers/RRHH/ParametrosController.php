<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ParametrosController extends Controller
{
    public function configParametrosGenerales()
    {
        return view('ajustes.parametrizacion');
    }

    public function configAreasView()
    {
        return view('ajustes.configAreas');
    }

    public function configCategoriasView()
    {
        return view('ajustes.configCategorias');
    }

    public function configRegimenesView()
    {
        return view('ajustes.configRegimenes');
    }

    public function listarServiciosColab()
    {
        try {
            $servicios = DB::select('CALL SP_LISTAR_SERVICIOS_COLAB()');

            return response()->json($servicios);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener servicios',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function crearArea(Request $request)
    {
        try {

            DB::statement("SET @codigo = 0");
            DB::statement("SET @mensaje = ''");

            DB::statement(
                "CALL SP_CREAR_NUEVA_AREA(?,?, @codigo, @mensaje)",
                [$request->nombreArea, Auth::user()->id]
            );

            $resultado = DB::selectOne(
                "SELECT @codigo AS codigo, @mensaje AS mensaje"
            );

            return response()->json([
                'success' => true,
                'codigo' => $resultado->codigo,
                'message' => $resultado->mensaje
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'codigo' => -1,
                'message' => 'Ocurrió un error al procesar la solicitud'
            ], 500);
        }
    }

    public function verDetalleArea(Request $request)
    {

        try {
            $idArea = $request->idArea;

            $data = DB::select('CALL SP_VER_AREA(?)', [$idArea]);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function editarArea(Request $request)
    {
        try {
            DB::statement('CALL SP_MODIFICAR_AREA(?, ?, ?)', [
                $request->idArea,
                $request->nombre,
                Auth::user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Área actualizada correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminarArea($id)
    {
        try {
            DB::statement("CALL SP_ELIMINAR_AREA(?,?)", [$id, Auth::user()->id]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Área eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al eliminar el área'
            ], 500);
        }
    }

    public function crearCategoria(Request $request)
    {
        try {

            DB::statement("SET @codigo = 0");
            DB::statement("SET @mensaje = ''");

            DB::statement(
                "CALL SP_CREAR_NUEVA_CATEG(?,?, @codigo, @mensaje)",
                [$request->nombreCateg, Auth::user()->id]
            );

            $resultado = DB::selectOne(
                "SELECT @codigo AS codigo, @mensaje AS mensaje"
            );

            return response()->json([
                'success' => true,
                'codigo' => $resultado->codigo,
                'message' => $resultado->mensaje
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'codigo' => -1,
                'message' => 'Ocurrió un error al procesar la solicitud'
            ], 500);
        }
    }

    public function verDetalleCateg(Request $request)
    {

        try {
            $idCateg = $request->idCateg;

            $data = DB::select('CALL SP_VER_CATEGORIA(?)', [$idCateg]);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function editarCateg(Request $request)
    {
        try {
            DB::statement('CALL SP_MODIFICAR_CATEG(?, ?)', [
                $request->idCateg,
                $request->nombre
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoría actualizada correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminarCateg($id)
    {
        try {
            DB::statement("CALL SP_ELIMINAR_CATEGORIA(?,?)", [$id, Auth::user()->id]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Categoría eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al eliminar la categoría'
            ], 500);
        }
    }

    public function verDetalleServicio(Request $request)
    {

        try {
            $idServ = $request->idServ;

            $data = DB::select('CALL SP_VER_SERVICIOS(?)', [$idServ]);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function editarServicio(Request $request)
    {
        try {
            DB::statement('CALL SP_MODIFICAR_SERVICIO(?, ?, ?)', [
                $request->idServ,
                $request->nombre,
                $request->idArea,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Servicio actualizado correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminarServicio($id)
    {
        try {
            DB::statement("CALL SP_ELIMINAR_SERVICIO(?)", [$id]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Servicio eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al eliminar el servicio'
            ], 500);
        }
    }

    public function crearServicio(Request $request)
    {
        try {

            DB::statement("SET @codigo = 0");
            DB::statement("SET @mensaje = ''");

            DB::statement(
                "CALL SP_CREAR_NUEVO_SERVICIO(?,?,?, @codigo, @mensaje)",
                [$request->servicio, $request->area, Auth::user()->id]
            );

            $resultado = DB::selectOne(
                "SELECT @codigo AS codigo, @mensaje AS mensaje"
            );

            return response()->json([
                'success' => true,
                'codigo' => $resultado->codigo,
                'message' => $resultado->mensaje
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'codigo' => -1,
                'message' => 'Ocurrió un error al procesar la solicitud'
            ], 500);
        }
    }

    public function listarRegimenesColab()
    {
        try {
            $servicios = DB::select('CALL SP_LISTAR_REGIMENES()');

            return response()->json($servicios);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener regimenes de colaboradores',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function crearRegimen(Request $request)
    {
        $request->validate([
            'regimen' => ['required', 'numeric', 'min:0'],
            'horasDiarias' => ['required', 'numeric', 'min:0'],
        ]);

        DB::statement(
            "CALL SP_CREAR_REGIMEN(?,?,?)",
            [
                $request->regimen,
                $request->horasDiarias,
                Auth::id()
            ]
        );

        return response()->json([
            'success' => true
        ]);
    }

    public function activarRegimen(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer'],
            'activo' => ['required', 'boolean'],
        ]);

        DB::statement(
            "CALL SP_ACTIVACION_REGIMEN(?,?,?)",
            [
                $request->id,
                $request->activo,
                Auth::id()
            ]
        );

        return response()->json([
            'success' => true
        ]);
    }

    public function editarRegimen(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer'],
            'regimen' => ['required', 'numeric', 'min:0'],
        ]);

        DB::statement(
            "CALL SP_EDICION_REGIMEN_REGIMEN(?,?,?)",
            [
                $request->id,
                $request->regimen,
                Auth::id()
            ]
        );

        return response()->json([
            'success' => true
        ]);
    }

    public function editarHorasRegimen(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer'],
            'horasDiarias' => ['required', 'numeric', 'min:0'],
        ]);

        DB::statement(
            "CALL SP_EDICION_REGIMEN_HORAS(?,?,?)",
            [
                $request->id,
                $request->horasDiarias,
                Auth::id()
            ]
        );

        return response()->json([
            'success' => true
        ]);
    }

    public function listarTurnosxArea($id)
    {
        try {
            $areas = DB::select(
                'CALL SP_LISTAR_TURNOS_AREAS(?)',
                [$id]
            );

            return response()->json($areas);
        } catch (\Throwable $e) {

            return response()->json([
                'error' => true,
                'mensaje' => 'Error al cargar areas',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function crearTurno(Request $request)
    {
        $datos = $request->validate([

            'nombre' => [
                'required',
                'string',
                'max:50'
            ],

            'codigo' => [
                'required',
                'string',
                'max:10'
            ],

            'horaInicio' => [
                'required',
                'date_format:H:i'
            ],

            'horaFin' => [
                'required',
                'date_format:H:i'
            ],

            'toleranciaIngreso' => [
                'required',
                'integer',
                'min:0'
            ],

            'idArea' => [
                'required',
                'integer'
            ]

        ]);


        DB::statement(
            'CALL SP_CREAR_TURNO(?,?,?,?,?,?,?)',
            [
                $datos['nombre'],
                $datos['codigo'],
                $datos['horaInicio'],
                $datos['horaFin'],
                $datos['toleranciaIngreso'],
                $datos['idArea'],
                Auth::id()
            ]
        );


        return response()->json([
            'success' => true,
            'message' => 'Turno creado correctamente.'
        ]);
    }

    public function actualizarCampoTurno(Request $request, $id)
    {
        $request->validate([

            'campo' => [
                'required',
                'in:nombre,hora_inicio,hora_fin,tolerancia_ingreso,horas_reales,horas_computadas,activo'
            ]

        ]);


        $campo = $request->campo;


        $configuracion = [

            'nombre' => [
                'sp' => 'SP_EDITAR_TURNO_NOMBRE',
                'rules' => [
                    'required',
                    'string',
                    'max:50'
                ]
            ],

            'hora_inicio' => [
                'sp' => 'SP_EDITAR_TURNO_DESDE',
                'rules' => [
                    'required',
                    'date_format:H:i'
                ]
            ],

            'hora_fin' => [
                'sp' => 'SP_EDITAR_TURNO_HASTA',
                'rules' => [
                    'required',
                    'date_format:H:i'
                ]
            ],

            'tolerancia_ingreso' => [
                'sp' => 'SP_EDITAR_TURNO_TOLERANCIA',
                'rules' => [
                    'required',
                    'integer',
                    'min:0'
                ]
            ],

            'horas_reales' => [
                'sp' => 'SP_EDITAR_TURNO_HORAS_REALES',
                'rules' => [
                    'required',
                    'integer',
                    'min:0'
                ]
            ],

            'horas_computadas' => [
                'sp' => 'SP_EDITAR_TURNO_HORAS_COMPUTADAS',
                'rules' => [
                    'required',
                    'integer',
                    'min:0'
                ]
            ],

            'activo' => [
                'sp' => 'SP_EDITAR_TURNO_ESTADO',
                'rules' => [
                    'required',
                    'integer',
                    'in:0,1'
                ]
            ]

        ];


        $config = $configuracion[$campo];


        $validacion = Validator::make(

            [
                'valor' => $request->valor
            ],

            [
                'valor' => $config['rules']
            ]

        )->validate();


        $existe = DB::table('turnos_areas')
            ->where('id', $id)
            ->exists();


        if (!$existe) {

            return response()->json([
                'message' => 'El turno seleccionado no existe.'
            ], 404);
        }


        DB::statement(
            "CALL {$config['sp']}(?,?,?)",
            [
                $id,
                $validacion['valor'],
                Auth::id()
            ]
        );


        return response()->json([
            'success' => true,
            'message' => 'Turno actualizado correctamente.'
        ]);
    }

    public function listarFuncionesAdicxArea($id)
    {
        try {
            $funciones = DB::select(
                'CALL SP_LISTAR_FUNCIONES_AREA(?)',
                [$id]
            );

            return response()->json($funciones);
        } catch (\Throwable $e) {

            return response()->json([
                'error' => true,
                'mensaje' => 'Error al cargar areas',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}

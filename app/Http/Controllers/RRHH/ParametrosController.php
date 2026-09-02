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

    public function actualizarCampoCategoria(Request $request, $id)
    {
        $request->validate([
            'campo' => 'required|string'
        ]);


        $campo = $request->campo;
        $valor = $request->valor;


        switch ($campo) {

            case 'nombre':

                $request->validate([
                    'valor' => 'required|string|max:200'
                ]);

                $sp = 'SP_EDITAR_CATEGORIA_NOMBRE';

                break;


            case 'estado':

                $request->validate([
                    'valor' => 'required|integer|in:0,1'
                ]);

                $sp = 'SP_EDITAR_CATEGORIA_ESTADO';

                break;


            default:

                return response()->json([
                    'message' => 'Campo no válido.'
                ], 422);
        }


        DB::statement(
            "CALL {$sp}(?,?,?)",
            [
                $id,
                $valor,
                Auth::id()
            ]
        );


        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada correctamente.'
        ]);
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

    public function actualizarCampoServicio(Request $request, $id)
    {
        $request->validate([
            'campo' => 'required|string',
            'valor' => 'nullable'
        ]);


        $campo = $request->campo;
        $valor = $request->valor;


        switch ($campo) {

            case 'nombre':

                $request->validate([
                    'valor' => 'required|string|max:250'
                ]);

                $sp = 'SP_EDITAR_SERVICIO_NOMBRE';

                break;


            case 'estado':

                $request->validate([
                    'valor' => 'required|integer|in:0,1'
                ]);

                $sp = 'SP_EDITAR_SERVICIO_ESTADO';

                break;


            default:

                return response()->json([
                    'message' => 'Campo no válido.'
                ], 422);
        }


        $existe = DB::table('servicios')
            ->where('id_servicios', $id)
            ->exists();


        if (!$existe) {

            return response()->json([
                'message' => 'El servicio seleccionado no existe.'
            ], 404);
        }


        DB::statement(
            "CALL {$sp}(?,?,?)",
            [
                $id,
                $valor,
                Auth::id()
            ]
        );


        return response()->json([
            'success' => true,
            'message' => 'Servicio actualizado correctamente.'
        ]);
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
        try {

            $request->validate([
                'campo' => 'required|string',
                'valor' => 'nullable'
            ]);


            $campo = $request->campo;
            $valor = $request->valor;


            switch ($campo) {

                case 'nombre':

                    $request->validate([
                        'valor' => 'required|string|max:50'
                    ]);

                    $sp = 'SP_EDITAR_TURNO_NOMBRE';

                    break;


                case 'hora_inicio':

                    $request->validate([
                        'valor' => 'required|date_format:H:i'
                    ]);

                    $sp = 'SP_EDITAR_TURNO_DESDE';

                    break;


                case 'hora_fin':

                    $request->validate([
                        'valor' => 'required|date_format:H:i'
                    ]);

                    $sp = 'SP_EDITAR_TURNO_HASTA';

                    break;


                case 'tolerancia_ingreso':

                    $request->validate([
                        'valor' => 'required|integer|min:0'
                    ]);

                    $sp = 'SP_EDITAR_TURNO_TOLERANCIA';

                    break;


                case 'horas_reales':

                    $request->validate([
                        'valor' => 'required|integer|min:0'
                    ]);

                    $sp = 'SP_EDITAR_TURNO_HORAS_REALES';

                    break;


                case 'horas_computadas':

                    $request->validate([
                        'valor' => 'required|integer|min:0'
                    ]);

                    $sp = 'SP_EDITAR_TURNO_HORAS_COMPUTADAS';

                    break;


                case 'activo':

                    $request->validate([
                        'valor' => 'required|integer|in:0,1'
                    ]);

                    $sp = 'SP_EDITAR_TURNO_ESTADO';

                    break;


                default:

                    return response()->json([
                        'message' => 'Campo no válido.'
                    ], 422);
            }


            DB::statement(
                "CALL {$sp}(?,?,?)",
                [
                    $id,
                    $valor,
                    Auth::id()
                ]
            );


            return response()->json([
                'success' => true,
                'message' => 'Turno actualizado correctamente.'
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'No se pudo actualizar el turno.',
                'detalle' => $e->getMessage()
            ], 500);
        }
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

    public function crearFuncionAdicional(Request $request)
    {
        $datos = $request->validate([

            'nombreFuncion' => [
                'required',
                'string',
                'max:250'
            ],

            'codigoFuncion' => [
                'max:50'
            ],

            'idNovedad' => [],

            'area' => [
                'required',
                'integer'
            ]

        ]);


        DB::statement(
            'CALL SP_FUNCION_ADICIONAL_CREAR(?,?,?,?,?)',
            [
                $datos['nombreFuncion'],
                $datos['codigoFuncion'],
                $datos['idNovedad'],
                $datos['area'],
                Auth::id()
            ]
        );


        return response()->json([
            'success' => true,
            'message' => 'Función adicional creada correctamente.'
        ]);
    }

    public function actualizarCampoFuncionAdicional(Request $request, $id)
    {
        $request->validate([
            'campo' => 'required|string',
            'idArea' => 'required|integer'
        ]);


        $campo = $request->campo;
        $valor = $request->valor;
        $idArea = $request->idArea;


        switch ($campo) {

            case 'nombre':

                $request->validate([
                    'valor' => 'required|string|max:250'
                ]);

                $sp = 'SP_FUNCION_ADICIONAL_EDITAR_NOMBRE';

                break;


            case 'marca':

                $request->validate([
                    'valor' => 'nullable|string|max:250'
                ]);

                $sp = 'SP_FUNCION_ADICIONAL_EDITAR_MARCA';

                break;


            case 'id_novedad':

                $request->validate([
                    'valor' => 'nullable|integer'
                ]);

                $sp = 'SP_FUNCION_ADICIONAL_EDITAR_NOVEDAD';

                break;


            case 'estado':

                $request->validate([
                    'valor' => 'required|integer|in:0,1'
                ]);

                $sp = 'SP_FUNCION_ADICIONAL_EDITAR_ESTADO';

                break;


            default:

                return response()->json([
                    'message' => 'Campo no válido.'
                ], 422);
        }


        /*
     * El option value="" llega como string vacío.
     * Para MySQL queremos NULL.
     */
        if ($campo === 'id_novedad' && $valor === '') {
            $valor = null;
        }


        DB::statement(
            "CALL {$sp}(?,?,?,?)",
            [
                $id,
                $valor,
                $idArea,
                Auth::id()
            ]
        );


        return response()->json([
            'success' => true,
            'message' => 'Función adicional actualizada correctamente.'
        ]);
    }
}

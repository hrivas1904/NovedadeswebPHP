<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use Mithun\PhpZkteco\Libs\ZKTeco;
use Illuminate\Http\Request;
use App\Services\ZktecoAttendanceService;
use Illuminate\Support\Facades\DB;

class ZktecoController extends Controller
{
    public function index()
    {
        return view('zkteco.index');
    }

    public function marcaciones(
        Request $request,
        ZktecoAttendanceService $zktecoService
    ) {
        set_time_limit(120);

        try {

            $inicio = microtime(true);

            /*
        |--------------------------------------------------------------------------
        | 1. OBTENER MARCACIONES DEL RELOJ
        |--------------------------------------------------------------------------
        */

            $marcaciones = $zktecoService->obtenerMarcaciones();


            /*
        |--------------------------------------------------------------------------
        | 2. FILTROS
        |--------------------------------------------------------------------------
        */

            $desde = $request->input('desde');
            $hasta = $request->input('hasta');

            $marcaciones = collect($marcaciones)
                ->filter(function ($marcacion) use ($desde, $hasta) {

                    if (empty($marcacion['record_time'])) {
                        return false;
                    }

                    /*
                 * record_time:
                 * 2026-09-29 13:45:10
                 *
                 * Nos quedamos con:
                 * 2026-09-29
                 */
                    $fecha = substr(
                        $marcacion['record_time'],
                        0,
                        10
                    );

                    if ($desde && $fecha < $desde) {
                        return false;
                    }

                    if ($hasta && $fecha > $hasta) {
                        return false;
                    }

                    return true;
                })
                ->values();


            /*
        |--------------------------------------------------------------------------
        | 3. OBTENER IDs DE MÉDICOS
        |--------------------------------------------------------------------------
        |
        | ZKTeco user_id = medicos.id
        |
        */

            $idsMedicos = $marcaciones
                ->pluck('user_id')
                ->filter()
                ->unique()
                ->values()
                ->all();


            /*
        |--------------------------------------------------------------------------
        | 4. CONSULTAR MÉDICOS
        |--------------------------------------------------------------------------
        |
        | Una única consulta SQL.
        |
        */

            $medicos = DB::table('medicos')
                ->whereIn('id', $idsMedicos)
                ->get()
                ->keyBy(function ($medico) {
                    return (string) $medico->id;
                });


            /*
        |--------------------------------------------------------------------------
        | 5. CRUZAR MARCACIONES CON MÉDICOS
        |--------------------------------------------------------------------------
        */

            $marcaciones = $marcaciones
                ->map(function ($marcacion) use ($medicos) {

                    $userId = (string) $marcacion['user_id'];

                    $medico = $medicos->get($userId);

                    if ($medico) {

                        /*
                     * AJUSTAR según los nombres reales
                     * de las columnas de tu tabla medicos.
                     */
                        $nombreCompleto = trim(
                            ($medico->apellido ?? '') . ' ' .
                                ($medico->nombre ?? '')
                        );

                        $marcacion['medico_id'] = $medico->id;

                        $marcacion['medico'] =
                            $nombreCompleto !== ''
                            ? $nombreCompleto
                            : 'Médico #' . $medico->id;
                    } else {

                        $marcacion['medico_id'] = null;
                        $marcacion['medico'] = 'Sin identificar';
                    }

                    return $marcacion;
                });


            /*
        |--------------------------------------------------------------------------
        | 6. ORDENAR MÁS RECIENTES PRIMERO
        |--------------------------------------------------------------------------
        */

            $marcaciones = $marcaciones
                ->sortByDesc('record_time')
                ->values();


            /*
        |--------------------------------------------------------------------------
        | 7. RESPUESTA
        |--------------------------------------------------------------------------
        */

            $tiempo = round(
                microtime(true) - $inicio,
                2
            );

            return response()->json([
                'success' => true,

                'filtros' => [
                    'desde' => $desde,
                    'hasta' => $hasta,
                ],

                'cantidad' => $marcaciones->count(),

                'tiempo' => $tiempo,

                'data' => $marcaciones->all()
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error consultando marcaciones.',
                'detalle' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function probarConexion()
    {
        $zk = new ZKTeco(
            host: config('zkteco.ip'),
            port: config('zkteco.port'),
            shouldPing: false,
            timeout: config('zkteco.timeout'),
            password: config('zkteco.password'),
            protocol: config('zkteco.protocol')
        );

        $conectado = false;

        try {

            $inicio = microtime(true);

            $conectado = $zk->connect();

            $tiempo = round(
                microtime(true) - $inicio,
                2
            );

            if (!$conectado) {

                return response()->json([
                    'success' => false,
                    'message' => 'El reloj no respondió a la conexión TCP.',
                    'tiempo' => $tiempo
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Conexión TCP con ZKTeco realizada correctamente.',
                'tiempo' => $tiempo
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al conectar con el reloj ZKTeco.',
                'detalle' => $e->getMessage()
            ], 500);
        } finally {

            if ($conectado) {

                try {
                    $zk->disconnect();
                } catch (\Throwable $e) {
                    //
                }
            }
        }
    }

    public function probarLectura()
    {
        $zk = new ZKTeco(
            host: config('zkteco.ip'),
            port: config('zkteco.port'),
            shouldPing: false,
            timeout: config('zkteco.timeout'),
            password: config('zkteco.password'),
            protocol: config('zkteco.protocol')
        );

        $conectado = false;

        try {

            $conectado = $zk->connect();

            if (!$conectado) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo conectar con el reloj.'
                ], 500);
            }

            $hora = $zk->getTime();

            return response()->json([
                'success' => true,
                'message' => 'Lectura realizada correctamente.',
                'hora_reloj' => $hora
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al leer información del reloj.',
                'detalle' => $e->getMessage()
            ], 500);
        } finally {

            if ($conectado) {
                try {
                    $zk->disconnect();
                } catch (\Throwable $e) {
                    //
                }
            }
        }
    }

    public function diagnostico()
    {
        $zk = new ZKTeco(
            host: config('zkteco.ip'),
            port: config('zkteco.port'),
            shouldPing: false,
            timeout: config('zkteco.timeout'),
            password: config('zkteco.password'),
            protocol: config('zkteco.protocol')
        );

        $conectado = false;

        try {

            $conectado = $zk->connect();

            if (!$conectado) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo conectar con el reloj.'
                ], 500);
            }

            $memoria = $zk->getMemoryInfo();

            return response()->json([
                'success' => true,
                'hora' => $zk->getTime(),
                'modelo' => $zk->deviceName(),
                'serial' => $zk->serialNumber(),
                'version' => $zk->version(),
                'plataforma' => $zk->platform(),
                'memoria' => $memoria,
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar diagnóstico.',
                'detalle' => $e->getMessage()
            ], 500);
        } finally {

            if ($conectado) {
                try {
                    $zk->disconnect();
                } catch (\Throwable $e) {
                    //
                }
            }
        }
    }

    public function probarUsuarios()
    {
        $zk = new ZKTeco(
            host: config('zkteco.ip'),
            port: config('zkteco.port'),
            shouldPing: false,
            timeout: config('zkteco.timeout'),
            password: config('zkteco.password'),
            protocol: config('zkteco.protocol')
        );

        $conectado = false;

        try {

            $conectado = $zk->connect();

            if (!$conectado) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo conectar con el reloj ZKTeco.',
                    'data' => []
                ], 500);
            }

            $inicio = microtime(true);

            // SOLO LECTURA
            $usuarios = $zk->getUsers();

            $tiempo = round(
                microtime(true) - $inicio,
                2
            );

            /*
        |--------------------------------------------------------------------------
        | Normalizar respuesta
        |--------------------------------------------------------------------------
        |
        | No devolvemos password.
        |
        */

            $usuariosLimpios = [];

            foreach ($usuarios as $usuario) {

                $usuariosLimpios[] = [
                    'uid' => $usuario['uid'] ?? null,
                    'user_id' => $usuario['user_id'] ?? null,
                    'name' => $usuario['name'] ?? null,
                    'role' => $usuario['role'] ?? null,
                    'card_no' => $usuario['card_no'] ?? null,
                    'device_ip' => $usuario['device_ip'] ?? null,
                ];
            }

            return response()->json([
                'success' => true,
                'cantidad' => count($usuariosLimpios),
                'tiempo' => $tiempo,
                'data' => $usuariosLimpios
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error consultando usuarios del reloj.',
                'detalle' => $e->getMessage(),
                'data' => []
            ], 500);
        } finally {

            if ($conectado) {

                try {
                    $zk->disconnect();
                } catch (\Throwable $e) {
                    //
                }
            }
        }
    }
}

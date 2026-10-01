<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use Mithun\PhpZkteco\Libs\ZKTeco;
use Illuminate\Http\Request;
use App\Services\ZktecoAttendanceService;
use App\Services\ZktecoSyncService;
use Illuminate\Support\Facades\DB;

class ZktecoController extends Controller
{
    public function index()
    {
        return view('zkteco.index');
    }

    public function marcaciones(Request $request)
    {
        try {

            $query = DB::table('zkteco_marcaciones as zm')
                ->join(
                    'zkteco_dispositivos as zd',
                    'zd.id',
                    '=',
                    'zm.dispositivo_id'
                )
                ->leftJoin(
                    'medicos as m',
                    'm.id',
                    '=',
                    'zm.user_id'
                )
                ->select([
                    'zm.id',
                    'zm.uid',
                    'zm.user_id',
                    'zm.record_time',
                    'zm.state',
                    'zm.type',
                    'zm.dispositivo_id',

                    'zd.nombre as dispositivo',
                    'zd.tipo_entidad',

                    'm.nombre as medico'
                ]);


            // FILTRO TIPO ENTIDAD
            if ($request->filled('tipo_entidad')) {

                $query->where(
                    'zd.tipo_entidad',
                    $request->tipo_entidad
                );
            }


            // FILTRO DESDE
            if ($request->filled('desde')) {

                $query->whereDate(
                    'zm.record_time',
                    '>=',
                    $request->desde
                );
            }


            // FILTRO HASTA
            if ($request->filled('hasta')) {

                $query->whereDate(
                    'zm.record_time',
                    '<=',
                    $request->hasta
                );
            }


            $marcaciones = $query
                ->orderByDesc('zm.record_time')
                ->get();


            $marcaciones->transform(function ($marcacion) {

                $marcacion->medico =
                    $marcacion->medico
                    ?? 'Sin identificar';

                return $marcacion;
            });


            return response()->json([
                'success' => true,
                'cantidad' => $marcaciones->count(),
                'data' => $marcaciones
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

    public function sincronizar(
        ZktecoSyncService $syncService
    ) {
        try {

            $dispositivos = DB::table('zkteco_dispositivos')
                ->where('activo', 1)
                ->get();

            $resultado = [];

            foreach ($dispositivos as $dispositivo) {

                $resultado[] = [
                    'dispositivo' => $dispositivo->nombre,
                    'resultado' => $syncService
                        ->sincronizar($dispositivo)
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error sincronizando relojes ZKTeco.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}

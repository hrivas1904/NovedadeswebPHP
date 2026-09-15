<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use Mithun\PhpZkteco\Libs\ZKTeco;

class ZktecoController extends Controller
{
    public function index()
    {
        return view('zkteco.index');
    }

    public function marcaciones()
    {
        set_time_limit(120);

        if (!defined('ZKTECO_DEBUG')) {
            define('ZKTECO_DEBUG', true);
        }

        if (!defined('ZKTECO_DEBUG_LOG')) {
            define(
                'ZKTECO_DEBUG_LOG',
                storage_path('logs/zkteco_debug.log')
            );
        }

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
                    'message' => 'No se pudo conectar con el reloj.',
                    'data' => []
                ], 500);
            }

            $inicio = microtime(true);

            // SOLO LECTURA
            $marcaciones = $zk->getAttendances();

            $tiempo = round(
                microtime(true) - $inicio,
                2
            );

            $total = count($marcaciones);

            $ultimas = array_slice($marcaciones, -50);
            $ultimas = array_reverse($ultimas);

            return response()->json([
                'success' => true,
                'total_reloj' => $total,
                'cantidad_mostrada' => count($ultimas),
                'tiempo' => $tiempo,
                'timeout_config' => config('zkteco.timeout'),
                'timeout_instancia' => $zk->_timeout,
                'data' => $ultimas
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error consultando marcaciones.',
                'detalle' => $e->getMessage(),
                'data' => []
            ], 500);
        } finally {

            if ($conectado) {
                try {
                    $zk->disconnect();
                } catch (\Throwable $e) {
                }
            }
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
            timeout: 15,
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

            $inicio = microtime(true);

            // SOLO LECTURA
            $usuarios = $zk->getUsers();

            $tiempo = round(
                microtime(true) - $inicio,
                2
            );

            $primerUsuario = !empty($usuarios)
                ? reset($usuarios)
                : null;

            return response()->json([
                'success' => true,
                'cantidad' => count($usuarios),
                'tiempo' => $tiempo,

                // No mostramos datos personales.
                // Solo queremos conocer la estructura.
                'campos' => is_array($primerUsuario)
                    ? array_keys($primerUsuario)
                    : [],

                // Para conocer cómo viene indexado el array exterior
                'primeros_indices' => array_slice(
                    array_keys($usuarios),
                    0,
                    5
                )
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error consultando usuarios.',
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
}

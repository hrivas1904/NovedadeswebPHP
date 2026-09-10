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

            if (!$conectado) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo establecer conexión con el reloj ZKTeco.',
                    'data' => []
                ], 500);
            }

            // SOLO LECTURA
            $marcaciones = $zk->getAttendances();

            $tiempo = round(microtime(true) - $inicio, 2);

            $total = count($marcaciones);

            // Para la vista mostramos únicamente las últimas 50
            $ultimasMarcaciones = array_slice($marcaciones, -50);

            // Última marcación primero
            $ultimasMarcaciones = array_reverse($ultimasMarcaciones);

            return response()->json([
                'success' => true,
                'mock' => false,
                'message' => 'Marcaciones obtenidas correctamente.',
                'total_reloj' => $total,
                'cantidad_mostrada' => count($ultimasMarcaciones),
                'tiempo' => $tiempo,
                'data' => $ultimasMarcaciones
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'mock' => false,
                'message' => 'Error al consultar las marcaciones del reloj ZKTeco.',
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
            timeout: 10,
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

            $usuarios = $zk->getUsers();

            return response()->json([
                'success' => true,
                'cantidad' => count($usuarios),
                'tiempo' => round(microtime(true) - $inicio, 2),

                // Solo mostramos los nombres de los campos,
                // no datos personales del reloj.
                'campos' => !empty($usuarios)
                    ? array_keys($usuarios[0])
                    : []
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
                }
            }
        }
    }
}

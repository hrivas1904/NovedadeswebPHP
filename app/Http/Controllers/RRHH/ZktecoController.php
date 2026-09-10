<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use MehediJaman\LaravelZkteco\LaravelZkteco;

class ZktecoController extends Controller
{
    public function index()
    {
        return view('zkteco.index');
    }


    public function marcaciones()
    {
        try {

            // =========================================
            // MODO SIMULACIÓN
            // =========================================
            if (config('zkteco.mock')) {

                return response()->json([
                    'success' => true,
                    'mock' => true,
                    'message' => 'Datos simulados.',
                    'data' => [
                        [
                            'uid' => 101,
                            'id' => '1548',
                            'state' => 1,
                            'timestamp' => '2026-09-10 06:58:21',
                            'type' => 255,
                        ],
                        [
                            'uid' => 102,
                            'id' => '1620',
                            'state' => 1,
                            'timestamp' => '2026-09-10 07:02:48',
                            'type' => 255,
                        ],
                        [
                            'uid' => 103,
                            'id' => '1548',
                            'state' => 1,
                            'timestamp' => '2026-09-10 15:06:03',
                            'type' => 255,
                        ],
                        [
                            'uid' => 104,
                            'id' => '1775',
                            'state' => 4,
                            'timestamp' => '2026-09-10 15:11:44',
                            'type' => 255,
                        ],
                    ],
                ]);
            }


            // =========================================
            // RELOJ REAL
            // =========================================

            $zk = new LaravelZkteco(
                config('zkteco.ip'),
                config('zkteco.port')
            );

            $conectado = $zk->connect();

            if (!$conectado) {

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo establecer conexión con el reloj ZKTeco.',
                    'data' => [],
                ], 500);
            }


            $marcaciones = $zk->getAttendance();

            $zk->disconnect();


            return response()->json([
                'success' => true,
                'mock' => false,
                'message' => 'Marcaciones obtenidas correctamente.',
                'data' => $marcaciones,
            ]);


        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar el reloj ZKTeco.',
                'detalle' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }
}
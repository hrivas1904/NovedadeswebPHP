<?php

namespace App\Services;

use Exception;
use Mithun\PhpZkteco\Libs\ZKTeco;
use Mithun\PhpZkteco\Libs\Services\Util;

class ZktecoAttendanceService
{
    public function obtenerMarcaciones(): array
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
                throw new Exception(
                    'No se pudo establecer conexión con el reloj ZKTeco.'
                );
            }

            /*
             * IMPORTANTE:
             * getAttendances() limpia este buffer antes de cada intento.
             * Nosotros debemos hacer lo mismo.
             */
            $zk->_tcp_buffer = '';

            $zk->_section = __METHOD__;

            /*
             * Solicitud de ATTLOG.
             * Solo lectura.
             */
            $session = $zk->_command(
                Util::CMD_ATT_LOG_RRQ,
                '',
                Util::COMMAND_TYPE_DATA
            );

            if ($session === false) {
                throw new Exception(
                    'El reloj no aceptó la solicitud de marcaciones.'
                );
            }

            /*
             * Descargar el bloque completo.
             */
            $rawData = Util::recData($zk);

            if (empty($rawData)) {
                return [];
            }

            return $this->parsearMarcaciones(
                $rawData,
                $zk->_ip
            );
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


    private function parsearMarcaciones(
        string $rawData,
        string $deviceIp
    ): array {

        $marcaciones = [];

        /*
         * recData() conserva el header ZKTeco del PRIMER paquete
         * y elimina el header de los siguientes.
         *
         * Tenemos:
         *
         * 8 bytes  -> header protocolo ZKTeco
         * 4 bytes  -> tamaño total del ATTLOG
         * ------------------------------------
         * 12 bytes antes del primer registro
         *
         * Después:
         * 40 bytes por marcación.
         */
        if (strlen($rawData) <= 12) {
            return [];
        }

        $data = substr($rawData, 12);

        $cantidadRegistros = intdiv(
            strlen($data),
            40
        );

        for ($i = 0; $i < $cantidadRegistros; $i++) {

            $registro = substr(
                $data,
                $i * 40,
                40
            );

            if (strlen($registro) !== 40) {
                continue;
            }

            /*
             * BYTE 0-1
             * Número interno del usuario.
             */
            $uidData = unpack(
                'vuid',
                substr($registro, 0, 2)
            );

            $uid = (int) ($uidData['uid'] ?? 0);


            /*
             * BYTE 2-10
             * User ID: STRING de 9 bytes.
             *
             * MUY IMPORTANTE:
             * NO convertirlo a int.
             */
            $userId = substr(
                $registro,
                2,
                9
            );

            $userId = trim(
                $userId,
                "\0 \t\n\r"
            );


            /*
             * BYTE 26
             * Método de verificación:
             *
             * 0 Password
             * 1 Fingerprint
             * 2 Card
             */
            $state = ord(
                $registro[26]
            );


            /*
             * BYTE 27-30
             * Fecha/hora ZKTeco.
             */
            $timestampData = unpack(
                'Vtimestamp',
                substr($registro, 27, 4)
            );

            $rawTimestamp = (int) (
                $timestampData['timestamp'] ?? 0
            );


            /*
             * BYTE 31
             * Estado/tipo del evento.
             */
            $type = ord(
                $registro[31]
            );


            /*
             * Validaciones mínimas.
             */
            if (
                $userId === '' ||
                $rawTimestamp <= 0
            ) {
                continue;
            }


            $recordTime = Util::decodeTime(
                $rawTimestamp
            );


            $marcaciones[] = [
                'uid' => $uid,

                // Siempre string.
                'user_id' => $userId,

                'state' => $state,

                'record_time' => $recordTime,

                'type' => $type,

                'device_ip' => $deviceIp,
            ];
        }

        return $marcaciones;
    }
}

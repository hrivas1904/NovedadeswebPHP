<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ZktecoSyncService
{
    public function __construct(
        private ZktecoAttendanceService $attendanceService
    ) {}

    public function sincronizar(object $dispositivo): array
    {
        $marcaciones = $this->attendanceService
            ->obtenerMarcaciones($dispositivo);

        $insertadas = 0;

        foreach ($marcaciones as $marcacion) {

            $insertado = DB::table('zkteco_marcaciones')
                ->insertOrIgnore([
                    'dispositivo_id' => $dispositivo->id,

                    'uid' => $marcacion['uid'],

                    'user_id' => $marcacion['user_id'],

                    'record_time' => $marcacion['record_time'],

                    'state' => $marcacion['state'],

                    'type' => $marcacion['type'],

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            $insertadas += $insertado;
        }

        return [
            'leidas' => count($marcaciones),
            'insertadas' => $insertadas,
        ];
    }
}

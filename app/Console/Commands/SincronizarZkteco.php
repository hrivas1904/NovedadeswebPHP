<?php

namespace App\Console\Commands;

use App\Services\ZktecoSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SincronizarZkteco extends Command
{
    protected $signature = 'zkteco:sincronizar';

    protected $description = 'Sincroniza las marcaciones de los relojes ZKTeco';

    public function handle(ZktecoSyncService $syncService): int
    {
        $dispositivos = DB::table('zkteco_dispositivos')
            ->where('activo', 1)
            ->get();

        if ($dispositivos->isEmpty()) {

            $this->warn('No hay dispositivos ZKTeco activos.');

            return self::SUCCESS;
        }

        foreach ($dispositivos as $dispositivo) {

            try {

                $this->info(
                    "Sincronizando: {$dispositivo->nombre}"
                );

                $resultado = $syncService->sincronizar(
                    $dispositivo
                );

                $this->info(
                    "Leídas: {$resultado['leidas']} | " .
                        "Insertadas: {$resultado['insertadas']}"
                );

                Log::info('Sincronización ZKTeco completada', [
                    'dispositivo_id' => $dispositivo->id,
                    'dispositivo' => $dispositivo->nombre,
                    'leidas' => $resultado['leidas'],
                    'insertadas' => $resultado['insertadas'],
                ]);
            } catch (\Throwable $e) {

                /*
                 * Importante:
                 * Si falla un reloj, continuamos con el siguiente.
                 */

                $this->error(
                    "Error sincronizando {$dispositivo->nombre}: " .
                        $e->getMessage()
                );

                Log::error('Error sincronizando ZKTeco', [
                    'dispositivo_id' => $dispositivo->id,
                    'dispositivo' => $dispositivo->nombre,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }
}

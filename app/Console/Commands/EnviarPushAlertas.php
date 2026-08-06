<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\WebPushService;

class EnviarPushAlertas extends Command
{
    protected $signature = 'alertas:enviar-push';
    protected $description = 'Envía push de alertas_usuario pendientes (push_enviado = 0)';

    public function handle(WebPushService $webPush)
    {
        $pendientes = DB::table('alertas_usuario')
            ->where('push_enviado', 0)
            ->orderBy('fecha')
            ->limit(200)
            ->get();

        if ($pendientes->isEmpty()) {
            return;
        }

        foreach ($pendientes as $alerta) {
            if (!empty($alerta->usuario_destino)) {
                $webPush->send(
                    $alerta->usuario_destino,
                    ucfirst(strtolower($alerta->modulo ?? 'Alerta')),
                    $alerta->mensaje,
                    $alerta->url ?? '/'
                );
            }

            DB::table('alertas_usuario')
                ->where('id', $alerta->id)
                ->update(['push_enviado' => 1]);
        }

        $this->info(count($pendientes) . ' alertas procesadas.');
    }
}
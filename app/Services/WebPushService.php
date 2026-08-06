<?php

namespace App\Services;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\DB;
use Throwable;

class WebPushService
{
    public function send($usuarioId, $title, $body, $url)
    {
        $auth = [
            'VAPID' => [
                'subject' => config('services.vapid.subject'),
                'publicKey' => config('services.vapid.public_key'),
                'privateKey' => config('services.vapid.private_key'),
            ],
        ];

        try {
            $webPush = new WebPush($auth);
        } catch (Throwable $e) {
            logger()->error('WebPush init falló: ' . $e->getMessage());
            return;
        }

        $subs = DB::table('push_subscriptions')
            ->where('usuario_id', $usuarioId)
            ->get();

        if ($subs->isEmpty()) {
            return;
        }

        foreach ($subs as $sub) {
            try {
                $subscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'keys' => [
                        'p256dh' => $sub->p256dh,
                        'auth' => $sub->auth,
                    ],
                ]);

                $payload = json_encode([
                    'title' => $title,
                    'body' => $body,
                    'url' => $url,
                ]);

                $webPush->queueNotification($subscription, $payload);
            } catch (Throwable $e) {
                logger()->error("Push queueNotification error (endpoint {$sub->endpoint}): " . $e->getMessage());
            }
        }

        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getEndpoint();

            if ($report->isSuccess()) {
                logger()->info("Push OK: {$endpoint}");
                continue;
            }

            $statusCode = $report->getResponse()?->getStatusCode();
            logger()->warning("Push ERROR ({$statusCode}) endpoint {$endpoint}: " . $report->getReason());

            // Solo borramos si el navegador/push service confirma que la suscripción ya no existe
            if (in_array($statusCode, [404, 410])) {
                DB::table('push_subscriptions')->where('endpoint', $endpoint)->delete();
            }
        }
    }
}
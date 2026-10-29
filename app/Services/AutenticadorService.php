<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente del autenticador central.
 * Todos los métodos devuelven un array con 'ok' => bool y, si falla, 'codigo' y 'mensaje'.
 */
class AutenticadorService
{
    public function login(string $usuario, string $password, ?string $ip): array
    {
        return $this->enviar(fn () => $this->cliente()->post('/api/login', [
            'usuario'  => $usuario,
            'password' => $password,
            'ip'       => $ip,
        ]));
    }

    public function me(string $token): array
    {
        return $this->enviar(fn () => $this->cliente()->withToken($token)->get('/api/me'));
    }

    public function logout(string $token, ?string $ip): array
    {
        return $this->enviar(fn () => $this->cliente()->withToken($token)->post('/api/logout', ['ip' => $ip]));
    }

    private function cliente(): PendingRequest
    {
        return Http::baseUrl(config('autenticador.url'))
            ->withHeaders(['X-Sistema-Key' => config('autenticador.key')])
            ->acceptJson()
            ->timeout(config('autenticador.timeout'));
    }

    private function enviar(callable $llamada): array
    {
        try {
            $resp = $llamada();
        } catch (ConnectionException $e) {
            Log::error('Autenticador no disponible: ' . $e->getMessage());
            return ['ok' => false, 'codigo' => 'autenticador_no_disponible',
                'mensaje' => 'El servicio de autenticación no está disponible. Intente más tarde.'];
        }

        $data = $resp->json() ?? [];

        if ($resp->successful() && ($data['ok'] ?? false)) {
            return $data;
        }

        return [
            'ok'      => false,
            'codigo'  => $data['codigo'] ?? 'error_' . $resp->status(),
            'mensaje' => $data['mensaje'] ?? 'No se pudo autenticar',
        ];
    }
}

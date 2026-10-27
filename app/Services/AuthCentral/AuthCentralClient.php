<?php

namespace App\Services\AuthCentral;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthCentralClient
{
    public function login(string $username, string $password, ?string $ip, ?string $navegador): RespuestaAuth
    {
        return $this->enviar('post', '/api/login', [
            'username'   => $username,
            'password'   => $password,
            'ip_cliente' => $ip,
            'navegador'  => $navegador,
        ]);
    }

    public function me(string $token, ?string $ip = null, ?string $navegador = null): RespuestaAuth
    {
        return $this->enviar('get', '/api/me', ['ip_cliente' => $ip, 'navegador' => $navegador], $token);
    }

    public function logout(string $token, ?string $ip = null, ?string $navegador = null): RespuestaAuth
    {
        return $this->enviar('post', '/api/logout', ['ip_cliente' => $ip, 'navegador' => $navegador], $token);
    }

    private function enviar(string $metodo, string $ruta, array $datos, ?string $token = null): RespuestaAuth
    {
        // Solo se quitan los null: una contraseña "0" tiene que viajar igual
        $datos = array_filter($datos, fn ($v) => $v !== null);

        try {
            $request = Http::baseUrl(config('auth_central.url'))
                ->withHeaders(['X-Sistema-Key' => config('auth_central.key')])
                ->acceptJson()
                ->timeout(config('auth_central.timeout'));

            if ($token) {
                $request = $request->withToken($token);
            }

            $respuesta = $metodo === 'get'
                ? $request->get($ruta, $datos)
                : $request->post($ruta, $datos);
        } catch (ConnectionException $e) {
            Log::error('Auth central no disponible', ['ruta' => $ruta, 'error' => $e->getMessage()]);

            return new RespuestaAuth(false, 0, 'SERVICIO_NO_DISPONIBLE',
                'El servicio de autenticación no está disponible. Intentá más tarde.');
        }

        if ($respuesta->successful()) {
            return new RespuestaAuth(true, $respuesta->status(), datos: $respuesta->json() ?? []);
        }

        $json = $respuesta->json();

        // Una respuesta sin nuestro formato (HTML de Apache, proxy, etc.) es algo anómalo: se registra
        if (!isset($json['error'])) {
            Log::warning('Respuesta inesperada del auth central', [
                'ruta'   => $ruta,
                'status' => $respuesta->status(),
                'body'   => mb_substr($respuesta->body(), 0, 500),
            ]);
        }

        return new RespuestaAuth(false, $respuesta->status(),
            $json['error'] ?? 'ERROR_DESCONOCIDO',
            $json['mensaje'] ?? 'No se pudo completar la operación con el servicio de autenticación.',
            $json ?? []);
    }
}
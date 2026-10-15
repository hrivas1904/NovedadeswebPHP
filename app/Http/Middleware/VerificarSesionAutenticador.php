<?php

namespace App\Http\Middleware;

use App\Services\AutenticadorService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cada N minutos revalida la sesión contra /api/me.
 * Si el usuario fue desactivado o perdió acceso a RRHH en el autenticador,
 * se cierra la sesión local sin esperar a que expire.
 *
 * Si el autenticador está caído, NO se corta la sesión (se reintenta en el próximo request).
 */
class VerificarSesionAutenticador
{
    public function __construct(private AutenticadorService $autenticador) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $minutos = (int) config('autenticador.revalidar_cada');
        $ultimo  = session('auth_validado_a', 0);

        if ($minutos > 0 && now()->timestamp - $ultimo >= $minutos * 60) {
            $token = session('auth_token');
            $resp  = $token
                ? $this->autenticador->me($token)
                : ['ok' => false, 'codigo' => 'sin_token'];

            if ($resp['ok']) {
                session(['auth_validado_a' => now()->timestamp]);
            } elseif (($resp['codigo'] ?? '') !== 'autenticador_no_disponible') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return $request->expectsJson()
                    ? response()->json(['mensaje' => 'Sesión finalizada'], 401)
                    : redirect()->route('login')->withErrors(['usuario' => 'Su sesión fue finalizada.']);
            }
        }

        return $next($request);
    }
}

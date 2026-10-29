<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthCentral\AuthCentralClient;
use App\Services\AuthCentral\RespuestaAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('index');
        }
        return view('login.login');
    }

    public function login(Request $request, AuthCentralClient $authCentral)
    {
        $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (!config('auth_central.habilitado')) {
            return $this->loginLocal($request);
        }

        // 1) El auth decide si la persona puede entrar a RRHH
        $respuesta = $authCentral->login(
            $request->username, $request->password, $request->ip(), $request->userAgent()
        );

        if (!$respuesta->ok) {
            return $this->rechazar($request, $this->mensajeDeError($respuesta));
        }

        $token      = $respuesta->datos['token'];
        $authUserId = $respuesta->datos['usuario']['id'];

        // 2) Usuario local vinculado + reglas propias de RRHH
        $user = User::where('auth_user_id', $authUserId)->first();

        $motivo = match (true) {
            !$user                     => 'Tu usuario no está habilitado en RRHH. Contactá al administrador.',
            $user->estado !== 'ACTIVO' => 'El usuario se encuentra de baja.',
            default                    => $this->motivoPorEmpleado($user),
        };

        if ($motivo) {
            // El auth ya emitió un token: se revoca para no dejarlo huérfano
            $authCentral->logout($token, $request->ip(), $request->userAgent());
            return $this->rechazar($request, $motivo);
        }

        // 3) Sesión local de siempre, con el usuario de RRHH
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('auth_central', [
            'token'      => $token,
            'expira'     => $respuesta->datos['expira'] ?? null,
            'revalidado' => now()->timestamp,
        ]);

        return redirect()->intended('index');
    }

    public function logout(Request $request, AuthCentralClient $authCentral)
    {
        // Si el auth no responde, igual se cierra la sesión local
        if ($token = $request->session()->get('auth_central.token')) {
            $authCentral->logout($token, $request->ip(), $request->userAgent());
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ---------------------------------------------------------------

    /** Login local original de RRHH (AUTH_CENTRAL_HABILITADO=false). */
    private function loginLocal(Request $request)
    {
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return $this->rechazar($request, 'Las credenciales no coinciden con nuestros registros.');
        }

        if ($user->estado !== 'ACTIVO') {
            return $this->rechazar($request, 'El usuario se encuentra de baja.');
        }

        if ($motivo = $this->motivoPorEmpleado($user)) {
            return $this->rechazar($request, $motivo);
        }

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect()->intended('index');
        }

        return $this->rechazar($request, 'Las credenciales no coinciden con nuestros registros.');
    }

    private function motivoPorEmpleado(User $user): ?string
    {
        $empleado = DB::table('empleados')->where('LEGAJO', $user->legajo)->first();

        return ($empleado && $empleado->ESTADO !== 'ACTIVO')
            ? 'El colaborador se encuentra de baja.'
            : null;
    }

    private function mensajeDeError(RespuestaAuth $respuesta): string
    {
        return match ($respuesta->error) {
            'CREDENCIALES_INVALIDAS' => 'Las credenciales no coinciden con nuestros registros.',
            'USUARIO_INACTIVO'       => 'El usuario se encuentra de baja.',
            'SIN_ACCESO'             => 'No tenés acceso a RRHH. Contactá al administrador.',
            'DEMASIADOS_INTENTOS'    => 'Demasiados intentos. Esperá un minuto y volvé a intentar.',
            'DATOS_INVALIDOS',
            'SERVICIO_NO_DISPONIBLE' => $respuesta->mensaje,
            default                  => $this->errorInesperado($respuesta),
        };
    }

    private function errorInesperado(RespuestaAuth $respuesta): string
    {
        // Por ejemplo SISTEMA_INVALIDO: es un problema de configuración (key), no del usuario
        Log::error('Error del auth central en el login', [
            'status' => $respuesta->status,
            'error'  => $respuesta->error,
        ]);

        return 'No se pudo iniciar sesión. Intentá más tarde o avisá al administrador.';
    }

    private function rechazar(Request $request, string $mensaje)
    {
        return back()->withInput($request->only('username'))->withErrors(['username' => $mensaje]);
    }
}
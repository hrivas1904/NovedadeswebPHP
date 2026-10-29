<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AutenticadorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Reemplaza (o adaptá dentro de) tu LoginController actual.
 * La vista de login no cambia: sigue enviando "usuario" y "password".
 */
class LoginController extends Controller
{
    public function __construct(private AutenticadorService $autenticador) {}

    public function showLoginForm()
    {
        return view('auth.login'); // tu vista actual
    }

    public function login(Request $request)
    {
        $request->validate([
            'usuario'  => 'required|string',
            'password' => 'required|string',
        ]);

        $resp = $this->autenticador->login($request->usuario, $request->password, $request->ip());

        if (!$resp['ok']) {
            return back()->withInput($request->only('usuario'))
                ->withErrors(['usuario' => $resp['mensaje']]);
        }

        // Vínculo con el usuario local
        $user = User::where('auth_user_id', $resp['usuario']['id'])->first();

        if (!$user) {
            // Tiene acceso en el autenticador pero no está vinculado en RRHH.
            // Durante la prueba lo rechazamos; más adelante se puede crear automáticamente.
            $this->autenticador->logout($resp['token'], $request->ip());
            return back()->withInput($request->only('usuario'))
                ->withErrors(['usuario' => 'Su usuario no está habilitado en este sistema.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        session([
            'auth_token'      => $resp['token'],
            'auth_validado_a' => now()->timestamp,
        ]);

        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        if ($token = session('auth_token')) {
            $this->autenticador->logout($token, $request->ip());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

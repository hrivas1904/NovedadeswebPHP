<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('index');
        }
        return view('login.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $user = \App\Models\User::where('username', $request->username)->first();

        if (!$user) {
            return back()->withErrors([
                'username' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        // 1. Usuario debe estar activo SIEMPRE
        if ($user->estado !== 'ACTIVO') {
            return back()->withErrors([
                'username' => 'El usuario se encuentra de baja.',
            ]);
        }

        // 2. Busco empleado (puede no existir)
        $empleado = DB::table('empleados')
            ->where('LEGAJO', $user->legajo)
            ->first();

        if ($empleado && $empleado->ESTADO !== 'ACTIVO') {
            return back()->withErrors([
                'username' => 'El colaborador se encuentra de baja.',
            ]);
        }

        // 4. Login
        if (Auth::attempt([
            'username' => $request->username,
            'password' => $request->password
        ])) {
            $request->session()->regenerate();
            return redirect()->intended('index');
        }

        return back()->withErrors([
            'username' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

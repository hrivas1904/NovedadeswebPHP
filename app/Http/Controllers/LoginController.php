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
        // 1. Validamos
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        // 2. Intentamos el ingreso (Laravel hace todo el trabajo aquí)
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('index');
        }

        // 3. Si falla
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

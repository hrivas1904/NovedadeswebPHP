<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function index()
    {
        return view('home.inicio');
    }

    public function dashboard()
    {
        return view('home.dashboard');
    }

    public function ayuda()
    {
        return view('home.ayuda');
    }

    public function crearUsuario()
    {
        return view('login.user');
    }

    public function guardar(Request $request)
    {
        // 🔹 Validación básica
        $validator = Validator::make($request->all(), [
            'legajo' => 'required|numeric',
            'password' => 'required|min:4'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $legajo = $request->legajo;

        // 🔹 1. Verificar que exista el legajo en empleados
        $empleado = DB::table('empleados')
            ->where('legajo', $legajo)
            ->first();

        if (!$empleado) {
            return back()->withErrors(['El legajo no existe'])->withInput();
        }

        // 🔹 2. Verificar que no exista ya el usuario
        $existeUsuario = DB::table('users')
            ->where('legajo', $legajo)
            ->exists();

        if ($existeUsuario) {
            return back()->withErrors(['Ya existe un usuario con ese legajo'])->withInput();
        }

        // 🔹 3. Insertar usuario
        DB::table('users')->insert([
            'name'      => $empleado->COLABORADOR, // 👈 desde empleados
            'password'  => Hash::make($request->password), // 👈 bcrypt
            'area_id'   => $empleado->ID_AREA,
            'username'  => $legajo,
            'rol'       => 'Colaborador/a',
            'legajo'    => $legajo
        ]);

        return redirect()->route('login')->with('success', 'Usuario creado correctamente');
    }
}

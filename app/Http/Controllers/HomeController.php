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
            'dni' => 'required|numeric',
            'password' => 'required|min:4'
        ]);

        if ($validator->fails()) {
            return redirect()->route('usuario')
                ->withErrors($validator)
                ->withInput();
        }

        $legajo = $request->legajo;

        // 🔹 1. Validar legajo + DNI + estado ACTIVO
        $empleado = DB::table('empleados')
            ->where('legajo', $request->legajo)
            ->where('dni', $request->dni)
            ->where('estado', 'ACTIVO')
            ->first();

        if (!$empleado) {
            return redirect()->route('usuario')
                ->withErrors(['Datos inválidos o colaborador inactivo'])
                ->withInput();
        }

        // 🔹 2. Verificar que no exista usuario
        $existeUsuario = DB::table('users')
            ->where('legajo', $legajo)
            ->exists();

        if ($existeUsuario) {
            return redirect()->route('usuario')
                ->withErrors(['Ya existe un usuario con ese legajo'])
                ->withInput();
        }

        // 🔹 3. Insertar usuario
        DB::beginTransaction();

        try {

            DB::table('users')->insert([
                'name'      => $empleado->COLABORADOR,
                'password'  => Hash::make($request->password),
                'area_id'   => $empleado->ID_AREA,
                'username'  => $request->legajo,
                'rol'       => 'Colaborador/a',
                'legajo'    => $request->legajo
            ]);

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()->route('usuario')
                ->withErrors(['Error al crear el usuario'])
                ->withInput();
        }

        // 🔹 Éxito → vuelve al login
        return redirect()->route('login')
            ->with('success', 'Usuario creado correctamente');
    }
}

<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

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

    public function forgotPassword()
    {
        return view('login.forgotPassword');
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

    public function restaurarPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'legajo' => 'required|numeric',
            'dni' => 'required|numeric',
            'password' => 'required|min:4'
        ]);

        if ($validator->fails()) {
            return redirect()->route('password.reset')
                ->withErrors($validator)
                ->withInput();
        }

        // Validar colaborador
        $empleado = DB::table('empleados')
            ->where('legajo', $request->legajo)
            ->where('dni', $request->dni)
            ->where('estado', 'ACTIVO')
            ->first();

        if (!$empleado) {
            return redirect()->route('password.reset')
                ->withErrors(['Datos inválidos o colaborador inactivo'])
                ->withInput();
        }

        // Buscar usuario existente
        $usuario = DB::table('users')
            ->where('legajo', $request->legajo)
            ->first();

        if (!$usuario) {
            return redirect()->route('password.reset')
                ->withErrors(['No existe un usuario asociado a ese legajo'])
                ->withInput();
        }

        try {

            DB::table('users')
                ->where('legajo', $request->legajo)
                ->update([
                    'password' => Hash::make($request->password)
                ]);
        } catch (\Exception $e) {

            return redirect()->route('password.reset')
                ->withErrors(['Ocurrió un error al restaurar la contraseña'])
                ->withInput();
        }

        return redirect()->route('login')
            ->with('success', 'La contraseña fue restablecida correctamente');
    }

    public function cargarDashboardDiario(Request $requests)
    {
        try {
            return response()->json([

                'totalColabActivos' => DB::select('CALL SP_COLAB_ACTIVOS_ACTUALES()'),
                'totalNovMes' => DB::select('CALL SP_NOVEDADES_ACTUALES()'),
                'totalAdPendiente' => DB::select(('CALL SP_ADELANTOS_PENDIENTES()')),
                'totalTicketsAbiertos' => DB::select('CALL SP_CANT_TICKET_ABIERTOS()'),
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => true,
                'mensaje' => 'Error en dashboard',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function cargarMisOperaciones(Request $requests)
    {

        $legajo = auth()->user()->legajo;

        try {
            return response()->json([
                'misNovedades' => DB::select('CALL SP_CANT_MIS_NOVEDADES(?)', [$legajo]),
                'misAdelantos' => DB::select('CALL SP_CANT_MIS_ADELANTOS(?)', [$legajo]),
                'misTickets' => DB::select(('CALL SP_CANT_MIS_TICKET(?)'), [$legajo]),
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => true,
                'mensaje' => 'Error en dashboard',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}

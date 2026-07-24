<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ConfigController extends Controller
{
    public function rolesPermisosView()
    {
        return view('roles_usuarios.roles');
    }

    public function listarRoles()
    {
        $roles = DB::select("CALL SP_LISTAR_ROLES_SISTEMA()");

        return response()->json([
            'success' => true,
            'roles' => $roles
        ]);
    }

    public function listarPermisosPorModulo(int $rolId, string $slugModulo)
    {
        $permisos = DB::select(
            "CALL SP_LISTAR_PERMISOS_POR_MODULO(?, ?)",
            [$rolId, $slugModulo]
        );

        return response()->json([
            'success' => true,
            'permisos' => $permisos
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdministracionController extends Controller
{
    public function pedidosComprasView()
    {
        return view('administracion.pedidosCompras');
    }

    public function panelAdminView()
    {
        return view('administracion.panelAdmin');
    }

    public function listarCentrosCosto()
    {
        $centros = DB::select("CALL SP_LISTAR_CENTRO_COSTOS()");

        return response()->json($centros);
    }

    public function listarProveedores()
    {
        $proveedores = DB::select("CALL SP_LISTAR_PROVEEDORES()");

        return response()->json($proveedores);
    }

    public function listarProductos()
    {
        $productos = DB::select("CALL SP_LISTAR_PRODUCTOS()");

        return response()->json($productos);
    }
}

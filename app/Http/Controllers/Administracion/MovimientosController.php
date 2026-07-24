<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;

class MovimientosController extends Controller
{
    public function movimientosView()
    {
        return view('administracion.movimientos.movimientos');
    }
}

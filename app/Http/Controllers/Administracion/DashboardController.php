<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
     public function homeView()
    {
        return view('administracion.posicion.dashboard');
    }

    public function operacionDiaView()
    {
        return view('administracion.posicion.operacionDia');
    }

}
<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;

class PresupuestarController extends Controller
{
    public function presupuestarView()
    {
        return view('administracion.presupuestar.presupuestar');
    }
}

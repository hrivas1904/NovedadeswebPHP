<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;

class AnalisisController extends Controller
{
    public function homeAnalisisView()
    {
        return view('administracion.analisis.homeAnalisis');
    }
    
    public function flujoFondosView()
    {
        return view('administracion.analisis.flujoFondos');
    }

    public function comparativaView()
    {
        return view('administracion.analisis.comparativoSeis');
    }

    public function presupuestadoView()
    {
        return view('administracion.analisis.presupejecutado');
    }

    public function resumenAnualView()
    {
        return view('administracion.analisis.resumenAnual');
    }
}

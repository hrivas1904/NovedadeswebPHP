<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NovedadesController extends Controller
{
    public function registroNovedades()
    {
        return view('novedades.registroNovedades');
    }

    public function controlNovedades()
    {
        return view('novedades.controlNovedades');
    }

    public function configNovedades()
    {
        return view('novedades.configNovedades');
    }
}

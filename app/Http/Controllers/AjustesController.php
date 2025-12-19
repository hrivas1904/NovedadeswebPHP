<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AjustesController extends Controller
{
    public function ajustes()
    {
        return view('ajustes.ajustes');
    }

    public function configPerfil()
    {
        return view('ajustes.configPerfil');
    }
}

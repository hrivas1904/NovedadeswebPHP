<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class PersonalController extends Controller
{
    public function nominaPersonal()
    {
        return view('personal.nominaPersonal');
    }

    public function controlAsistencia()
    {
        return view('personal.controlAsistencia');
    }

    public function cronogramaPersonal()
    {
        return view('personal.cronogramaPersonal');
    }

    public function registroAsistencia()
    {
        return view('personal.registroAsistencia');
    }
}

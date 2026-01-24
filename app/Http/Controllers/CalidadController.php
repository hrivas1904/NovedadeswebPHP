<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CalidadController extends Controller
{
    public function encuestasCalidad(){
        return view('calidad.encuestas');
    }

    public function dashboardCalidad(){
        return view('calidad.dashboardCalidad');
    }

    public function cmiCalidad(){
        return view('calidad.cmi');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalendarioServController extends Controller
{
    public function calendarioServicios(){
        return view('calendarios.calendarioServ');
    }
}

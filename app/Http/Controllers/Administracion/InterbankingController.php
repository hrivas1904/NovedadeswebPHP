<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;

class InterbankingController extends Controller
{
    public function interbankingView()
    {
        return view('administracion.interbanking.interbanking');
    }
}

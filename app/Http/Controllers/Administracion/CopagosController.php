<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Support\ClasificadorOperacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CopagosController extends Controller {
    
    public function vistaCopagos(){
        return view('cobranzas.copagosIps');
    }

    
}
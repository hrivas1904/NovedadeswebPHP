<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;

class ImportacionController extends Controller
{
    public function importacionView()
    {
        return view('administracion.importacion.importacion');
    }

    public function importMovBancariosView()
    {
        return view('administracion.importacion.importacionBancos');
    }

    public function importMovCajaView()
    {
        return view('administracion.importacion.importacionCaja');
    }

    public function importTsvView()
    {
        return view('administracion.importacion.importacionTsv');
    }
}

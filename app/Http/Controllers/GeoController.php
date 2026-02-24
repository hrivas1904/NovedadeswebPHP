<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeoController extends Controller
{
    public function buscarLocalidades(Request $request)
    {
        $q = $request->get('q');

        $response = Http::get(
            'https://apis.datos.gob.ar/georef/api/localidades',
            ['nombre' => $q]
        );

        dd($response->status(), $response->body());
    }
}

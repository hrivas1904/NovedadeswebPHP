<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeoController extends Controller
{
    public function buscarLocalidades(Request $request)
    {
        $q = $request->get('q');

        // Consultamos a la API oficial
        $response = Http::get('https://apis.datos.gob.ar/georef/api/localidades', [
            'nombre' => $q,
            'max' => 10, // Limitamos para no saturar el select
            'campos' => 'id,nombre,provincia.nombre' // Solo traemos lo necesario
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'No se pudo conectar con el servicio'], 500);
    }
}

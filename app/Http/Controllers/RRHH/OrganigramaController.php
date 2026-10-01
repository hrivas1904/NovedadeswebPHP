<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Services\Organigrama\Estructura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrganigramaController extends Controller
{
    public function __construct(private readonly Estructura $estructura) {}

    public function index()
    {
        $disponible = $this->estructura->disponible();

        return view('organigrama.index', [
            'disponible' => $disponible,
            'datos' => $disponible ? $this->estructura->datos() : null,
            'catalogos' => $disponible && Gate::allows('organigrama.editar') ? $this->estructura->catalogos() : [],
        ]);
    }

    public function datos()
    {
        abort_unless($this->estructura->disponible(), 503, 'Organigrama pendiente de instalación.');

        return response()->json($this->estructura->datos())->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request)
    {
        return response()->json($this->estructura->guardar(null, $request->all(), $request->user()->id), 201);
    }

    public function update(Request $request, string $posicion)
    {
        return response()->json($this->estructura->guardar($posicion, $request->all(), $request->user()->id));
    }

    public function destroy(Request $request, string $posicion)
    {
        $v = $request->validate(['revision' => ['required', 'integer', 'min:0']]);

        return response()->json($this->estructura->eliminar($posicion, (int) $v['revision'], $request->user()->id));
    }
}

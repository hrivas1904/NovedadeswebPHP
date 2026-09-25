<?php

namespace App\Http\Controllers\RRHH;

use App\Enums\Edd\EstadoEvaluacion;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EddController extends Controller
{
    public function index(): RedirectResponse
    {
        $destino = match (true) {
            Gate::allows('edd.administrar') => 'rrhh.edd.resumen',
            Gate::allows('edd.evaluar') => 'rrhh.edd.equipo',
            default => 'rrhh.edd.autoevaluacion',
        };

        return redirect()->route($destino);
    }

    public function resumen(): View
    {
        return $this->pantalla('edd.index');
    }

    public function configuracion(): View
    {
        return $this->pantalla('edd.configuracion.periodo');
    }

    public function poblacion(): View
    {
        return $this->pantalla('edd.configuracion.poblacion');
    }

    public function evaluadores(): View
    {
        return $this->pantalla('edd.configuracion.evaluadores');
    }

    public function instrumento(): View
    {
        return $this->pantalla('edd.configuracion.instrumento');
    }

    public function equipo(): View
    {
        // Consultar asignaciones por usuario autenticado al implementar persistencia.
        // Nunca reemplazar una colección vacía por la nómina general.
        return $this->pantalla('edd.equipo.index', ['evaluaciones' => collect()]);
    }

    public function modeloEvaluacion(): View
    {
        return $this->pantalla('edd.evaluaciones.modelo');
    }

    public function autoevaluacion(Request $request): View
    {
        return $this->pantalla('edd.autoevaluacion.index', [
            'tieneLegajo' => ! empty($request->user()->legajo),
        ]);
    }

    public function reportes(): View
    {
        return $this->pantalla('edd.reportes.index');
    }

    private function pantalla(string $vista, array $datos = []): View
    {
        return view($vista, array_merge([
            'institucion' => config('edd.institucion'),
            'periodoReferencia' => config('edd.periodo_referencia'),
            'escala' => config('edd.escala'),
            'bloques' => config('edd.bloques'),
            'estados' => EstadoEvaluacion::cases(),
        ], $datos));
    }
}

<?php

namespace App\Http\Controllers\RRHH;

use App\Enums\Edd\EstadoEvaluacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Edd\GuardarInstrumentoRequest;
use App\Http\Requests\Edd\GuardarPeriodoRequest;
use App\Http\Requests\Edd\GuardarPoblacionRequest;
use App\Services\Edd\ConfiguracionEdd;
use App\Services\Edd\PoblacionEdd;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EddController extends Controller
{
    public function __construct(private readonly ConfiguracionEdd $configuracionEdd, private readonly PoblacionEdd $poblacionEdd) {}

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

    public function configuracion(Request $request): View
    {
        return $this->pantalla('edd.configuracion.periodo', $this->contextoConfiguracion($request));
    }

    public function poblacion(Request $request): View
    {
        $datos = $this->contextoPoblacion($request);
        $lista = $datos['poblacionDisponible'] && $datos['periodo'];

        return $this->pantalla('edd.configuracion.poblacion', array_merge($datos, [
            'candidatos' => $lista ? $this->poblacionEdd->candidatos($datos['periodo']['id'], $datos['filtros']) : collect(),
            'participantes' => $lista ? $this->poblacionEdd->participantes($datos['periodo']['id'], $datos['filtros']) : collect(),
        ]));
    }

    public function evaluadores(Request $request): View
    {
        $datos = $this->contextoPoblacion($request);
        $lista = $datos['poblacionDisponible'] && $datos['periodo'];

        return $this->pantalla('edd.configuracion.evaluadores', array_merge($datos, [
            'participantes' => $lista ? $this->poblacionEdd->participantes($datos['periodo']['id'], $datos['filtros'], true) : collect(),
            'evaluadores' => $lista ? $this->poblacionEdd->evaluadores() : collect(),
        ]));
    }

    public function competencias(Request $request): View
    {
        $datos = $this->contextoPoblacion($request);
        $area = $datos['filtros']['area'] ?? $datos['areas']->first()?->ID_AREA;
        $base = $datos['poblacionDisponible'] && $datos['periodo'] && $area
            ? $this->poblacionEdd->competenciasArea($datos['periodo']['id'], $area) : ['revision' => 0, 'items' => []];

        return $this->pantalla('edd.configuracion.competencias', array_merge($datos, [
            'areaSeleccionada' => $area, 'base' => $base, 'textoCompetencias' => $this->poblacionEdd->textoCompetencias($base['items']),
            'catalogo' => config('edd_competencias'),
        ]));
    }

    public function participante(Request $request, int $periodo, int $participante): View
    {
        $datos = $this->contextoPoblacion($request);
        abort_unless($datos['poblacionDisponible'], 404);
        $persona = $this->poblacionEdd->participante($periodo, $participante);

        return $this->pantalla('edd.configuracion.participante', array_merge($datos, [
            'participante' => $persona, 'evaluadores' => $this->poblacionEdd->evaluadores(),
            'textoCompetencias' => $this->poblacionEdd->textoCompetencias($this->poblacionEdd->competenciasEfectivas($persona)),
        ]));
    }

    public function agregarPoblacion(GuardarPoblacionRequest $request, int $periodo): JsonResponse
    {
        $cantidad = $this->poblacionEdd->agregar($periodo, $request->validated('legajos'), (int) $request->user()->id);

        return $this->poblacionGuardada('rrhh.edd.configuracion.poblacion', ['periodo' => $periodo], $cantidad.' colaborador(es) incorporado(s) al período.');
    }

    public function guardarCompetencias(GuardarPoblacionRequest $request, int $periodo, int $area): JsonResponse
    {
        $this->poblacionEdd->guardarArea($periodo, $area, $request->validated(), (int) $request->user()->id);

        return $this->poblacionGuardada('rrhh.edd.configuracion.competencias', ['periodo' => $periodo, 'area' => $area], 'Competencias del área guardadas. Las listas personalizadas se conservan.');
    }

    public function guardarParticipante(GuardarPoblacionRequest $request, int $periodo, int $participante): JsonResponse
    {
        $this->poblacionEdd->guardarParticipante($periodo, $participante, $request->validated(), (int) $request->user()->id);

        return $this->poblacionGuardada('rrhh.edd.configuracion.participante', compact('periodo', 'participante'), 'Configuración del colaborador guardada.');
    }

    public function asignarEvaluadores(GuardarPoblacionRequest $request, int $periodo): JsonResponse
    {
        $this->poblacionEdd->asignarVarios($periodo, $request->validated(), (int) $request->user()->id);

        return $this->poblacionGuardada('rrhh.edd.configuracion.evaluadores', ['periodo' => $periodo], 'Evaluador asignado a los colaboradores seleccionados.');
    }

    private function contextoPoblacion(Request $request): array
    {
        $datos = $this->contextoConfiguracion($request);
        $filtros = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'area' => ['nullable', 'integer', 'min:1']]);
        $disponible = $datos['almacenamientoDisponible'] && $this->poblacionEdd->disponible();

        return array_merge($datos, [
            'poblacionDisponible' => $disponible, 'filtros' => $filtros,
            'areas' => $disponible ? $this->poblacionEdd->areas() : collect(),
        ]);
    }

    private function poblacionGuardada(string $ruta, array $parametros, string $mensaje): JsonResponse
    {
        session()->flash('edd_guardado', $mensaje);

        return response()->json(['redirect' => route($ruta, $parametros)]);
    }

    public function instrumento(Request $request): View
    {
        $datos = $this->contextoConfiguracion($request);
        $instrumentos = $datos['periodo'] ? $this->configuracionEdd->instrumentos($datos['periodo']['id']) : collect();
        $request->validate(['instrumento' => ['nullable', 'integer', 'min:1']]);
        $seleccion = $request->boolean('nuevo_instrumento') ? null : ($request->input('instrumento') ?? $instrumentos->first()?->id);
        $instrumento = $seleccion && $datos['periodo']
            ? $this->configuracionEdd->instrumento($datos['periodo']['id'], (int) $seleccion) : null;

        return $this->pantalla('edd.configuracion.instrumento', array_merge($datos, [
            'instrumentos' => $instrumentos, 'instrumento' => $instrumento,
            'erroresPublicacion' => $instrumento ? $this->configuracionEdd->erroresPublicacion($instrumento) : [],
            'escala' => $instrumento['escala'] ?? config('edd.escala'),
        ]));
    }

    public function crearPeriodo(GuardarPeriodoRequest $request): JsonResponse
    {
        $periodo = $this->configuracionEdd->crearPeriodo($request->validated(), (int) $request->user()->id);

        return $this->periodoGuardado($periodo, 201);
    }

    public function guardarPeriodo(GuardarPeriodoRequest $request, int $periodo): JsonResponse
    {
        return $this->periodoGuardado($this->configuracionEdd->guardarPeriodo($periodo, $request->validated(), (int) $request->user()->id));
    }

    public function crearInstrumento(GuardarInstrumentoRequest $request, int $periodo): JsonResponse
    {
        $instrumento = $this->configuracionEdd->crearInstrumento($periodo, $request->validated(), (int) $request->user()->id);

        return $this->instrumentoGuardado($periodo, $instrumento, 201);
    }

    public function guardarInstrumento(GuardarInstrumentoRequest $request, int $periodo, int $instrumento): JsonResponse
    {
        return $this->instrumentoGuardado($periodo, $this->configuracionEdd->guardarInstrumento($periodo, $instrumento, $request->validated(), (int) $request->user()->id));
    }

    public function publicarInstrumento(Request $request, int $periodo, int $instrumento): JsonResponse
    {
        $datos = $request->validate(['revision' => ['required', 'integer', 'min:0'], 'confirmar' => ['required', 'accepted']]);

        return $this->instrumentoGuardado($periodo, $this->configuracionEdd->publicarInstrumento($periodo, $instrumento, (int) $datos['revision'], (int) $request->user()->id));
    }

    public function nuevaVersionInstrumento(Request $request, int $periodo, int $instrumento): JsonResponse
    {
        $datos = $request->validate(['revision' => ['required', 'integer', 'min:0']]);

        return $this->instrumentoGuardado($periodo, $this->configuracionEdd->nuevaVersion($periodo, $instrumento, (int) $datos['revision'], (int) $request->user()->id), 201);
    }

    public function equipo(Request $request): View
    {
        return $this->pantalla('edd.equipo.index', [
            'evaluaciones' => collect(),
            'asignaciones' => $this->poblacionEdd->disponible() ? $this->poblacionEdd->equipo((int) $request->user()->id) : collect(),
        ]);
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

    private function contextoConfiguracion(Request $request): array
    {
        $request->validate(['periodo' => ['nullable', 'integer', 'min:1']]);
        $disponible = $this->configuracionEdd->disponible();
        $periodos = $disponible ? $this->configuracionEdd->periodos() : collect();
        $nuevo = $request->routeIs('rrhh.edd.configuracion') && $request->boolean('nuevo');
        $id = $nuevo ? null : ($request->route('periodo') ?? $request->input('periodo') ?? $periodos->first()['id'] ?? null);
        $periodo = $disponible && $id ? $this->configuracionEdd->periodo((int) $id) : null;

        return [
            'almacenamientoDisponible' => $disponible, 'periodos' => $periodos, 'periodo' => $periodo,
            'periodoReferencia' => $periodo['nombre'] ?? config('edd.periodo_referencia'),
        ];
    }

    private function periodoGuardado(array $periodo, int $status = 200): JsonResponse
    {
        session()->flash('edd_guardado', 'El período quedó guardado en borrador.');

        return response()->json([
            'id' => $periodo['id'], 'revision' => $periodo['revision'],
            'redirect' => route('rrhh.edd.configuracion', ['periodo' => $periodo['id']]),
        ], $status);
    }

    private function instrumentoGuardado(int $periodo, array $instrumento, int $status = 200): JsonResponse
    {
        session()->flash('edd_guardado', $instrumento['estado'] === 'publicado' ? 'Instrumento publicado. Esta versión queda protegida contra modificaciones.' : 'El borrador del instrumento quedó guardado.');

        return response()->json([
            'id' => $instrumento['id'], 'revision' => $instrumento['revision'], 'estado' => $instrumento['estado'],
            'redirect' => route('rrhh.edd.configuracion.instrumento', ['periodo' => $periodo, 'instrumento' => $instrumento['id']]),
        ], $status);
    }
}

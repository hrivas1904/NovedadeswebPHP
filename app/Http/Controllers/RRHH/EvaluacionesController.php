<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Support\Edd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Carbon\Carbon;


class EvaluacionesController extends Controller
{
    public function index()
    {
        $esAdmin = Auth::user()->rol === 'Administrador/a';
        return view('evaluaciones.index', compact('esAdmin'));
    }

    public function misEvaluaciones()
    {
        return view('evaluaciones.misEvaluaciones');
    }

    public function formularioEvaluacion(int $id)
    {
        try {
            $edd = DB::select('CALL SP_EDD_OBTENER_ENCABEZADO(?, ?)', [$id, Auth::id()])[0] ?? null;
        } catch (QueryException $e) {
            if ($this->esErrorNegocio($e)) abort(403, $e->errorInfo[2]);
            throw $e;
        }
        abort_if(!$edd, 404);

        $modo = match (true) {
            $edd->es_evaluado  && in_array($edd->estado, ['PENDIENTE', 'AUTO_EN_CURSO'])        => 'AUTO',
            $edd->es_evaluador && in_array($edd->estado, ['AUTO_ENVIADA', 'SUPERIOR_EN_CURSO']) => 'SUPERIOR',
            $edd->es_evaluado  && $edd->estado === 'PENDIENTE_FIRMA'                            => 'FIRMA',
            default                                                                             => 'LECTURA',
        };

        $estadoBadge     = config("edd.estados.{$edd->estado}");
        $valoracionLabel = Edd::valoracionLabel($edd->prom_sup_general);

        return view('evaluaciones.formulario', compact('edd', 'modo', 'estadoBadge', 'valoracionLabel'));
    }

    public function listarMisEvaluaciones()
    {
        return $this->ejecutar(fn() => response()->json([
            'data' => DB::select('CALL SP_EDD_MIS_EVALUACIONES(?)', [Auth::id()]),
        ]));
    }

    public function listarPanel(Request $request)
    {
        $request->validate([
            'periodo' => 'nullable|integer',
            'areas'   => 'nullable|string|max:1000',
            'estados' => 'nullable|string|max:500',
        ]);

        return $this->ejecutar(fn() => response()->json([
            'data' => DB::select('CALL SP_EDD_LISTAR_PANEL(?, ?, ?, ?)', [
                Auth::id(),
                $request->periodo,
                $request->areas,
                $request->estados,
            ]),
        ]));
    }

    public function obtenerRespuestas(int $id)
    {
        return $this->ejecutar(fn() => response()->json([
            'data' => DB::select('CALL SP_EDD_OBTENER_RESPUESTAS(?, ?)', [$id, Auth::id()]),
        ]));
    }

    public function listarCandidatos(int $id)
    {
        return $this->ejecutar(fn() => response()->json([
            'data' => DB::select('CALL SP_EDD_CANDIDATOS_EVALUADOR(?, ?)', [$id, Auth::id()]),
        ]));
    }

    public function guardarBorrador(Request $request, int $id)
    {
        $datos = $this->validarRespuestas($request);

        return $this->ejecutar(function () use ($datos, $id) {
            DB::transaction(fn() => $this->guardarFilas($id, $datos));
            return response()->json(['message' => 'Borrador guardado.']);
        });
    }

    /** Enviar = guardar lo que está en pantalla + enviar, en una sola transacción */
    public function enviarEvaluacion(Request $request, int $id)
    {
        $datos = $this->validarRespuestas($request);

        return $this->ejecutar(function () use ($datos, $id, $request) {
            DB::transaction(function () use ($datos, $id, $request) {
                $this->guardarFilas($id, $datos);

                $sp = $datos['parte'] === 'AUTO'
                    ? 'SP_EDD_ENVIAR_AUTOEVALUACION'
                    : 'SP_EDD_ENVIAR_EVALUACION_SUPERIOR';

                DB::statement("CALL {$sp}(?, ?, ?, ?)", [
                    $id,
                    Auth::id(),
                    $request->ip(),
                    $request->userAgent(),
                ]);
            });

            $mensaje = $datos['parte'] === 'AUTO'
                ? 'Autoevaluación enviada.'
                : 'Evaluación enviada y firmada.';

            return response()->json(['message' => $mensaje]);
        });
    }

    public function firmarEvaluacion(Request $request, int $id)
    {
        $request->validate(['observacion' => 'nullable|string|max:2000']);

        return $this->ejecutar(function () use ($request, $id) {
            DB::transaction(fn() => DB::statement('CALL SP_EDD_FIRMAR_EVALUADO(?, ?, ?, ?, ?)', [
                $id,
                $request->observacion,
                Auth::id(),
                $request->ip(),
                $request->userAgent(),
            ]));
            return response()->json(['message' => 'Evaluación firmada.']);
        });
    }

    /* =============================== PANEL (ADMIN) =============================== */

    public function generarPeriodo(Request $request)
    {
        $request->validate(['periodo' => 'required|integer|min:2024|max:' . now()->year]);

        return $this->ejecutar(function () use ($request) {
            $filas = DB::transaction(fn() => DB::select('CALL SP_EDD_GENERAR_PERIODO(?, ?, ?, ?)', [
                $request->periodo,
                Auth::id(),
                $request->ip(),
                $request->userAgent(),
            ]));

            return response()->json([
                'resumen' => collect($filas)->countBy('resultado'),
                'data'    => $filas,
            ]);
        });
    }

    public function asignarEvaluador(Request $request, int $id)
    {
        $request->validate(['id_tramo_evaluador' => 'required|integer']);

        return $this->ejecutar(function () use ($request, $id) {
            DB::transaction(fn() => DB::statement('CALL SP_EDD_ASIGNAR_EVALUADOR(?, ?, ?, ?, ?)', [
                $id,
                $request->id_tramo_evaluador,
                Auth::id(),
                $request->ip(),
                $request->userAgent(),
            ]));
            return response()->json(['message' => 'Evaluador asignado.']);
        });
    }

    /* =============================== PRIVADOS =============================== */

    private function validarRespuestas(Request $request): array
    {
        return $request->validate([
            'parte'                   => 'required|in:AUTO,SUPERIOR',
            'respuestas'              => 'required|array|min:1',
            'respuestas.*.id'         => 'required|integer',
            'respuestas.*.puntaje'    => 'nullable|integer|between:1,4',
            'respuestas.*.comentario' => 'nullable|string|max:2000',
            'retroalimentacion'       => 'nullable|string|max:5000',
        ]);
    }

    private function guardarFilas(int $id, array $datos): void
    {
        foreach ($datos['respuestas'] as $r) {
            DB::statement('CALL SP_EDD_GUARDAR_RESPUESTA(?, ?, ?, ?, ?, ?)', [
                $id,
                $r['id'],
                $datos['parte'],
                $r['puntaje'] ?? null,
                $r['comentario'] ?? null,
                Auth::id(),
            ]);
        }

        if ($datos['parte'] === 'SUPERIOR') {
            DB::statement('CALL SP_EDD_GUARDAR_RETROALIMENTACION(?, ?, ?)', [
                $id,
                $datos['retroalimentacion'] ?? null,
                Auth::id(),
            ]);
        }
    }

    private function esErrorNegocio(QueryException $e): bool
    {
        return ($e->errorInfo[0] ?? null) === '45000';
    }

    private function ejecutar(callable $fn)
    {
        try {
            return $fn();
        } catch (QueryException $e) {
            if ($this->esErrorNegocio($e)) {
                return response()->json(['message' => $e->errorInfo[2]], 422);
            }
            Log::error('EDD: ' . $e->getMessage());
            return response()->json(['message' => 'Ocurrió un error inesperado.'], 500);
        }
    }
}

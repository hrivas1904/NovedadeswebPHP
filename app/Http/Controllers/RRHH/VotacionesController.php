<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PhpParser\Node\Expr\FuncCall;

class VotacionesController extends Controller
{
    public function index()
    {
        return view('flyers.votacionRecEntrePares');
    }

    public function obtenerColaboradoresxGrupo(Request $request, $token)
    {
        try {

            $request->validate([
                'dni' => ['required', 'regex:/^[0-9]{7,8}$/']
            ]);

            $colaborador = DB::table('empleados')
                ->select('legajo')
                ->where('dni', $request->dni)
                ->where('estado', 'ACTIVO')
                ->first();

            if (!$colaborador) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Colaborador no encontrado.'
                ], 404);
            }

            $legajo = $colaborador->legajo;

            $grupos = [
                'grupo1' => [4],
                'grupo2' => [5, 10, 11, 15, 16],
                'grupo3' => [1, 3, 6, 7, 12, 14]
            ];

            $resultado = [];

            foreach ($grupos as $nombre => $areas) {

                $resultado[$nombre] = DB::table('empleados as e')
                    ->join('areas as a', 'a.id_area', '=', 'e.id_area')
                    ->select(
                        'e.legajo',
                        'e.colaborador',
                        'a.nombre as area'
                    )
                    ->whereIn('e.id_area', $areas)
                    ->where('e.estado', 'ACTIVO')
                    ->where('e.legajo', '<>', $legajo)
                    ->orderBy('e.colaborador', 'ASC')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Exception $e) {

            Log::error('Error al obtener colaboradores por grupo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener los colaboradores.'
            ], 500);
        }
    }

    public function validarDniVotacion(Request $request)
    {
        $request->validate([
            'dni' => ['required', 'regex:/^[0-9]{7,8}$/'],
            'id_votacion' => 'required|integer'
        ]);

        try {

            $dni = $request->dni;
            $idVotacion = $request->id_votacion;

            // Validar disponibilidad de la votación
            $votacion = DB::table('votaciones')
                ->where('id', $idVotacion)
                ->where('abierta', 1)
                ->whereDate('fecha_desde', '<=', now()->toDateString())
                ->whereDate('fecha_hasta', '>=', now()->toDateString())
                ->first();

            if (!$votacion) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'La votación no se encuentra habilitada.'
                ], 403);
            }

            // Buscar colaborador
            $colaborador = DB::table('empleados')
                ->select('legajo', 'colaborador', 'estado')
                ->where('dni', $dni)
                ->first();

            if (!$colaborador || $colaborador->estado !== 'ACTIVO') {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se encontró un colaborador activo asociado al DNI ingresado.'
                ], 404);
            }

            // Verificar si ya votó
            $yaVoto = DB::table('votacion_encabezado')
                ->where('id_colaborador', $colaborador->legajo)
                ->where('id_votacion', $idVotacion)
                ->exists();

            if ($yaVoto) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Usted ya emitió su voto en esta votación.'
                ], 409);
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Colaborador validado correctamente.',
                'data' => [
                    'colaborador' => $colaborador->colaborador
                ]
            ]);
        } catch (\Exception $e) {

            Log::error('Error al validar DNI para votación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Ocurrió un error al validar el DNI.'
            ], 500);
        }
    }

    public function emitirVoto(Request $request, $token)
    {
        $request->validate([
            'dni' => ['required', 'regex:/^[0-9]{7,8}$/'],
            'grupos' => ['required', 'array', 'size:3'],
            'grupos.*.grupo' => ['required', 'integer', 'between:1,3'],
            'grupos.*.candidato' => ['required'],
            'grupos.*.situacion' => ['nullable', 'string', 'max:512'],
        ]);

        try {

            $idVotacion = 1;

            DB::transaction(function () use ($request, $idVotacion) {

                $dni = $request->dni;

                $areas = [
                    1 => [4],
                    2 => [5, 10, 11, 15, 16],
                    3 => [1, 3, 6, 7, 12, 14]
                ];

                // Obtener preguntas ordenadas
                $preguntas = DB::table('votacion_preguntas')
                    ->where('id_votacion', $idVotacion)
                    ->orderBy('orden')
                    ->orderBy('id')
                    ->pluck('id')
                    ->toArray();

                if (count($preguntas) !== 3) {
                    throw new \Exception(
                        'La votación debe tener tres preguntas configuradas.'
                    );
                }

                $grupos = collect($request->grupos)->keyBy('grupo');

                if ($grupos->count() !== 3) {
                    throw new \Exception('Los grupos enviados no son válidos.');
                }

                $respuestas = [];

                foreach ([1, 2, 3] as $numero) {

                    $grupo = $grupos->get($numero);

                    if (!$grupo) {
                        throw new \Exception('Faltan respuestas de un grupo.');
                    }

                    $candidato = (string) $grupo['candidato'];
                    $situacion = trim($grupo['situacion'] ?? '');

                    if ($candidato === 'NO_CONOZCO') {

                        $situacion = '';
                    } else {

                        if (
                            !ctype_digit($candidato) ||
                            !$situacion
                        ) {
                            throw new \Exception(
                                "Respuestas incompletas en grupo {$numero}."
                            );
                        }

                        // Verificar que el candidato sea válido
                        $candidatoValido = DB::table('empleados')
                            ->where('legajo', $candidato)
                            ->where('estado', 'ACTIVO')
                            ->whereIn('id_area', $areas[$numero])
                            ->where('dni', '<>', $dni)
                            ->exists();

                        if (!$candidatoValido) {
                            throw new \Exception(
                                "Candidato inválido en grupo {$numero}."
                            );
                        }
                    }

                    $respuestas[] = [
                        'pregunta' => $preguntas[0],
                        'respuesta' => $candidato,
                        'grupo' => $numero
                    ];

                    $respuestas[] = [
                        'pregunta' => $preguntas[2],
                        'respuesta' => $situacion,
                        'grupo' => $numero
                    ];
                }

                // Registrar encabezado
                DB::statement(
                    "CALL SP_EMITIR_VOTO_ENCABEZADO(
                        ?, ?, ?, @msj, @id_encabezado, @puede_votar
                    )",
                    [
                        null,
                        $dni,
                        $idVotacion
                    ]
                );

                $resultado = DB::selectOne(
                    "SELECT
                    @msj AS mensaje,
                    @id_encabezado AS id_encabezado,
                    @puede_votar AS puede_votar"
                );

                if (
                    !$resultado ||
                    (int) $resultado->puede_votar !== 1 ||
                    !$resultado->id_encabezado
                ) {
                    throw new \Exception(
                        $resultado->mensaje ?? 'No se pudo registrar el voto.'
                    );
                }

                foreach ($respuestas as $respuesta) {

                    DB::statement(
                        "CALL SP_EMITIR_VOTO_RESPUESTA(?, ?, ?)",
                        [
                            $resultado->id_encabezado,
                            $respuesta['pregunta'],
                            $respuesta['respuesta']
                        ]
                    );
                }
            });

            return response()->json([
                'success' => true,
                'mensaje' => 'Voto registrado correctamente.'
            ]);
        } catch (\Throwable $e) {

            Log::error('Error al emitir voto', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage()
            ], 422);
        }
    }

    public function verVotaciones()
    {
        return view('personal.verVotaciones');
    }

    public function obtenerRespuestas()
    {
        $respuestas = DB::select("call SP_OBTENER_RESPUESTAS_REC_PARES()");
        return response()->json(["data" => $respuestas]);
    }

    public function mejores3xGrupo()
    {
        $mejores = DB::select("call SP_OBTENER_MEJORES3_XGRUPO();");
        return response()->json((["data" => $mejores]));
    }


    public function listarAsistencias()
    {
        try {

            $registros = DB::table('eventos_confirmaciones as ec')
                ->join(
                    'eventos_invitados as ei',
                    'ei.id_confirmacion',
                    '=',
                    'ec.id'
                )
                ->leftJoin(
                    'eventos_requerimientos_invitados as eri',
                    'eri.id_invitado',
                    '=',
                    'ei.id'
                )
                ->whereNull('ec.deleted_at')
                ->whereNull('ei.deleted_at')
                ->select(
                    'ec.id as id_confirmacion',
                    'ec.fecha_confirmacion',
                    'ec.transporte',
                    'ec.cancion',
                    'ei.id as id_invitado',
                    'ei.invitado',
                    'ei.dni',
                    'ei.sector',
                    'ei.condicion',
                    'eri.requerimiento',
                    'ec.cancion',
                )
                ->orderByDesc('ec.fecha_confirmacion')
                ->get();

            $confirmaciones = $registros
                ->groupBy('id_confirmacion')
                ->map(function ($filas) {

                    $primera = $filas->first();

                    $invitados = $filas
                        ->groupBy('id_invitado')
                        ->map(function ($items) {

                            $persona = $items->first();

                            return [
                                'id' => $persona->id_invitado,
                                'nombre' => $persona->invitado,
                                'dni' => $persona->dni,
                                'sector' => $persona->sector,
                                'condicion' => $persona->condicion,
                                'requerimientos' => $items
                                    ->pluck('requerimiento')
                                    ->filter()
                                    ->unique()
                                    ->values()
                                    ->all()
                            ];
                        })
                        ->values();

                    $titular = $invitados->firstWhere(
                        'condicion',
                        'TITULAR'
                    );

                    $acompanantes = $invitados
                        ->where('condicion', 'ACOMPAÑANTE')
                        ->values();

                    return [
                        'id_confirmacion' => $primera->id_confirmacion,
                        'fecha_confirmacion' => $primera->fecha_confirmacion,
                        'transporte' => $primera->transporte,
                        'cancion' => $primera->cancion,
                        'titular' => $titular,
                        'acompanantes' => $acompanantes->all(),
                        'cantidad_acompanantes' => $acompanantes->count()
                    ];
                })
                ->values();

            return response()->json([
                'data' => $confirmaciones
            ]);
        } catch (\Throwable $e) {

            Log::error('Error al listar asistencias', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'data' => [],
                'mensaje' => 'No se pudieron obtener las asistencias.'
            ], 500);
        }
    }
}

<?php

namespace App\Services\Edd;

use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ConfiguracionEdd
{
    private const FECHAS = ['fecha_corte', 'inicio_evaluacion', 'fin_evaluacion', 'inicio_autoevaluacion', 'fin_autoevaluacion', 'limite_devolucion'];

    public function disponible(): bool
    {
        return Schema::hasTable('edd_eventos');
    }

    public function periodos(): Collection
    {
        return DB::table('edd_periodos')->orderByDesc('anio')->orderByDesc('id')->get()
            ->map(fn ($periodo) => $this->presentarPeriodo($periodo));
    }

    public function periodo(int $id): array
    {
        $periodo = DB::table('edd_periodos')->where('id', $id)->first();
        abort_unless($periodo, 404);

        return $this->presentarPeriodo($periodo);
    }

    public function instrumentos(int $periodo): Collection
    {
        return DB::table('edd_instrumentos as i')
            ->join('edd_periodo_instrumentos as pi', 'pi.instrumento_id', '=', 'i.id')
            ->where('pi.periodo_id', $periodo)->orderBy('i.codigo')->orderByDesc('i.version')
            ->get(['i.id', 'i.codigo', 'i.nombre', 'i.version', 'i.estado']);
    }

    public function instrumento(int $periodo, int $id): array
    {
        $this->verificarVinculo($periodo, $id);
        $instrumento = (array) DB::table('edd_instrumentos')->where('id', $id)->first();
        $instrumento['escala'] = json_decode($instrumento['escala_json'], true, flags: JSON_THROW_ON_ERROR);
        $instrumento['bloques'] = DB::table('edd_bloques')->where('instrumento_id', $id)->orderBy('orden')->get()
            ->mapWithKeys(function ($bloque) {
                $datos = (array) $bloque;
                $datos['items'] = DB::table('edd_items')->where('bloque_id', $bloque->id)->orderBy('orden')->get()
                    ->map(fn ($item) => (array) $item)->all();

                return [$bloque->codigo => $datos];
            })->all();

        return $instrumento;
    }

    public function crearPeriodo(array $datos, int $actor): array
    {
        try {
            return DB::transaction(function () use ($datos, $actor) {
                $id = DB::table('edd_periodos')->insertGetId(array_merge($this->datosPeriodo($datos), [
                    'estado' => 'borrador', 'revision' => 0, 'created_by' => $actor, 'updated_by' => $actor,
                    'created_at' => $this->ahora(), 'updated_at' => $this->ahora(),
                ]));
                $resultado = $this->periodo($id);
                $this->evento($id, 'periodo', $id, 'crear_periodo', $actor, null, $resultado);

                return $resultado;
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['codigo' => 'Ya existe un período con ese código.']);
        }
    }

    public function guardarPeriodo(int $id, array $datos, int $actor): array
    {
        try {
            return DB::transaction(function () use ($id, $datos, $actor) {
                $antes = $this->bloquearPeriodo($id);
                $this->verificarRevision($antes->revision, $datos['revision']);
                DB::table('edd_periodos')->where('id', $id)->update(array_merge($this->datosPeriodo($datos), [
                    'revision' => $antes->revision + 1, 'updated_by' => $actor, 'updated_at' => $this->ahora(),
                ]));
                $resultado = $this->periodo($id);
                $this->evento($id, 'periodo', $id, 'guardar_periodo', $actor, $this->presentarPeriodo($antes), $resultado);

                return $resultado;
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['codigo' => 'Ya existe un período con ese código.']);
        }
    }

    public function crearInstrumento(int $periodo, array $datos, int $actor): array
    {
        try {
            return DB::transaction(function () use ($periodo, $datos, $actor) {
                $this->bloquearPeriodo($periodo);
                if (DB::table('edd_instrumentos')->where('codigo', $datos['codigo'])->exists()) {
                    throw ValidationException::withMessages(['codigo' => 'Ese código ya identifica un instrumento. Usá otro código o creá una nueva versión desde el instrumento publicado.']);
                }
                $id = DB::table('edd_instrumentos')->insertGetId([
                    'codigo' => $datos['codigo'], 'nombre' => $datos['nombre'], 'version' => 1,
                    'estado' => 'borrador', 'revision' => 0, 'escala_json' => $this->json(config('edd.escala')),
                    'reglas_calculo_json' => $this->json(['metodo' => 'promedio_ponderado', 'decimales_presentacion' => 2, 'no_aplica' => false]),
                    'created_by' => $actor, 'updated_by' => $actor, 'created_at' => $this->ahora(), 'updated_at' => $this->ahora(),
                ]);
                foreach (array_keys(config('edd.bloques')) as $orden => $codigo) {
                    $bloque = config('edd.bloques.'.$codigo);
                    DB::table('edd_bloques')->insert([
                        'instrumento_id' => $id, 'codigo' => $codigo, 'nombre' => $bloque['nombre'],
                        'descripcion' => $bloque['descripcion'], 'peso_porcentaje' => null, 'orden' => $orden + 1, 'activo' => true,
                    ]);
                }
                DB::table('edd_periodo_instrumentos')->insert(['periodo_id' => $periodo, 'instrumento_id' => $id]);
                $resultado = $this->instrumento($periodo, $id);
                $this->evento($periodo, 'instrumento', $id, 'crear_instrumento', $actor, null, $resultado);

                return $resultado;
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['codigo' => 'Ya existe un instrumento con ese código.']);
        }
    }

    public function guardarInstrumento(int $periodo, int $id, array $datos, int $actor): array
    {
        return DB::transaction(function () use ($periodo, $id, $datos, $actor) {
            $this->bloquearPeriodo($periodo);
            $actual = $this->bloquearInstrumento($periodo, $id, $datos['revision']);
            abort_unless($actual->estado === 'borrador', 409, 'El instrumento está publicado. Creá una nueva versión para modificarlo.');
            $antes = $this->instrumento($periodo, $id);

            foreach ($antes['bloques'] as $codigo => $bloque) {
                $cambio = $datos['bloques'][$codigo];
                DB::table('edd_bloques')->where('id', $bloque['id'])->update([
                    'activo' => (bool) $cambio['activo'], 'peso_porcentaje' => $cambio['peso_porcentaje'] ?? null,
                ]);
                $idsPermitidos = array_column($bloque['items'], 'id');
                $idsConservados = [];
                foreach (array_values($cambio['items'] ?? []) as $orden => $item) {
                    $itemId = $item['id'] ?? null;
                    if ($itemId && ! in_array((int) $itemId, $idsPermitidos, true)) {
                        throw ValidationException::withMessages(['bloques.'.$codigo.'.items' => 'Hay un criterio que no pertenece a este bloque. Recargá antes de guardar.']);
                    }
                    $contenido = [
                        'titulo' => $item['titulo'], 'descripcion' => $item['descripcion'] ?? null,
                        'competencia_codigo' => $item['competencia_codigo'] ?? null,
                        'peso_relativo' => $item['peso_relativo'], 'obligatorio' => (bool) $item['obligatorio'], 'orden' => $orden + 1,
                    ];
                    if ($itemId) {
                        DB::table('edd_items')->where('id', $itemId)->where('bloque_id', $bloque['id'])->update($contenido);
                    } else {
                        $itemId = DB::table('edd_items')->insertGetId(array_merge($contenido, [
                            'bloque_id' => $bloque['id'], 'codigo' => (string) Str::uuid(),
                            'tipo' => match ($codigo) {
                                'generales', 'especificas' => 'competencia',
                                'conductas' => 'conducta', 'objetivos' => 'objetivo', default => 'desempeno',
                            },
                            'permite_no_aplica' => false,
                        ]));
                    }
                    $idsConservados[] = (int) $itemId;
                }
                DB::table('edd_items')->where('bloque_id', $bloque['id'])->whereNotIn('id', $idsConservados)->delete();
            }

            DB::table('edd_instrumentos')->where('id', $id)->update([
                'nombre' => $datos['nombre'], 'revision' => $actual->revision + 1, 'updated_by' => $actor, 'updated_at' => $this->ahora(),
            ]);
            $resultado = $this->instrumento($periodo, $id);
            $this->evento($periodo, 'instrumento', $id, 'guardar_instrumento', $actor, $antes, $resultado);

            return $resultado;
        });
    }

    public function publicarInstrumento(int $periodo, int $id, int $revision, int $actor): array
    {
        return DB::transaction(function () use ($periodo, $id, $revision, $actor) {
            $this->bloquearPeriodo($periodo);
            $actual = $this->bloquearInstrumento($periodo, $id, $revision);
            abort_unless($actual->estado === 'borrador', 409, 'El instrumento ya está publicado. Recargá la página.');
            $antes = $this->instrumento($periodo, $id);
            $errores = $this->erroresPublicacion($antes);
            if ($errores) {
                throw ValidationException::withMessages($errores);
            }
            DB::table('edd_instrumentos')->where('id', $id)->update([
                'estado' => 'publicado', 'revision' => $actual->revision + 1,
                'publicado_by' => $actor, 'publicado_at' => $this->ahora(), 'updated_by' => $actor, 'updated_at' => $this->ahora(),
            ]);
            $resultado = $this->instrumento($periodo, $id);
            $this->evento($periodo, 'instrumento', $id, 'publicar_instrumento', $actor, $antes, $resultado);

            return $resultado;
        });
    }

    public function nuevaVersion(int $periodo, int $id, int $revision, int $actor): array
    {
        try {
            return DB::transaction(function () use ($periodo, $id, $revision, $actor) {
                $this->bloquearPeriodo($periodo);
                $origen = $this->bloquearInstrumento($periodo, $id, $revision);
                abort_unless($origen->estado === 'publicado', 409, 'La nueva versión se crea desde un instrumento publicado.');
                if (DB::table('edd_instrumentos')->where('codigo', $origen->codigo)->where('estado', 'borrador')->exists()) {
                    throw ValidationException::withMessages(['instrumento' => 'Ya existe una versión en borrador de este instrumento. Continuá editando ese borrador.']);
                }
                $antes = $this->instrumento($periodo, $id);
                $nuevoId = DB::table('edd_instrumentos')->insertGetId([
                    'codigo' => $origen->codigo, 'nombre' => $origen->nombre,
                    'version' => DB::table('edd_instrumentos')->where('codigo', $origen->codigo)->max('version') + 1,
                    'estado' => 'borrador', 'revision' => 0, 'escala_json' => $origen->escala_json,
                    'reglas_calculo_json' => $origen->reglas_calculo_json,
                    'created_by' => $actor, 'updated_by' => $actor, 'created_at' => $this->ahora(), 'updated_at' => $this->ahora(),
                ]);
                foreach ($antes['bloques'] as $bloque) {
                    $items = $bloque['items'];
                    unset($bloque['id'], $bloque['items']);
                    $bloque['instrumento_id'] = $nuevoId;
                    $bloqueId = DB::table('edd_bloques')->insertGetId($bloque);
                    foreach ($items as $item) {
                        unset($item['id']);
                        $item['bloque_id'] = $bloqueId;
                        DB::table('edd_items')->insert($item);
                    }
                }
                DB::table('edd_periodo_instrumentos')->insert(['periodo_id' => $periodo, 'instrumento_id' => $nuevoId]);
                $resultado = $this->instrumento($periodo, $nuevoId);
                $this->evento($periodo, 'instrumento', $nuevoId, 'crear_version_instrumento', $actor, $antes, $resultado);

                return $resultado;
            });
        } catch (UniqueConstraintViolationException) {
            abort(409, 'Otra sesión creó una versión. Recargá para continuar.');
        }
    }

    public function erroresPublicacion(array $instrumento): array
    {
        $errores = [];
        $total = 0;
        $activos = 0;
        foreach ($instrumento['bloques'] as $codigo => $bloque) {
            if (! $bloque['activo']) {
                continue;
            }
            $activos++;
            $peso = $this->centesimas($bloque['peso_porcentaje']);
            if ($peso <= 0) {
                $errores['bloques.'.$codigo.'.peso_porcentaje'] = 'Definí un peso mayor a cero para '.$bloque['nombre'].'.';
            }
            $total += $peso;
            if (! $bloque['items']) {
                $errores['bloques.'.$codigo.'.items'] = 'Agregá al menos un criterio a '.$bloque['nombre'].'.';
            }
        }
        if ($activos === 0 || $total !== 10000) {
            $errores['bloques'] = 'Los pesos de los bloques activos deben sumar exactamente 100 %.';
        }

        return $errores;
    }

    private function centesimas(?string $numero): int
    {
        [$entero, $decimales] = array_pad(explode('.', $numero ?? '0'), 2, '');

        return (int) $entero * 100 + (int) str_pad($decimales, 2, '0');
    }

    private function datosPeriodo(array $datos): array
    {
        $resultado = array_intersect_key($datos, array_flip(['codigo', 'nombre', 'anio']));
        foreach (self::FECHAS as $campo) {
            $resultado[$campo] = $datos[$campo] ?? null;
        }
        $resultado['reglas_json'] = $this->json([
            'autoevaluacion' => $datos['autoevaluacion'] ?? null,
            'visibilidad_autoevaluacion' => $datos['visibilidad_autoevaluacion'] ?? null,
            'condiciones_cierre' => $datos['condiciones_cierre'] ?? null,
            'excepciones' => $datos['excepciones'] ?? null,
        ]);

        return $resultado;
    }

    private function presentarPeriodo(object $periodo): array
    {
        return array_merge((array) $periodo, ['reglas' => json_decode($periodo->reglas_json, true, flags: JSON_THROW_ON_ERROR)]);
    }

    public function bloquearPeriodo(int $id): object
    {
        $periodo = DB::table('edd_periodos')->where('id', $id)->lockForUpdate()->first();
        abort_unless($periodo, 404);
        abort_unless($periodo->estado === 'borrador', 409, 'El período ya no está en borrador. Recargá para consultar su estado.');

        return $periodo;
    }

    private function bloquearInstrumento(int $periodo, int $id, int $revision): object
    {
        $this->verificarVinculo($periodo, $id);
        $instrumento = DB::table('edd_instrumentos')->where('id', $id)->lockForUpdate()->first();
        abort_unless($instrumento, 404);
        $this->verificarRevision($instrumento->revision, $revision);

        return $instrumento;
    }

    private function verificarVinculo(int $periodo, int $instrumento): void
    {
        abort_unless(DB::table('edd_periodo_instrumentos')->where('periodo_id', $periodo)->where('instrumento_id', $instrumento)->exists(), 404);
    }

    public function verificarRevision(int $actual, int $recibida): void
    {
        abort_if($actual !== $recibida, 409, 'Otra sesión modificó esta configuración. Tus cambios siguen en pantalla; revisá la versión guardada antes de volver a editar.');
    }

    public function evento(int $periodo, string $tipo, int $id, string $accion, int $actor, ?array $antes, array $despues): void
    {
        DB::table('edd_eventos')->insert([
            'periodo_id' => $periodo, 'entidad_tipo' => $tipo, 'entidad_id' => $id, 'accion' => $accion,
            'actor_user_id' => $actor, 'before_json' => $antes === null ? null : $this->json($antes),
            'after_json' => $this->json($despues), 'request_id' => (string) Str::uuid(), 'created_at' => $this->ahora(),
        ]);
    }

    private function json(array $datos): string
    {
        return json_encode($datos, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function ahora(): string
    {
        return CarbonImmutable::now('UTC')->format('Y-m-d H:i:s');
    }
}

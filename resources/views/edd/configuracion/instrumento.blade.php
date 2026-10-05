@extends('edd.configuracion.layout')

@section('edd-configuracion')
    @if(!$periodo)
        <section class="edd-panel edd-empty"><strong>Guardá primero el período.</strong><p>Después podrás crear uno o varios instrumentos según los puestos que participen.</p><a class="btn btn-primary mt-3" href="{{ route('rrhh.edd.configuracion', ['nuevo' => 1]) }}">Configurar período</a></section>
    @else
        <section class="edd-panel mb-4">
            <div class="edd-panel-header">
                <h3 class="h6">Instrumentos de {{ $periodo['nombre'] }}</h3>
                @if($periodo['estado'] === 'borrador')<a class="btn btn-sm btn-outline-primary" href="{{ route('rrhh.edd.configuracion.instrumento', ['periodo' => $periodo['id'], 'nuevo_instrumento' => 1]) }}">Nuevo instrumento</a>@endif
            </div>
            <nav class="edd-subnav mb-0" aria-label="Instrumentos del período">
                @forelse($instrumentos as $opcion)
                    <a href="{{ route('rrhh.edd.configuracion.instrumento', ['periodo' => $periodo['id'], 'instrumento' => $opcion->id]) }}" @if(($instrumento['id'] ?? null) === $opcion->id) aria-current="page" @endif>{{ $opcion->nombre }} · v{{ $opcion->version }} · {{ ucfirst($opcion->estado) }}</a>
                @empty
                    <p class="edd-muted mb-0">Todavía no hay instrumentos guardados en este período.</p>
                @endforelse
            </nav>
        </section>

        @if(!$instrumento)
            <section class="edd-panel">
                <h3 class="h6">Crear instrumento</h3>
                <p class="edd-muted">Se creará un borrador con los cinco bloques y la escala 1–4. Podrás definir criterios y ponderaciones antes de publicarlo.</p>
                <form method="post" action="{{ route('rrhh.edd.instrumentos.store', $periodo['id']) }}" data-edd-form>
                    @csrf
                    <fieldset @disabled($periodo['estado'] !== 'borrador')>
                        <legend class="visually-hidden">Identificación del instrumento</legend>
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label" for="edd-instrumento-codigo">Código *</label><input class="form-control" id="edd-instrumento-codigo" name="codigo" required maxlength="40" placeholder="Ej.: EDD-ADMIN"></div>
                            <div class="col-md-8"><label class="form-label" for="edd-instrumento-nombre">Nombre *</label><input class="form-control" id="edd-instrumento-nombre" name="nombre" required maxlength="160" placeholder="Ej.: Administración"></div>
                        </div>
                        <button class="btn btn-primary mt-3" type="submit" data-edd-guardar disabled>Crear instrumento en borrador</button>
                    </fieldset>
                    @include('edd.configuracion.estado-guardado')
                </form>
            </section>
        @else
            @php($editable = $instrumento['estado'] === 'borrador' && $periodo['estado'] === 'borrador')
            <section class="edd-panel">
                <div class="edd-panel-header">
                    <h3 class="h6">{{ $instrumento['codigo'] }} · Versión {{ $instrumento['version'] }}</h3>
                    <span class="badge {{ $instrumento['estado'] === 'publicado' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ ucfirst($instrumento['estado']) }}</span>
                </div>
                @if(!$editable)<p class="edd-notice">Esta versión es de consulta. Para cambiar el instrumento, creá una nueva versión.</p>@endif
                <form method="post" action="{{ route('rrhh.edd.instrumentos.update', ['periodo' => $periodo['id'], 'instrumento' => $instrumento['id']]) }}" data-edd-form data-edd-instrumento>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="revision" value="{{ $instrumento['revision'] }}">
                    <fieldset @disabled(!$editable)>
                        <legend class="visually-hidden">Criterios y ponderaciones del instrumento</legend>
                        <label class="form-label" for="edd-instrumento-nombre">Nombre *</label>
                        <input class="form-control mb-3" id="edd-instrumento-nombre" name="nombre" value="{{ $instrumento['nombre'] }}" required maxlength="160">
                        <p class="edd-muted small">Los bloques activos deben sumar 100 % para publicar. El peso relativo distribuye el puntaje dentro de cada bloque; con todos los pesos en 1, sus criterios tienen la misma importancia.</p>
                        <p class="fw-semibold" data-edd-total-pesos role="status" aria-live="polite"></p>
                        @foreach($instrumento['bloques'] as $codigo => $bloque)
                            <section class="border rounded p-3 mb-3" data-edd-bloque data-codigo="{{ $codigo }}" data-siguiente="{{ count($bloque['items']) }}">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-8">
                                        <input type="hidden" name="bloques[{{ $codigo }}][activo]" value="0">
                                        <div class="form-check"><input class="form-check-input" id="edd-bloque-{{ $codigo }}" type="checkbox" name="bloques[{{ $codigo }}][activo]" value="1" @checked($bloque['activo']) data-edd-activo><label class="form-check-label fw-semibold" for="edd-bloque-{{ $codigo }}">{{ $bloque['nombre'] }}</label></div>
                                        <p class="small edd-muted mt-1 mb-0">{{ $bloque['descripcion'] }}</p>
                                    </div>
                                    <div class="col-md-4"><label class="form-label" for="edd-peso-{{ $codigo }}">Ponderación (%)</label><input class="form-control" type="number" step="0.01" min="0" max="100" id="edd-peso-{{ $codigo }}" name="bloques[{{ $codigo }}][peso_porcentaje]" value="{{ $bloque['peso_porcentaje'] }}" data-edd-peso></div>
                                </div>
                                <p class="small edd-muted mt-2 mb-0">Al desactivar un bloque, se conservan sus criterios y se excluye su peso de la publicación.</p>
                                <div data-edd-items>
                                    @foreach($bloque['items'] as $indice => $item)
                                        @include('edd.configuracion.item', compact('codigo', 'indice', 'item'))
                                    @endforeach
                                </div>
                                <button class="btn btn-sm btn-outline-primary mt-3" type="button" data-edd-agregar>Agregar criterio</button>
                                <button class="btn btn-sm btn-outline-secondary mt-3" type="button" data-edd-deshacer hidden>Deshacer última eliminación</button>
                                <template data-edd-item-template>
                                    @include('edd.configuracion.item', ['codigo' => $codigo, 'indice' => '__INDICE__', 'item' => []])
                                </template>
                            </section>
                        @endforeach
                        <input type="hidden" name="formulario_completo" value="1">
                        @if($editable)<button class="btn btn-primary" type="submit" data-edd-guardar disabled>Guardar borrador del instrumento</button>@endif
                    </fieldset>
                    @include('edd.configuracion.estado-guardado')
                </form>
            </section>

            <div class="row g-4 mt-1">
                <div class="col-lg-6">
                    <section class="edd-panel">
                        <h3 class="h6">Escala de esta versión</h3>
                        <dl class="mb-0">
                            @foreach($escala as $valor => $nivel)<dt class="mt-3">{{ $valor }}. {{ $nivel['nombre'] }}</dt><dd class="small edd-muted mb-0">{{ $nivel['descripcion'] }}</dd>@endforeach
                        </dl>
                    </section>
                </div>
                <div class="col-lg-6">
                    <section class="edd-panel">
                        @if($editable)
                            <h3 class="h6">Publicar instrumento</h3>
                            <p class="edd-muted">La publicación utiliza el borrador guardado y protege esta versión contra cambios. El período seguirá en configuración.</p>
                            <p class="small text-warning-emphasis" data-edd-publicacion-pendiente hidden>Guardá los cambios del instrumento antes de publicar.</p>
                            @if($erroresPublicacion)
                                <p class="small fw-semibold">Pendiente en la versión guardada:</p><ul class="small edd-muted">@foreach($erroresPublicacion as $mensaje)<li>{{ $mensaje }}</li>@endforeach</ul>
                            @endif
                            <form method="post" action="{{ route('rrhh.edd.instrumentos.publicar', ['periodo' => $periodo['id'], 'instrumento' => $instrumento['id']]) }}" data-edd-form data-edd-publicar>
                                @csrf
                                <input type="hidden" name="revision" value="{{ $instrumento['revision'] }}">
                                <div class="form-check mb-3"><input type="checkbox" class="form-check-input" name="confirmar" value="1" required id="edd-confirmar-publicacion"><label class="form-check-label" for="edd-confirmar-publicacion">Revisé los criterios y pesos de esta versión.</label></div>
                                <button class="btn btn-primary" type="submit" data-edd-guardar disabled>Publicar versión {{ $instrumento['version'] }}</button>
                                @include('edd.configuracion.estado-guardado')
                            </form>
                        @elseif($periodo['estado'] === 'borrador' && $instrumento['estado'] === 'publicado')
                            <h3 class="h6">Nueva versión</h3>
                            <p class="edd-muted">Se copiarán los bloques, criterios y escala en un nuevo borrador. La versión {{ $instrumento['version'] }} se conservará publicada.</p>
                            <form method="post" action="{{ route('rrhh.edd.instrumentos.versiones', ['periodo' => $periodo['id'], 'instrumento' => $instrumento['id']]) }}" data-edd-form>
                                @csrf
                                <input type="hidden" name="revision" value="{{ $instrumento['revision'] }}">
                                <button class="btn btn-outline-primary" type="submit" data-edd-guardar disabled>Crear nueva versión</button>
                                @include('edd.configuracion.estado-guardado')
                            </form>
                        @else
                            <p class="edd-muted mb-0">El período ya no permite editar su configuración.</p>
                        @endif
                    </section>
                </div>
            </div>
        @endif
    @endif
@endsection

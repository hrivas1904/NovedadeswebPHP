<fieldset disabled aria-describedby="edd-disponibilidad">
    <legend>Puntajes y comentarios {{ $tipoRespuesta === 'autoevaluacion' ? 'del colaborador' : 'del evaluador' }}</legend>
    <div class="row g-3">
        @foreach($bloques as $codigo => $bloque)
            <div class="col-12">
                <div class="border rounded p-3">
                    <h3 class="h6">{{ $bloque['nombre'] }}</h3>
                    <p class="small edd-muted">{{ $bloque['descripcion'] }} Los ítems se mostrarán según el instrumento asignado.</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="{{ $tipoRespuesta }}-puntaje-{{ $codigo }}">Puntaje del ítem</label>
                            <select class="form-select" id="{{ $tipoRespuesta }}-puntaje-{{ $codigo }}">
                                <option value="">Sin responder</option>
                                @foreach($escala as $valor => $nivel)<option value="{{ $valor }}">{{ $valor }}. {{ $nivel['nombre'] }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-8"><label class="form-label" for="{{ $tipoRespuesta }}-comentario-{{ $codigo }}">Comentario y evidencia</label><textarea class="form-control" rows="2" id="{{ $tipoRespuesta }}-comentario-{{ $codigo }}"></textarea></div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-12"><label class="form-label" for="{{ $tipoRespuesta }}-comentario-general">Comentario general</label><textarea class="form-control" rows="3" id="{{ $tipoRespuesta }}-comentario-general"></textarea></div>
    </div>
</fieldset>

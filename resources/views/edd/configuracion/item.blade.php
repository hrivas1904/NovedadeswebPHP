@php($prefijo = 'bloques['.$codigo.'][items]['.$indice.']')
@php($campoId = 'edd-item-'.$codigo.'-'.$indice)
<div class="edd-item border rounded p-3 mt-3" data-edd-item>
    <input type="hidden" name="{{ $prefijo }}[id]" value="{{ $item['id'] ?? '' }}">
    <div class="d-flex justify-content-between gap-3 align-items-center mb-3">
        <h4 class="h6 mb-0">Criterio</h4>
        <button class="btn btn-sm btn-outline-danger" type="button" data-edd-quitar>Quitar criterio</button>
    </div>
    <div class="row g-3">
        <div class="col-md-8"><label class="form-label" for="{{ $campoId }}-titulo">Criterio / objetivo *</label><input class="form-control" id="{{ $campoId }}-titulo" name="{{ $prefijo }}[titulo]" value="{{ $item['titulo'] ?? '' }}" maxlength="200" required></div>
        <div class="col-md-4"><label class="form-label" for="{{ $campoId }}-peso">Peso relativo *</label><input class="form-control" type="number" id="{{ $campoId }}-peso" name="{{ $prefijo }}[peso_relativo]" value="{{ $item['peso_relativo'] ?? 1 }}" min="0.01" max="1000" step="0.01" required></div>
        <div class="col-12"><label class="form-label" for="{{ $campoId }}-descripcion">Descripción y comportamiento esperado</label><textarea class="form-control" rows="2" id="{{ $campoId }}-descripcion" name="{{ $prefijo }}[descripcion]" maxlength="2000">{{ $item['descripcion'] ?? '' }}</textarea></div>
        <div class="col-md-7"><label class="form-label" for="{{ $campoId }}-codigo">Código de competencia (opcional)</label><input class="form-control" id="{{ $campoId }}-codigo" name="{{ $prefijo }}[competencia_codigo]" value="{{ $item['competencia_codigo'] ?? '' }}" maxlength="60" placeholder="Ej.: TRABAJO_EQUIPO"></div>
        <div class="col-md-5 d-flex align-items-end pb-2">
            <input type="hidden" name="{{ $prefijo }}[obligatorio]" value="0">
            <div class="form-check"><input class="form-check-input" type="checkbox" id="{{ $campoId }}-obligatorio" name="{{ $prefijo }}[obligatorio]" value="1" @checked($item['obligatorio'] ?? true)><label class="form-check-label" for="{{ $campoId }}-obligatorio">Respuesta obligatoria</label></div>
        </div>
    </div>
</div>

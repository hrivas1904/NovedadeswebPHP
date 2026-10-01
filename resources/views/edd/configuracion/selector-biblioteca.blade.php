<div class="row g-2 align-items-end mb-3">
    <div class="col-md-7">
        <label class="form-label" for="edd-catalogo">Tomar competencias de una lista</label>
        <select class="form-select" id="edd-catalogo">
            <option value="">Seleccioná una lista</option>
            @foreach($catalogo as $codigo => $plantilla)<option value="{{ $codigo }}">{{ $plantilla['nombre'] }}</option>@endforeach
        </select>
    </div>
    <div class="col-auto"><button type="button" class="btn btn-outline-primary" data-edd-cargar-base>Usar esta lista</button></div>
    <div class="col-auto"><button type="button" class="btn btn-outline-secondary" data-edd-deshacer-base hidden>Deshacer reemplazo</button></div>
</div>
<p class="small edd-muted">Copia la lista al texto de abajo. Podés modificarla antes de guardar. Las modificaciones posteriores de la biblioteca no cambian las copias ya aplicadas.</p>
@foreach($catalogo as $codigo => $plantilla)<template data-edd-catalogo="{{ $codigo }}"><textarea>{{ implode("\n", $plantilla['competencias']) }}</textarea></template>@endforeach

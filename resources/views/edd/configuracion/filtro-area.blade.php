<form method="get" class="row g-2 mb-3 align-items-end">
    <input type="hidden" name="periodo" value="{{ $periodo['id'] }}">
    <div class="col-md-5">
        <label for="edd-filtro-area" class="form-label">Área</label>
        <select class="form-select" id="edd-filtro-area" name="area">
            <option value="">Todas las áreas</option>
            @foreach($areas as $area)<option value="{{ $area->ID_AREA }}" @selected(($filtros['area'] ?? '') == $area->ID_AREA)>{{ $area->NOMBRE }} · #{{ $area->ID_AREA }}</option>@endforeach
        </select>
    </div>
    @if($buscar ?? false)
        <div class="col-md-5"><label for="edd-filtro-buscar" class="form-label">Buscar candidatos por nombre o legajo</label><input id="edd-filtro-buscar" class="form-control" name="q" value="{{ $filtros['q'] ?? '' }}" maxlength="100"></div>
    @endif
    <div class="col-auto"><button class="btn btn-outline-secondary" type="submit">Filtrar</button></div>
</form>

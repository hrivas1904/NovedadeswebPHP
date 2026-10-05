@if($persona['nomina'])
    <span>{{ $persona['nomina']['rol_nombre'] ?? 'Rol sin informar' }}</span><br>
    <span class="small edd-muted">{{ $persona['nomina']['servicio_nombre'] ?? 'Servicio sin informar' }}</span>
@else
    <span class="text-warning-emphasis">Legajo inexistente o duplicado en la nómina</span>
@endif

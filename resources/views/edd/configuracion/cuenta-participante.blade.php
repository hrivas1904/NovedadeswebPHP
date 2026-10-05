@if((int) ($persona['cuentas_activas'] ?? 0) === 1)
    <span class="badge text-bg-success">Cuenta vinculada</span>
@elseif((int) ($persona['cuentas_activas'] ?? 0) > 1)
    <span class="badge text-bg-warning">Más de una cuenta activa</span>
@else
    <span class="badge text-bg-warning">Sin cuenta activa</span>
@endif

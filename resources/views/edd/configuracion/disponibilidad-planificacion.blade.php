@include('edd.configuracion.disponibilidad-poblacion')
@if($poblacionDisponible && !$planificacionDisponible)
    <p class="alert alert-warning">La administración del sistema debe instalar la actualización de bibliotecas y evaluadores por área para habilitar este panel.</p>
@endif

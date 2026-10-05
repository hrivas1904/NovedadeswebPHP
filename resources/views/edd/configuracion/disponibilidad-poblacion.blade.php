@if(!$poblacionDisponible)
    <p class="alert alert-warning">La configuración de población todavía no está instalada. La administración del sistema debe completar su instalación.</p>
@elseif(!$periodo)
    <p class="edd-notice">Creá o seleccioná un período para configurar la población, las competencias y los evaluadores.</p>
@elseif($periodo['estado'] !== 'borrador')
    <p class="edd-notice">Este período está en consulta. Su configuración ya no se puede modificar.</p>
@endif

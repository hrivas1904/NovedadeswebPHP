@extends('edd.configuracion.layout')

@section('edd-configuracion')
    <section class="edd-panel">
        <h3 class="h6">Colaboradores activos al corte</h3>
        <p class="edd-muted">La población del período conservará el legajo, área, servicio, rol y descriptivo de cada colaborador. Las inclusiones y exclusiones excepcionales deberán registrar un motivo.</p>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th scope="col">Legajo</th><th scope="col">Colaborador</th><th scope="col">Área / servicio</th><th scope="col">Rol / puesto</th><th scope="col">Descriptivo</th><th scope="col">Inclusión</th></tr></thead>
                <tbody><tr><td colspan="6"><div class="edd-empty"><strong>No hay población definida.</strong><p>La nómina se incorporará después de configurar la fecha de corte del período.</p></div></td></tr></tbody>
            </table>
        </div>
    </section>
@endsection

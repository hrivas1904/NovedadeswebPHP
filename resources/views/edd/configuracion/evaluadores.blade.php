@extends('edd.configuracion.layout')

@section('edd-configuracion')
    <section class="edd-panel">
        <h3 class="h6">Quién evalúa a quién</h3>
        <p class="edd-muted">RRHH asignará un jefe, coordinador, responsable o gerente a cada evaluación. El responsable verá directamente las personas asignadas a su cuenta.</p>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th scope="col">Colaborador</th><th scope="col">Evaluador responsable</th><th scope="col">Función</th><th scope="col">Equipo</th><th scope="col">Vigencia / excepción</th></tr></thead>
                <tbody><tr><td colspan="5"><div class="edd-empty"><strong>No hay evaluadores asignados.</strong><p>Las asignaciones y sus cambios quedarán asociados al período.</p></div></td></tr></tbody>
            </table>
        </div>
    </section>
@endsection

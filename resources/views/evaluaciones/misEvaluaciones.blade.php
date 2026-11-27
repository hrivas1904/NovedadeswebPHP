@extends('layouts.app')

@section('title', 'Mis evaluaciones')

@section('content')
<nav aria-label="breadcrumb" class="hp3c-breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ url('/') }}"><i class="fa-solid fa-house me-1"></i> Inicio</a>
        </li>
        <li class="breadcrumb-item">Recursos Humanos</li>
        <li class="breadcrumb-item">Colaboradores</li>
        <li class="breadcrumb-item active" aria-current="page">Mis evaluaciones de desempeño</li>
    </ol>
</nav>

<div class="d-flex flex-column gap-3">
    <h3 class="tituloVista mb-0">Mis evaluaciones de desempeño</h3>

    <div class="card p-1">
        <div class="table-card">
            <table class="table table-striped table-hover align-middle w-100" id="tbMisEvaluaciones">
                <thead>
                    <tr>
                        <th>PERÍODO</th>
                        <th>PUESTO</th>
                        <th>ÁREA</th>
                        <th>EVALUADOR</th>
                        <th>ESTADO</th>
                        <th>EVALUACIÓN FINAL</th>
                        <th>ACCIÓN</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('modals')
@endpush

@push('scripts')
<script>
    const EDD_MIS = {
        estados: @json(config('edd.estados')),
        escala:  @json(config('edd.escala')),
        routes: {
            listar:     @json(route('rrhh.edd.misEvaluaciones.listar')),
            formulario: @json(route('rrhh.edd.formularioEvaluacion', ':id')),
        },
    };
</script>
<script src="{{ asset('js/evaluaciones/misEvaluaciones.js') }}"></script>
@endpush
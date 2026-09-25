@extends('edd.configuracion.layout')

@section('edd-configuracion')
    <div class="row g-4">
        <div class="col-xl-7">
            <section class="edd-panel">
                <h3 class="h6">Bloques del instrumento</h3>
                <p class="edd-muted small">RRHH definirá criterios y ponderaciones según el puesto. Los pesos de los bloques activos deberán sumar 100 %.</p>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th scope="col">Bloque</th><th scope="col">Contenido</th><th scope="col">Peso</th></tr></thead>
                        <tbody>
                            @foreach($bloques as $bloque)
                                <tr><td class="fw-semibold">{{ $bloque['nombre'] }}</td><td class="small">{{ $bloque['descripcion'] }}</td><td class="text-nowrap edd-muted">A definir</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <a class="btn btn-outline-primary btn-sm mt-3" href="{{ route('rrhh.edd.evaluacion.modelo') }}">Ver estructura del formulario</a>
            </section>
        </div>
        <div class="col-xl-5">
            <section class="edd-panel">
                <h3 class="h6">Escala de evaluación</h3>
                <p class="edd-muted small">Escala confirmada para {{ $periodoReferencia }}.</p>
                <dl class="mb-0">
                    @foreach($escala as $valor => $nivel)
                        <dt class="mt-3">{{ $valor }}. {{ $nivel['nombre'] }}</dt>
                        <dd class="small edd-muted mb-0">{{ $nivel['descripcion'] }}</dd>
                    @endforeach
                </dl>
            </section>
        </div>
    </div>
@endsection

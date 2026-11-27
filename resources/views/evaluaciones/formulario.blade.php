@extends("layouts.app")

@section("content")
<div class="d-flex flex-column gap-3" id="eddFormulario">

    {{-- ============ ENCABEZADO ============ --}}
    <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0" style="color: var(--color-default);">
                EVALUACIÓN DE DESEMPEÑO {{ $edd->periodo }}
            </h5>
            <span class="badge {{ $estadoBadge['class'] }}">{{ $estadoBadge['label'] }}</span>
        </div>
        <div class="row g-2">
            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                <label class="label-control text-muted">Evaluado</label>
                <input class="form-control" type="text" value="{{ $edd->evaluado_nombre_snap }}" readonly>
            </div>
            <div class="col-4 col-md-2 col-lg-2 col-xl-1">
                <label class="label-control text-muted">Legajo</label>
                <input class="form-control" type="text" value="{{ $edd->evaluado_legajo_snap }}" readonly>
            </div>
            <div class="col-8 col-md-4 col-lg-3 col-xl-2">
                <label class="label-control text-muted">Área</label>
                <input class="form-control" type="text" value="{{ $edd->area_snap }}" readonly>
            </div>
            <div class="col-12 col-md-4 col-lg-3 col-xl-2">
                <label class="label-control text-muted">Servicio</label>
                <input class="form-control" type="text" value="{{ $edd->servicio_snap }}" readonly>
            </div>
            <div class="col-12 col-md-4 col-lg-4 col-xl-4">
                <label class="label-control text-muted">Puesto de trabajo</label>
                <input class="form-control" type="text" value="{{ $edd->puesto_snap }}" readonly>
            </div>
            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                <label class="label-control text-muted">Evaluador</label>
                <input class="form-control" type="text" value="{{ $edd->evaluador_nombre_snap }}" readonly>
            </div>
            <div class="col-12 col-md-6 col-lg-4 col-xl-4">
                <label class="label-control text-muted">Puesto del evaluador</label>
                <input class="form-control" type="text" value="{{ $edd->evaluador_puesto_snap }}" readonly>
            </div>
        </div>
    </div>

    {{-- ============ ESCALA (colapsable) ============ --}}
    <div class="card p-3">
        <a class="fw-bold text-decoration-none" data-bs-toggle="collapse" href="#collapseEscala" role="button">
            <i class="fa-solid fa-circle-info me-1"></i> Escala de evaluación
        </a>
        <div class="collapse mt-2" id="collapseEscala">
            <table class="table table-sm mb-0">
                <tbody>
                    @foreach (config('edd.escala') as $valor => $item)
                    <tr>
                        <td class="fw-bold text-nowrap">{{ $valor }}. {{ mb_strtoupper($item['label']) }}</td>
                        <td>{{ $item['concepto'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============ EVALUACIÓN ============ --}}
    <div class="card p-3">
        <div class="d-flex flex-column gap-3">
            <h5 class="fw-bold" style="color: var(--color-default);">EVALUACIÓN</h5>

            <h6 class="fw-bold text-muted">Competencias organizacionales</h6>
            <div class="table-card">
                <table class="table table-striped table-hover align-middle" id="tbCompetenciasOrg">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>COMPETENCIA</th>
                            <th>AUTOEVALUACIÓN</th>
                            <th>COMENTARIOS EVALUADO</th>
                            <th>EVALUACIÓN</th>
                            <th>COMENTARIOS EVALUADOR</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <h6 class="fw-bold text-muted">Competencias funcionales</h6>
            <div class="table-card">
                <table class="table table-striped table-hover align-middle" id="tbCompetenciasFunc">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>COMPETENCIA</th>
                            <th>AUTOEVALUACIÓN</th>
                            <th>COMENTARIOS EVALUADO</th>
                            <th>EVALUACIÓN</th>
                            <th>COMENTARIOS EVALUADOR</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- ============ RESULTADOS ============ --}}
    <div class="card p-3">
        <div class="d-flex flex-column gap-3">
            <h5 class="fw-bold" style="color: var(--color-default);">RESULTADOS</h5>
            <div class="table-card">
                <table class="table table-sm align-middle mb-0" id="tbResultados">
                    <thead>
                        <tr>
                            <th>PROMEDIOS</th>
                            <th class="text-center">AUTOEVALUACIÓN</th>
                            <th class="text-center">EVALUACIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Competencias organizacionales</td>
                            <td class="text-center" id="promAutoOrg">-</td>
                            <td class="text-center" id="promSupOrg">-</td>
                        </tr>
                        <tr>
                            <td>Competencias funcionales</td>
                            <td class="text-center" id="promAutoFunc">-</td>
                            <td class="text-center" id="promSupFunc">-</td>
                        </tr>
                        <tr class="fw-bold">
                            <td>Todas las competencias</td>
                            <td class="text-center" id="promAutoGral">-</td>
                            <td class="text-center" id="promSupGral">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <h5 class="fw-bold mb-0" style="color: var(--color-default);">
                Evaluación final: <span id="lblEvaluacionFinal">-</span>
                <small class="text-muted fw-normal ms-2" id="lblEvaluacionFinalConcepto"></small>
            </h5>
        </div>
    </div>

    {{-- ============ RETROALIMENTACIÓN (la escribe el evaluador) ============ --}}
    <div class="card p-3">
        <div class="d-flex flex-column gap-1">
            <h5 class="fw-bold" style="color: var(--color-default);">
                RETROALIMENTACIÓN - LOGROS Y OPORTUNIDADES DE MEJORA - DETECCIÓN DE NECESIDADES DE CAPACITACIÓN
            </h5>
            <p class="text-muted fst-italic">
                La retroalimentación es una etapa clave para el desarrollo profesional. Ayuda a identificar fortalezas y áreas de mejora, alinea el desempeño con objetivos y fomenta la mejora continua. Una comunicación clara y constructiva motiva, fortalece la confianza y potencia el éxito individual y colectivo.
            </p>
            <textarea class="form-control" id="inputRetroalimentacion" rows="4"
                @readonly($modo !=='SUPERIOR' )>{{ $edd->retroalimentacion }}</textarea>
        </div>
    </div>

    {{-- ============ ACCIONES (según modo) ============ --}}
    @if ($modo === 'AUTO')
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary" id="btnGuardarAuto">
            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar borrador
        </button>
        <button type="button" class="btn btn-primary" id="btnEnviarAuto">
            <i class="fa-solid fa-paper-plane me-1"></i> Enviar autoevaluación
        </button>
    </div>
    @elseif ($modo === 'SUPERIOR')
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary" id="btnGuardarSuperior">
            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar borrador
        </button>
        <button type="button" class="btn btn-primary" id="btnEnviarSuperior">
            <i class="fa-solid fa-file-signature me-1"></i> Enviar y firmar evaluación
        </button>
    </div>
    @endif

    {{-- ============ FIRMAS ============ --}}
    <div class="card p-3">
        <h5 class="fw-bold mb-3" style="color: var(--color-default);">FIRMAS</h5>
        <div class="row g-3">

            {{-- Evaluador: firma al enviar su evaluación --}}
            <div class="col-12 col-md-6">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small mb-1">Evaluador</div>
                    @if ($edd->firma_evaluador_at)
                    <div class="fw-bold text-success">
                        <i class="fa-solid fa-circle-check me-1"></i>{{ $edd->evaluador_nombre_snap }}
                    </div>
                    <div class="small text-muted">
                        Firmado el {{ \Carbon\Carbon::parse($edd->firma_evaluador_at)->format('d/m/Y H:i') }}
                    </div>
                    @else
                    <div class="text-muted">
                        <i class="fa-regular fa-clock me-1"></i>Pendiente: se firma al enviar la evaluación
                    </div>
                    @endif
                </div>
            </div>

            {{-- Evaluado: firma "tomé conocimiento" con observación opcional --}}
            <div class="col-12 col-md-6">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small mb-1">Evaluado</div>
                    @if ($edd->firma_evaluado_at)
                    <div class="fw-bold text-success">
                        <i class="fa-solid fa-circle-check me-1"></i>{{ $edd->evaluado_nombre_snap }}
                    </div>
                    <div class="small text-muted">
                        Tomó conocimiento el {{ \Carbon\Carbon::parse($edd->firma_evaluado_at)->format('d/m/Y H:i') }}
                    </div>
                    @if ($edd->observacion_evaluado)
                    <div class="mt-2 small"><span class="fw-bold">Observación:</span> {{ $edd->observacion_evaluado }}</div>
                    @endif
                    @elseif ($modo === 'FIRMA')
                    <label class="label-control small" for="inputObservacionEvaluado">Observación (opcional)</label>
                    <textarea class="form-control mb-2" id="inputObservacionEvaluado" rows="2"></textarea>
                    <button type="button" class="btn btn-primary w-100" id="btnFirmarEvaluado">
                        <i class="fa-solid fa-file-signature me-1"></i> Firmar: tomé conocimiento
                    </button>
                    @else
                    <div class="text-muted">
                        <i class="fa-regular fa-clock me-1"></i>Pendiente: firma después de la evaluación del superior
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const EDD = {
        id:     {{ $edd->id }},
        modo:   @json($modo),            // AUTO | SUPERIOR | FIRMA | LECTURA
        escala: @json(config('edd.escala')),
        routes: {
            respuestas: @json(route('rrhh.edd.respuestas', $edd->id)),
            guardar:    @json(route('rrhh.edd.guardar',    $edd->id)),
            enviar:     @json(route('rrhh.edd.enviar',     $edd->id)),
            firmar:     @json(route('rrhh.edd.firmar',     $edd->id)),
        },
    };
</script>
<script src="{{ asset('js/evaluaciones/formulario.js') }}"></script>
@endpush
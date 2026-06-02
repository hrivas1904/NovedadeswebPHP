@extends('layouts.app')

@section('title', 'Registro de Novedades')

@section('content')
    <div class="container-fluid">
        <div class="text-start mb-2">
            <div class="d-flex align-items-center justify-content-between mt-2 mb-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="icon-box">
                        <img src="{{ asset('img/icons/novedades-logo.png') }}" style="height: 32px;" alt="Logo novedades">
                    </div>
                    <div>
                        <h3 class="tituloVista mb-0">REGISTRO DE NOVEDADES MENSUALES</h3>
                        <p class="mb-0 text-muted">Carga de novedades próximas a liquidar.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="border-radius:15px;">
            <div class="card-header">
                <div class="row align-items-end g-3">
                    <div class="col-6 col-sm-12 col-md-6 col-lg-1">
                        <input id="filtroDesde" class="form-control" type="text" placeholder="Desde" required>
                    </div>

                    <div class="col-6 col-sm-12 col-md-6 col-lg-1">
                        <input id="filtroHasta" class="form-control" type="text" placeholder="Hasta" required>
                    </div>

                    <div class="col-6 col-sm-12 col-md-6 col-lg-2 d-none d-md-block">
                        @if (in_array(Auth::user()->rol, ['Administrador/a', 'Supervisor/a Calidad']))
                            <select id="area" name="area" class="form-select js-select-area w-100">
                            </select>
                        @endif
                    </div>

                    <div class="col-6 col-sm-12 col-md-6 col-lg-3 d-none d-md-block">
                        <select id="idNovedad" name="idNovedad" class="form-select js-select-novedadFiltro w-100">
                        </select>
                    </div>

                    <div class="col-6 col-sm-12 col-md-6 col-lg-1 d-none d-md-block">
                        <select id="paraFinnegans" name="paraFinnegans"
                            class="form-select js-select-novedadFinnegans w-100">
                            <option value="">Todas</option>
                            <option value="0">Informativas</option>
                            <option value="1">Para Finnegans</option>
                        </select>
                    </div>

                    <div class="col-6 col-sm-12 col-md-6 col-lg-1 col-xl-auto">
                        <button type="button" id="btnLimpiarFiltros" class="btn btn-secondary w-100">
                            <i class="fa-solid fa-eraser"></i> Limpiar
                        </button>
                    </div>

                    @if (Auth::user()->rol == 'Administrador/a' ||
                            Auth::user()->rol == 'Coordinador/a' ||
                            Auth::user()->rol == 'Coordinador/a L2')
                        <div class="col-6 col-sm-12 col-md-6 col-lg-1 col-xl-auto">
                            <button type="button" id="btnCargaMasiva" class="btn btn-primary w-100">
                                <i class="fa-solid fa-database"></i> Carga masiva
                            </button>
                        </div>
                    @endif

                    @if (Auth::user()->rol == 'Administrador/a')
                        <div class="col-6 col-sm-12 col-md-6 col-lg-1 col-xl-auto">
                            <button type="button" id="btnLiquidarNovedadesSelec" class="btn btn-primary w-100">
                                <i class="fa-solid fa-file-invoice-dollar"></i> Liquidar
                            </button>
                        </div>                    
                    
                        <div class="col-6 col-sm-12 col-md-6 col-lg-1 col-xl-auto">
                            <button type="button" id="btnExportExcel" class="btn btn-primary">
                                <i class="fa-solid fa-file-excel"></i> Excel
                            </button>
                        </div>
                    @endif

                </div>
            </div>
            <div class="table-responsive">
                <div class="card-body">
                    <table id="tb_control" class="table table-bordered table-hover align-middle nowrap">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>FECHA</th>
                                <th>AREA</th>
                                <th>REGISTRANTE</th>
                                <th>COLABORADOR</th>
                                <th>LEGAJO</th>
                                <th>CODIGO</th>
                                <th>NOVEDAD</th>
                                <th>CENTROCOSTO</th>
                                <th>CANTIDAD</th>
                                <th>VALOR2</th>
                                <th>FECHAAPLICACION</th>
                                <th>EMPRESA</th>
                                <th>DESDE</th>
                                <th>HASTA</th>
                                <th>VALOR</th>
                                <th>DESCRIPCION</th>
                                <th>AÑO</th>
                                <th>LEGAJO</th>
                                <th>TIPO</th>
                                <th>COLABORADOR</th>
                                <th>CONCEPTO</th>
                                <th class="text-end">
                                    <div class="d-flex align-items-start justify-content-center gap-2">
                                        <span>ACCIONES</span>
                                        @if (Auth::user()->rol === 'Administrador/a')
                                            <input type="checkbox" class="form-check-input" id="checkAll">
                                        @endif
                                    </div>
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <div class="modal fade" id="modalDetalleNovedad" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"
        data-bs-backdrop="static">

        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content p-1">

                <div class="modal-header">
                    <h5 class="modal-title" id="tituloLegajo">
                        <i class="fa-solid fa-id-card me-2"></i> Detalle de Novedad
                    </h5>
                    <button type="button" class="btn-close" onclick="cerrarModalDetalleNovedad()"></button>
                </div>

                <div class="modal-body">
                    <form id="formDetalleNovedad">
                        @csrf
                        <div class="row g-4 mb-4">
                            <div class="col-lg-12">
                                <div class="section-divider mb-3">
                                    <span>Información general</span>
                                </div>
                                <div class="empleado-box p-3">
                                    <div class="row g-3">
                                        <div class="col-lg-3">
                                            <label class="form-label">Registro N°</label>
                                            <input type="text" id="inputRegistro" name="idRegistro" class="form-control"
                                                readonly>
                                        </div>
                                        <div class="col-lg-4">
                                            <label class="form-label">Fecha de registro</label>
                                            <input type="text" id="inputFechaReg" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-5">
                                            <label class="form-label">Registrante</label>
                                            <input type="text" id="inputRegistrante" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="section-divider mb-3">
                                    <span>Datos Personales</span>
                                </div>
                                <div class="empleado-box p-3">
                                    <div class="row g-3">
                                        <div class="col-lg-1">
                                            <label class="form-label">Legajo</label>
                                            <input type="text" id="inputLegajo" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-4">
                                            <label class="form-label">Colaborador</label>
                                            <input type="text" id="inputColaborador" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Estado</label>
                                            <input type="text" id="inputEstado" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">DNI</label>
                                            <input type="text" id="inputDni" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">Área</label>
                                            <input type="text" id="inputArea" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="section-divider mb-3">
                                    <span>Información de la novedad</span>
                                </div>
                                <div class="empleado-box p-3">
                                    <div class="row g-3">
                                        <div class="col-lg-2">
                                            <label class="form-label">Fecha aplicación</label>
                                            <input type="date" id="inputFechaAplicacion" class="form-control editable" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Fecha desde</label>
                                            <input type="date" id="inputFechaDesde" name="fechaDesde"
                                                class="form-control editable" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Fecha hasta</label>
                                            <input type="date" id="inputFechaHasta" name="fechaHasta"
                                                class="form-control editable" readonly>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">Valor</label>
                                            <input type="text" class="d-none" id="tipoValor"></input>
                                            <input type="text" id="inputValor" name="duracion"
                                                class="form-control editable" readonly>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">Código</label>
                                            <input type="text" id="inputCodigo" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-4">
                                            <label class="form-label">Novedad</label>
                                            <select class="form-select" id="inputNovedad" disabled></select>
                                            <input hidden id="inputIdNovedad">
                                        </div>
                                        <div class="col-lg-6">
                                            <label class="form-label">Descripción</label>
                                            <input type="text" id="inputDescripcion" name="descripcion"
                                                class="form-control editable" readonly>
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-2 d-none" id="divInfoVacaciones">
                                        <div class="col-lg-3">
                                            <label class="form-label">Tipo de Vacaciones</label>
                                            <input type="text" id="inputTipoVacaciones" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Año</label>
                                            <input type="text" id="inputAnnioVacaciones" name="annio"
                                                class="form-control editable" readonly>
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-2 d-none" id="divInfoAtencionMedica">
                                        <div class="col-lg-2">
                                            <label class="form-label">N° Atención</label>
                                            <input type="text" id="inputNumAtencion" name='nAtencion'
                                                class="form-control editable" readonly>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">Paciente</label>
                                            <input type="text" id="inputPacienteAtencion" name="paciente"
                                                class="form-control editable" readonly>
                                        </div>
                                        <div class="col-lg-4">
                                            <label class="form-label">Concepto</label>
                                            <input type="text" id="inputConcepto" name="concepto"
                                                class="form-control editable" readonly>
                                        </div>
                                        <div class="col-lg-1">
                                            <label class="form-label">Cuotas</label>
                                            <input type="text" id="inputCuotas" name="cuotas"
                                                class="form-control editable" readonly required title="Horas Diarias">
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Importe cuotas</label>
                                            <input type="text" id="inputImporteCuotas" class="form-control" readonly
                                                required title="Horas Diarias">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    @if (Auth::user()->rol !== 'Colaborador/a')
                        <button class="btn btn-secondary" id="btnHabilitarEdicion">
                            Editar
                        </button>
                    @endif
                    <button class="btn btn-primary d-none" id="btnGuardarCambios">
                        Actualizar
                    </button>
                    <button class="btn btn-primary" onclick="cerrarModalDetalleNovedad()">
                        Atrás
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCargaMasivaNovedad" tabindex="-1" aria-labelledby="staticBackdropLabel"
        aria-hidden="true" data-bs-backdrop="static">

        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content p-1">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-id-card me-2"></i> Formulario de carga masiva de novedades de sueldo
                    </h5>
                    <button type="button" class="btn-close" onclick="cerrarModalCargaMasiva()"></button>
                </div>
                <form id="formCargaMasiva">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-4 mb-4">
                            <div class="col-lg-12">
                                <div class="empleado-box p-3">
                                    <div class="row g-3 mb-3">
                                        <div class="col-lg-2">
                                            <label class="form-label">Fecha de registro</label>
                                            <input type="text" class="form-control" name="fechaRegistro"
                                                value="{{ now()->format('d/m/Y') }}" readonly>
                                        </div>
                                        <div class="col-lg-4">
                                            <label class="form-label">Registrante</label>
                                            <input type="text" class="form-control" value="{{ Auth::user()->name }}"
                                                name="registrante" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Fecha aplicación</label>
                                            <input type="date" class="form-control" name="fechaAplicacion"
                                                onchange="" required readonly>
                                        </div>
                                        <div class="col-lg-4">
                                            <label for="" class="form-label">Novedad</label>
                                            <select id="selectNovedad" name="idNovedad" class="form-select selectjs"
                                                required>
                                                <option value="">Seleccionar novedad</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-2">
                                            <label class="form-label">Código Finnegans</label>
                                            <input type="text" class="form-control" id="codigoFinnegans" readonly>
                                        </div>

                                        <input type="hidden" id="idNovedad" name="idNovedad" required readonly>

                                        <div class="col-lg-2">
                                            <label class="form-label">Fecha desde</label>
                                            <input type="text" class="form-control" name="fechaDesde"
                                                id="fechaDesdeNovedad" placeholder="Fecha desde" required>
                                        </div>

                                        <div class="col-lg-2">
                                            <label class="form-label">Fecha hasta</label>
                                            <input type="text" class="form-control" name="fechaHasta"
                                                id="fechaHastaNovedad" placeholder="Fecha hasta" required>
                                        </div>

                                        <div class="col-lg-2" id="divPeriodoDias" hidden>
                                            <label class="form-label">Período (días)</label>
                                            <input id="inputDias" name="duracionDias" type="text"
                                                class="form-control">
                                        </div>

                                        <div class="col-lg-2" id="divCantidadHoras" hidden>
                                            <label class="form-label">Horas</label>
                                            <input id="inputHoras" name="horas" type="text" class="form-control">
                                        </div>

                                        <div class="col-lg-2" id="divCantidadPesos" hidden>
                                            <label class="form-label">Importe</label>
                                            <input id="inputImporte" type="text" class="form-control text-end">
                                        </div>

                                        <input type="hidden" name="cantidadFinal" id="cantidadFinal" required>

                                        <div class="col-lg-2 d-none" id="divSelectTipoVacaciones">
                                            <label class="form-label">Tipo de vacaciones</label>
                                            <select id="selectTipoVacaciones" name="tipoVacaciones" class="form-control"
                                                {{ Auth::user()->rol !== 'Administrador/a' ? 'disabled' : '' }}>
                                                <option value="">Seleccione tipo de vacaciones</option>
                                                <option value="2">Gozadas</option>
                                                <option value="3" selected
                                                    {{ Auth::user()->rol !== 'Administrador/a' ? 'selected' : '' }}>
                                                    Gozadas pagadas
                                                </option>
                                                <option value="4">Pagadas</option>
                                                <option value="5">Vencidas</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-1 d-none" id="divSelectAnnioVacaciones">
                                            <label class="form-label">Año</label>
                                            <input type="number" class="form-control" name="annio">
                                        </div>

                                        <div class="col-lg-1 d-none" id="divNumeroAtencion">
                                            <label class="form-label">N° de atención</label>
                                            <input type="number" class="form-control" id="numAtencion"
                                                name="numAtencion">
                                        </div>

                                        <div class="col-lg-2 d-none" id="divIngresarPacienteAtencion">
                                            <label class="form-label">Paciente</label>
                                            <input type="text" class="form-control" id="paciente"
                                                name="pacienteAtencion">
                                        </div>

                                        <div class="col-lg-4 d-none" id="divConceptoAtencion">
                                            <label class="form-label">Concepto</label>
                                            <input type="text" class="form-control" id="conceptoAtencion"
                                                name="conceptoAtencion">
                                        </div>

                                        <div class="col-lg-1 d-none" id="divCantidadCuotas">
                                            <label class="form-label">Cuotas</label>
                                            <input type="number" class="form-control" id="cantidadCuotas"
                                                name="cantidadCuotas">
                                        </div>

                                        <div class="col-lg-4 d-none">
                                            <label class="form-label">Comprobante</label>
                                            <input type="file" class="form-control">
                                        </div>

                                        <div class="col-lg-6    ">
                                            <label class="form-label">Descripción/Observaciones</label>
                                            <input type="text" class="form-control" name="descripcionCargaMasiva"
                                                placeholder="Descripción de la novedad (opcional)">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="empleado-box p-3">
                                    <table id="tbDetalleNovedadMasiva"
                                        class="table table-striped table-bordered table-hover align-middle nowrap">
                                        <thead>
                                            <tr>
                                                <th>LEGAJO</th>
                                                <th>COLABORADOR</th>
                                                <th>DNI</th>
                                                <th>ÁREA</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                    <button id="btnAbrirSeleccionColabs" class="btn-primary btn" type="button">
                                        Seleccionar colaboradores
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" onclick="cerrarModalCargaMasiva()">
                            Atrás
                        </button>
                        <button id="btnRegistrarMasivo" class="btn-primary btn" type="button">
                            Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSeleccionColab" tabindex="-1" aria-labelledby="staticBackdropLabel"
        aria-hidden="true" data-bs-backdrop="static">

        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content p-1">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-id-card me-2"></i> Selección de colaboradores
                    </h5>
                    <button type="button" class="btn-close" onclick="cerrarModalSeleccionColab()"></button>
                </div>

                <div class="modal-body">
                    <table id="tbSeleccionColabs"
                        class="table table-striped table-bordered table-hover align-middle nowrap">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12">
                                <select id="filtroArea" class="form-select js-select-area w-100"
                                    {{ !in_array(Auth::user()->rol, ['Administrador/a', 'Coordinador/a L2']) ? 'disabled' : '' }}>
                                </select>

                                @if (!in_array(Auth::user()->rol, ['Administrador/a', 'Coordinador/a L2']))
                                    <input type="hidden" id="areaFija" value="{{ Auth::user()->area_id }}">
                                @endif
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12">
                                <label for="selectUti" class="form-label text-sm text-muted">Personal de UTI</label>
                                <select id="selectUti" class="form-select w-100">
                                    <option value="" selected>TODOS</option>
                                    <option value="1">SI</option>
                                    <option value="0">NO</option>
                                </select>
                            </div>

                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12">
                                <label for="selectNoche" class="form-label text-sm text-muted">Personal turno
                                    noche</label>
                                <select id="selectNoche" class="form-select">
                                    <option value="">TODOS</option>
                                    <option value="1">SI</option>
                                    <option value="0">NO</option>
                                </select>
                            </div>
                        </div>
                        <thead>
                            <tr>
                                <th>LEGAJO</th>
                                <th>COLABORADOR</th>
                                <th>DNI</th>
                                <th>ÁREA</th>
                                <th class="text-center">
                                    <input type="checkbox" id="chkTodos">
                                </th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="btn-secondary btn" onclick="cerrarModalSeleccionColab()">
                        Atrás
                    </button>
                    <button id="btnSeleccionarColab" class="btn-primary btn" type="button">
                        Seleccionar colaboradores
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="{{ asset('js/novedades/abmNovedades.js') }}"></script>
    <script src="{{ asset('js/novedades/controlNovedades.js') }}"></script>
    <script>
        const USER_ROLE = "{{ Auth::user()->rol }}";
        const USER_AREA_ID = "{{ Auth::user()->area_id }}";
        const USER_NAME = "{{ Auth::user()->username }}";
    </script>
@endpush

@extends('layouts.app')

@section('title', 'Personal de baja')

@section('content')
    <div class="container-fluid">

        <div class="text-start mb-4">
            <h3 class="pill-heading tituloVista">GESTIÓN DEL PERSONAL DE BAJA</h3>
        </div>

        <div class="card" style="border-radius:15px;">
            <div class="card-header">
                <div class="row d-flex align-item-center">
                    <div class="col-10 d-flex justify-content-start align-items-center gap-3">
                        <select id="filtroArea" class="form-select js-select-area" style="width:auto;"
                            {{ Auth::user()->rol !== 'Administrador/a' ? 'disabled' : '' }}>
                        </select>

                        @if (Auth::user()->rol !== 'Administrador/a')
                            <input type="hidden" id="areaFija" value="{{ Auth::user()->area_id }}">
                        @endif

                        <select id="filtroCategoria" class="form-select js-select-categFiltro" style="width:auto;"></select>
                        <select id="filtroRegimen" class="form-select js-select-regFiltro" style="width:auto;"></select>
                        <select id="filtroConvenio" class="form-select js-select-convenioFiltro" style="width:auto;"></select>

                        <button type="button" id="btn-limpiar-filtros" class="btn-secundario">
                            <i class="fa-solid fa-eraser"></i> Limpiar
                        </button>

                        <script>
                            const USER_ROLE = "{{ Auth::user()->rol }}";
                            const USER_AREA_ID = "{{ Auth::user()->area_id }}";
                        </script>

                    </div>
                    <div class="col-2 text-end">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive px-2">
                    <table id="tb_personal" class="table table-striped table-bordered table-hover align-middle nowrap">
                        <thead class="thead-dark">
                            <tr>
                                <th>LEGAJO</th>
                                <th>COLABORADOR</th>
                                <th>DNI</th>
                                <th>ÁREA</th>
                                <th>CATEGORÍA</th>
                                <th>RÉG</th>
                                <th>HD</th>
                                <th>CONVENIO</th>
                                <th>ESTADO</th>
                                <th>FECHA BAJA</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('modals')
    <div class="modal fade" id="modalLegajoColaborador" tabindex="-1" aria-labelledby="staticBackdropLabel"
        aria-hidden="true" data-bs-backdrop="static">

        <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content p-2">

                <div class="modal-header">
                    <h5 class="modal-title" id="tituloLegajo">
                        <i class="fa-solid fa-id-card me-2"></i> Legajo del colaborador
                    </h5>
                    <button type="button" class="btn-close" onclick="cerrarModalLegajo()"></button>
                </div>

                <div class="modal-body">
                    <form id="formAltaColaborador">
                        @csrf
                        <div class="row g-4 mb-4">
                            <div class="col-lg-6">
                                <div class="section-divider mb-3">
                                    <span>Datos Personales</span>
                                </div>
                                <div class="empleado-box p-3">
                                    <div class="row g-3">
                                        <div class="col-lg-3">
                                            <label class="form-label">Legajo</label>
                                            <input type="text" id="inputLegajo" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-7">
                                            <label class="form-label">Apellido y Nombre</label>
                                            <input type="text" id="inputNombre" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Estado</label>
                                            <input type="text" id="inputEstado" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">DNI</label>
                                            <input type="text" id="inputDni" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">CUIL</label>
                                            <input type="text" id="inputCuil" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">Fecha nacimiento</label>
                                            <input type="text" id="inputFechaNacimiento" class="form-control"
                                                readonly>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">Edad</label>
                                            <input type="text" id="inputEdad" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="section-divider mb-3">
                                    <span>Datos de Contacto</span>
                                </div>
                                <div class="empleado-box p-3">
                                    <div class="row g-3">
                                        <div class="col-lg-7">
                                            <label class="form-label">Correo electrónico</label>
                                            <input type="text" id="inputEmail" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-5">
                                            <label class="form-label">Teléfono</label>
                                            <input type="tel" id="inputTelefono" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-7">
                                            <label class="form-label">Domicilio</label>
                                            <input type="text" id="inputDomicilio" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-5">
                                            <label class="form-label">Localidad</label>
                                            <input type="text" id="inputLocalidad" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="section-divider mb-3">
                                    <span>Datos Socioeconómicos</span>
                                </div>
                                <div class="empleado-box p-3">
                                    <div class="row g-3">
                                        <div class="col-lg-3">
                                            <label class="form-label">Estado civil</label>
                                            <input type="text" id="inputEstadoCivil" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">Género</label>
                                            <input type="text" id="inputGenero" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-6">
                                            <label class="form-label">Obra social</label>
                                            <input type="text" id="inputObraSocial" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Código OS</label>
                                            <input type="text" id="inputCodigoOS" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Título</label>
                                            <input type="text" id="inputTitulo" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-6">
                                            <label class="form-label">Descripción de Título</label>
                                            <input type="text" id="inputDescripTitulo" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">M.P.</label>
                                            <input type="text" id="inputMatricula" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="section-divider mb-3">
                                    <span>Datos Laborales</span>
                                </div>
                                <div class="empleado-box p-3">
                                    <div class="row g-3">
                                        <div class="col-lg-2">
                                            <label class="form-label">Tipo de contrato</label>
                                            <input type="text" id="inputTipoContrato" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Fecha ingreso</label>
                                            <input type="text" id="inputFechaIngreso" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Fecha fin prueba</label>
                                            <input type="text" id="inputFechaFinPrueba" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Fecha egreso</label>
                                            <input type="text" id="inputFechaEgreso" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Antiguedad</label>
                                            <input type="text" id="inputAntiguedad" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Área</label>
                                            <input type="text" id="inputArea" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Servicio</label>
                                            <input type="text" id="inputServicio" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Convenio</label>
                                            <input type="text" id="inputConvenio" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Categoría</label>
                                            <input type="text" id="inputCategoria" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-2">
                                            <label class="form-label">Rol</label>
                                            <input type="text" id="inputRol" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-1">
                                            <label class="form-label">Régimen (hs)</label>
                                            <input type="text" id="inputRegimen" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-1">
                                            <label class="form-label">Horas diarias</label>
                                            <input type="text" id="inputHorasDiarias" class="form-control" readonly
                                                required title="Horas Diarias">
                                        </div>
                                        <div class="col-lg-1">
                                            <label class="form-label">Es coordinador</label>
                                            <input type="text" id="inputCordinador" class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-1">
                                            <label class="form-label">Afiliado</label>
                                            <input type="text" id="inputAfiliado" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="file" id="inputComprobantes" accept="pdf,image/*" multiple hidden>

                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <div class="section-divider mb-3">
                                    <span>Historial de Novedades</span>
                                </div>
                                <div class="card table-responsive">
                                    <table id="tb_historialNovedades"
                                        class="table table-striped table-bordered table-hover align-middle nowrap">
                                        <thead>
                                            <tr>
                                                <th>N°</th>
                                                <th>Fecha registro</th>
                                                <th>Novedad</th>
                                                <th>Fecha desde</th>
                                                <th>Fecha hasta</th>
                                                <th>Días/Horas</th>
                                                <th>Comprobantes</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" onclick="cerrarModalLegajo()">
                        Atrás
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="{{ asset('js/personal/personalBaja.js') }}"></script>
@endpush

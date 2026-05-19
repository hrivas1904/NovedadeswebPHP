@extends('layouts.app')

@section('title', 'Personal de baja')

@section('content')
    <div class="container-fluid">

        <div class="d-flex align-items-start gap-3 my-3">
            <div class="icon-box">
                <img src="{{ asset('img/icons/personal-logo.png') }}" style="height: 32px;" alt="Logo personal de baja">
            </div>
            <div>
                <h3 class="tituloVista mb-0">GESTIÓN DEL PERSONAL DE BAJA</h3>
                <p class="mb-0 text-muted">Administración de Colaboradores de Baja.</p>
            </div>
        </div>

        <div class="row d-flex justify-content-start align-items-start">
            <div class="col-2 d-none d-xl-block">
                <div class="card" style="border-radius:15px;">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-semibold" style="color: var(--title-color);">
                                Filtros
                            </h5>
                            <i class="fa-solid fa-sliders" style="color: var(--color-default);"></i>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">
                        @if (Auth::user()->rol === 'Administrador/a' || Auth::user()->rol === 'Colaborador/a L2')
                            <div class="filtro-box">
                                <div class="filtro-header" id="toggleAreas">
                                    <span>Áreas</span>
                                    <i class="fa fa-chevron-down"></i>
                                </div>
                                <div class="filtro-body d-none" id="listaAreas">
                                </div>
                            </div>
                        @endif
                        <div class="filtro-box">
                            <div class="filtro-header" id="toggleCateg">
                                <span>Categorías</span>
                                <i class="fa fa-chevron-down"></i>
                            </div>
                            <div class="filtro-body d-none" id="listaCateg">
                            </div>
                        </div>
                        <div class="filtro-box">
                            <div class="filtro-header" id="toggleConvenios">
                                <span>Convenios</span>
                                <i class="fa fa-chevron-down"></i>
                            </div>
                            <div class="filtro-body d-none" id="listaConvenios">
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="button" id="btn-limpiar-filtros" class="btn btn-secondary w-100">
                                <i class="fa-solid fa-eraser"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-10 col-xxl-10">
                <div class="card" style="border-radius:15px;">
                    <div class="card-header">
                        <div class="d-flex justify-content-end align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" id="btnExportExcelBaja" class="btn btn-primary">
                                    <i class="fa-regular fa-file-excel"></i> Excel
                                </button>
                                <div class="input-group" style="max-width: 750px;">
                                    <span class="input-group-text bg-white">
                                        <i class="fa-solid fa-magnifying-glass text-muted"></i>
                                    </span>
                                    <input type="text" id="searchPersonal" class="form-control" placeholder="Buscar colaborador...">
                                    <button class="btn btn-secondary" id="btnClearSearch" type="button">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>                            
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive px-2">
                            <table id="tb_personal" class="table table-bordered table-hover align-middle">
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
                                        <th>MOTIVO BAJA</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
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
                                            <input type="text" id="inputFechaNacimiento" class="form-control" readonly>
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
    <script src="{{ asset('js/personal/scriptComun.js') }}"></script>
    <script src="{{ asset('js/personal/personalBaja.js') }}"></script>
    <script>
        const USER_ROLE = "{{ Auth::user()->rol }}";
        const USER_AREA_ID = "{{ Auth::user()->area_id }}";
    </script>
@endpush

@extends('layouts.app')

@section('title', 'Registro de Novedades')

@section('content')
    <div class="container-fluid">
        <div class="container-fluid text-start">
            <h3 class="pill-heading tituloVista">REGISTRO DE NOVEDADES DE SUELDO</h3>
        </div>
        <div class="container-fluid py-2">
            <div class="card p-1" style="border-radius: 20px;">

                <div class="card-body">

                    <form id="formCargaNovedad" action="{{ route('novedades.store') }}" method="POST">

                        <div class="section-divider mb-3">
                            <span>Información general</span>
                        </div>

                        <div class="empleado-box p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-lg-1 col-md-3">
                                    <label class="form-label">Fecha de registro</label>
                                    <input type="text" class="form-control" name="fechaRegistro"
                                        value="{{ now()->format('d-m-Y') }}" readonly>
                                </div>

                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Registrante</label>
                                    <input type="text" class="form-control" value="{{ Auth::user()->name }}" name="registrante" readonly>
                                </div>

                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Rol</label>
                                    <input type="text" class="form-control" value="{{ Auth::user()->rol }}" readonly>
                                </div>

                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Área</label>
                                    <select id="area" name="area" class="form-select js-select-area"
                                        style="width: auto;" {{ Auth::user()->rol !== 'Administrador' ? 'disabled' : '' }}>
                                    </select>

                                    @if (Auth::user()->rol !== 'Administrador')
                                        <input type="hidden" id="areaFija" value="{{ Auth::user()->area_id }}">
                                    @endif

                                    <script>
                                        const USER_ROLE = "{{ Auth::user()->rol }}";
                                        const USER_AREA_ID = "{{ Auth::user()->area_id }}";
                                    </script>
                                </div>

                            </div>
                        </div>

                        <div class="section-divider mb-3">
                            <span>Información del empleado</span>
                        </div>

                        <div class="empleado-box p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-lg-1">
                                    <label class="form-label">Legajo</label>
                                    <input type="number" class="form-control" name="legajo" required>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Empleado</label>
                                    <select id="selectEmpleado" class="form-select js-selector-colaborador">
                                        <option value="">Seleccione empleado</option>
                                    </select>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Servicio</label>
                                    <input type="text" class="form-control" name="servicio" readonly>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Antigüedad</label>
                                    <input type="text" class="form-control" name="antiguedad" readonly>
                                </div>

                                <div class="col-lg-1">
                                    <label class="form-label">Régimen</label>
                                    <input type="text" class="form-control" name="regimen" readonly>
                                </div>

                                <div class="col-lg-1">
                                    <label class="form-label">Hs diarias</label>
                                    <input type="text" class="form-control" name="horasDiarias" readonly>
                                </div>

                                <div class="col-lg-1">
                                    <label class="form-label">Convenio</label>
                                    <input type="text" class="form-control" name="convenio" readonly>
                                </div>

                                <div class="col-lg-1">
                                    <label class="form-label">Título</label>
                                    <input type="text" class="form-control" name="titulo" readonly>
                                </div>

                                <div class="col-lg-1">
                                    <label class="form-label">Afiliado</label>
                                    <input type="text" class="form-control" name="afiliado" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="section-divider mb-3">
                            <span>Registrar novedad de sueldo</span>
                        </div>

                        <div class="empleado-box p-3">
                            <div class="row g-3">

                                <div class="col-lg-2">
                                    <label class="form-label">Tipo de novedad</label>
                                    <select id="selectCategoria" class="form-select" required>
                                        <option value="">Seleccionar tipo</option>
                                    </select>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Novedad</label>
                                    <select id="selectNovedad" name="idNovedad" class="form-select" disabled required>
                                        <option value="">Seleccionar novedad</option>
                                    </select>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Código Finnegans</label>
                                    <input type="text" class="form-control" id="codigoFinnegans" name="codigoNovedad"
                                        readonly>
                                </div>


                                <div class="col-lg-2">
                                    <label class="form-label">Tipo valor</label>
                                    <input type="text" class="form-control" name="centroCosto" required>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Valor 1</label>
                                    <input type="text" class="form-control" name="valor1" required>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Valor 2</label>
                                    <input type="text" class="form-control" name="valor2" required>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Fecha aplicación</label>
                                    <input type="date" class="form-control" name="fechaAplicacion"
                                        onchange="calcularDuracion()" required>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Fecha desde</label>
                                    <input type="date" class="form-control" name="fechaDesde"
                                        onchange="calcularDuracion()" required>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Fecha hasta</label>
                                    <input type="date" class="form-control" name="fechaHasta"
                                        onchange="calcularDuracion()" required>
                                </div>

                                <div class="col-lg-2">
                                    <label class="form-label">Período (días)</label>
                                    <input type="text" class="form-control" name="duracion" readonly required>
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label">Descipción</label>
                                    <input type="text" class="form-control" name="descripcion" required>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>

            </div>
            <div class="p-4 d-flex gap-3 justify-content-end">
                <button type="button" class="btn btn-secundario">Cancelar</button>
                <button type="submit" class="btn btn-primario" form="formCargaNovedad" id="btnRegistrarNovedad">
                    <i class="fa-solid fa-floppy-disk me-1"></i>
                    Registrar novedad
                </button>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/novedades/registroNovedades.js') }}"></script>
@endpush

@extends('layouts.app')

@section('title', 'Configuraciones Recursos Humanos')

@section('content')
    <div class="container-fluid">
        <h3 class="tituloVista mb-3">CONFIGURACIONES RECURSOS HUMANOS</h3>

        <div class="d-flex justify-content-start align-items-center gap-3">
            <button type="button" class="btn btn-sm btn-analisis btnParametro active" style="color: var(--color-default);" data-url="{{ route('rrhh.configAreasView') }}" data-vista="areasServicios">Áreas y Servicios</button>
            <button type="button" class="btn btn-sm btn-analisis btnParametro" style="color: var(--color-default);" data-url="{{ route('rrhh.configCategoriasView') }}" data-vista="categorias">Categorías</button>
            <button type="button" class="btn btn-sm btn-analisis btnParametro" style="color: var(--color-default);" data-url="{{ route('rrhh.configRegimenesView') }}" data-vista="regimenes">Regimenes</button>
        </div>
        <hr style="color: var(--color-default); border: 1px solid;" />
        <div class="renderDivParametros">

        </div>
    </div>
@endsection

@push('modals')
    <div class="modal fade" id="modalEdicionArea" tabindex="-1" aria-labelledby="staticBackdropLabel"
        aria-hidden="true" data-bs-backdrop="static">

        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content p-2">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-pen-to-square"></i> Edición de área
                    </h5>
                    <button type="button" class="btn-close" onclick="cerrarModalAreaEdit()"></button>
                </div>

                <form id="formEditArea">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-4 mb-4">
                            <div class="col-lg-12">
                                <div class="empleado-box p-3">
                                    <div class="row g-3">
                                        <div class="col-lg-2 col-12">
                                            <label class="form-label">ID</label>
                                            <input type="text" id="idAreaEdit" name="idAreaEdit" class="form-control"
                                                readonly>
                                        </div>
                                        <div class="col-lg-10 col-12">
                                            <label class="form-label">NOMBRE</label>
                                            <input type="text" id="nombreAreaEdit" name="nombreAreaEdit"
                                                class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="btnEliminarArea">
                            Eliminar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnEditarArea">
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdicionCateg" tabindex="-1" aria-labelledby="staticBackdropLabel"
        aria-hidden="true" data-bs-backdrop="static">

        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content p-2">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-pen-to-square"></i> Edición de categoría
                    </h5>
                    <button type="button" class="btn-close" onclick="cerrarModalCategEdit()"></button>
                </div>

                <form id="formEditCateg">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-4 mb-4">
                            <div class="col-lg-12">
                                <div class="empleado-box p-3">
                                    <div class="row g-3">
                                        <div class="col-lg-2 col-12">
                                            <label class="form-label">ID</label>
                                            <input type="text" id="idCategEdit" name="idCategEdit"
                                                class="form-control" readonly>
                                        </div>
                                        <div class="col-lg-10 col-12">
                                            <label class="form-label">NOMBRE</label>
                                            <input type="text" id="nombreCategEdit" name="nombreCategEdit"
                                                class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="btnEliminarCateg">
                            Eliminar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnEditarCateg">
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdicionServicio" tabindex="-1" aria-labelledby="staticBackdropLabel"
        aria-hidden="true" data-bs-backdrop="static">

        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content p-2">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-pen-to-square"></i> Edición de servicios
                    </h5>
                    <button type="button" class="btn-close" onclick="cerrarModalServEdit()"></button>
                </div>

                <form id="formEditServicio">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-4 mb-4">
                            <div class="col-lg-12">
                                <div class="empleado-box p-3">
                                    <div class="row g-3">
                                        <div class="col-lg-2 col-12">
                                            <label class="form-label">ID</label>
                                            <input type="text" id="idServEdit" name="idServEdit" class="form-control"
                                                readonly>
                                        </div>
                                        <div class="col-lg-10 col-12">
                                            <label class="form-label">NOMBRE</label>
                                            <input type="text" id="nombreServEdit" name="nombreServEdit"
                                                class="form-control">
                                        </div>
                                        <div class="col-12">
                                            <label for="areaServEdit" class="form-label">
                                                ÁREA VINCULADA
                                            </label>

                                            <select id="areaServEdit" name="areaServEdit" class="form-select" style="width: 100%;">
                                                <option value="">-- Seleccione un área --</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="btnEliminarServ">
                            Eliminar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnEditarServ">
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="{{ asset('js/ajustes/parametrizacionAreasServ.js') }}"></script>
    <script src="{{ asset('js/ajustes/parametrizacionHome.js') }}"></script>
@endpush

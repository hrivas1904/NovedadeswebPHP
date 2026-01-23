@extends('layouts.app')

@section('title', 'Espacio de Comunicados')

@section('content')

    <div class="container-fluid">

        <div class="d-flex justify-content-between ms-auto align-item-end gap-3 my-2">
            <h3 class="pill-heading tituloVista">CALENDARIO DE SERVICIOS
            </h3>
        </div>

        <div class="row align-items-end g-2 my-2">

            <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                <label class="form-label mb-1">Desde</label>
                <input id="filtroDesde" class="form-control" type="date">
            </div>

            <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                <label class="form-label mb-1">Hasta</label>
                <input id="filtroHasta" class="form-control" type="date">
            </div>

            <div class="col-xl-auto col-lg-auto col-md-4 col-6">
                <button type="button" id="btnAplicarFiltros" class="btn btn-primario w-100">
                    <i class="fa-regular fa-circle-check"></i> Aplicar
                </button>
            </div>

            <div class="col-xl-auto col-lg-auto col-md-4 col-6">
                <button type="button" id="btnLimpiarFiltros" class="btn btn-secundario w-100">
                    <i class="fa-solid fa-eraser"></i> Limpiar
                </button>
            </div>

            <div class="col-xl-auto col-lg-auto col-md-4 col-6">
                <button type="button" id="btnAsignarTarea" class="btn btn-primario w-100">
                    <i class="fa-solid fa-list-check"></i> Nueva tarea
                </button>
            </div>

            <div class="col-xl-auto col-lg-auto col-md-4 col-6">
                <button type="button" id="btnExportarPdf" class="btn btn-peligro w-100">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </button>
            </div>

            <div class="col-xl-auto col-lg-auto col-md-4 col-6">
                <button type="button" id="btnExportarImagen" class="btn btn-terciario w-100">
                    <i class="fa-solid fa-image"></i> IMG
                </button>
            </div>

        </div>

        <div class="container-fluid"></div>

    </div>

@endsection

@push('modals')
    <div class="modal fade" id="modalNuevaTarea">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="{{ asset('js/calendario/calendarioServicio.js') }}"></script>

    <script>
        const USER_ROLE = "{{ Auth::user()->rol }}";
    </script>
@endpush

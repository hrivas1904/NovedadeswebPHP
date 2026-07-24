@extends('layouts.app')

@section('title', 'USUARIOS Y PERMISOS')

@section('content')
<div class="container-fluid">
    <h3 class="tituloVista mb-3">PERMISOS</h3>
    <div class="row g-3">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card p-2 gap-3 h-100">
                <div class="card-header d-flex align-items-end justify-content-between">
                    <h6 class="fw-bold">Listado de Roles</h6>
                    <button type="button" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-plus"></i>
                        Nuevo Rol
                    </button>
                </div>

                <div class="card-body d-flex flex-column">
                    <div class="input-group mb-3">
                        <input type="text" id="txtBuscarRol" class="form-control" placeholder="Buscar rol...">
                        <button id="btnLimpiarBusqueda" class="btn btn-outline-secondary" type="button">Limpiar</button>
                    </div>

                    <div class="renderListaRoles flex-grow-1 overflow-auto">

                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-9">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-end justify-content-between">
                        <h6 class="fw-bold mb-0">
                            Editando:
                            <span id="nombreRolSeleccionado" style="color: var(--color-default);">
                                Ningún rol seleccionado
                            </span>
                        </h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                <i class="fa-solid fa-plus"></i>
                                Nuevo Permiso
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-sm btn-primary">
                                Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body d-flex flex-column gap-3">
                    <div class="card flex-grow-1">
                        <div class="card-header">
                            Recursos Humanos
                        </div>
                        <div id="renderPermisosRecursosHumanos" class="card-body overflow-auto" style="max-height: 30vh;">
                        </div>
                    </div>

                    <div class="card flex-grow-1">
                        <div class="card-header">
                            Calidad
                        </div>
                        <div id="renderPermisosCalidad" class="card-body overflow-auto" style="max-height: 30vh;">
                        </div>
                    </div>

                    <div class="card flex-grow-1">
                        <div class="card-header">
                            Administración
                        </div>
                        <div id="renderPermisosAdministracion" class="card-body overflow-auto" style="max-height: 30vh;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">
                    Crear Nuevo Permiso</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Understood</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="/js/config/homeConfig/homeConfig.js"></script>
<script src="/js/config/roles_usuarios/roles_usuarios.js"></script>
@endpush
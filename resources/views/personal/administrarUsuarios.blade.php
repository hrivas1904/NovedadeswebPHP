@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="container-fluid">
    <div class="text-start mb-4">
        <h3 class="pill-heading tituloVista">ADMINISTRAR USUARIOS</h3>
    </div>

    <div class="card p-1" style="border-radius: 20px;">
        <div class="card-header">
            <div class="row g-2 mb-3">
                <div class="col-12 col-sm-12 col-md-3 col-xl-3 col-xxl-2">
                    <select class="form-select js-select-area w-100" id="filtroArea"></select>
                </div>
                <div class="col-12 col-sm-12 col-md-3 col-xl-3 col-xxl-2">
                    <select class="form-select w-100" id="filtroEstado">
                        <option value="">TODOS</option>
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="DE BAJA">DE BAJA</option>
                    </select>
                </div>
                <div class="col-12 col-sm-12 col-md-3 col-xl-3 col-xxl-2">
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                        Nuevo usuario
                    </button>
                </div>
                <div class="col-12 col-sm-12 col-md-3 col-xl-3 col-xxl-2">
                    <button id="btnLimpiarFiltros" class="btn btn-secondary w-100">
                        <i class="fa fa-eraser"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive px-2">
                <table id="tb_usuarios" class="table table-bordered table-hover align-middle">
                    <thead class="thead-dark">
                        <tr>
                            <th>LEGAJO</th>
                            <th>COLABORADOR</th>
                            <th>ÁREA</th>
                            <th>USUARIO</th>
                            <th>FECHA ALTA</th>
                            <th>FECHA BAJA</th>
                            <th>ESTADO</th>
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
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px;">

            <!-- HEADER -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-user-plus"></i> Nuevo Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body">
                <form id="formNuevoUsuario">
                    <div class="row g-3">

                        <!-- NOMBRE -->
                        <div class="col-md-6">
                            <label class="form-label">Colaborador</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>

                        <!-- LEGAJO -->
                        <div class="col-md-3">
                            <label class="form-label">Legajo</label>
                            <input type="number" class="form-control" id="legajo" required>
                        </div>

                        <!-- USUARIO -->
                        <div class="col-md-3">
                            <label class="form-label">Nombre de usuario</label>
                            <input type="text" class="form-control" id="username">
                        </div>

                        <!-- CONTRASEÑA -->
                        <div class="col-md-6">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" required>
                        </div>

                        <!-- ROL -->
                        <div class="col-md-6">
                            <label class="form-label">Rol</label>
                            <select class="form-select" id="rol" required>
                                <option value="">Seleccionar rol</option>
                                <option value="Administrador/a">Administrador/a</option>
                                <option value="Coordinador/a">Coordinador/a</option>
                                <option value="Coordinador/a L2">Coordinador/a L2</option>
                                <option value="Supervisor/a Calidad">Supervisor/a Calidad</option>
                                <option value="Colaborador/a">Colaborador/a</option>
                            </select>
                        </div>

                        <!-- AREA -->
                        <div class="col-md-6">
                            <label class="form-label">Área</label>
                            <select class="form-select js-select-area" id="area" required>
                                <option value="">Seleccionar área</option>
                            </select>
                        </div>

                    </div>

                    <!-- INFO UX -->
                    <div class="mt-3 small text-muted">
                        💡 Si no se ingresa nombre de usuario, se utilizará el legajo automáticamente.
                    </div>

                </form>
            </div>

            <!-- FOOTER -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="button" id="btnGuardarUsuario" class="btn btn-primary">
                    <i class="fa fa-save"></i> Guardar
                </button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px;">

            <!-- HEADER -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-user-edit"></i> Editar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body">
                <form id="formEditarUsuario">

                    <div class="row g-3">

                        <!-- NOMBRE -->
                        <div class="col-md-6">
                            <label class="form-label">Nombre del empleado</label>
                            <input type="text" class="form-control" id="edit_name" required>
                        </div>

                        <!-- LEGAJO -->
                        <div class="col-md-3">
                            <label class="form-label">Legajo</label>
                            <input type="number" class="form-control" id="edit_legajo" readonly>
                        </div>

                        <!-- USERNAME -->
                        <div class="col-md-3">
                            <label class="form-label">Nombre de usuario</label>
                            <input type="text" class="form-control" id="edit_username">
                        </div>

                        <!-- PASSWORD -->
                        <div class="col-md-6">
                            <label class="form-label">Nueva contraseña</label>
                            <input type="password" class="form-control" id="edit_password">
                        </div>

                        <!-- ROL -->
                        <div class="col-md-6">
                            <label class="form-label">Rol</label>
                            <select class="form-select" id="edit_rol" required>
                                <option value="">Seleccionar rol</option>
                                <option value="Administrador/a">Administrador/a</option>
                                <option value="Coordinador/a">Coordinador/a</option>
                                <option value="Coordinador/a L2">Coordinador/a L2</option>
                                <option value="Supervisor/a Calidad">Supervisor/a Calidad</option>
                                <option value="Colaborador/a">Colaborador/a</option>
                            </select>
                        </div>

                        <!-- AREA -->
                        <div class="col-md-6">
                            <label class="form-label">Área</label>
                            <select class="form-select js-select-area" id="edit_area" required></select>
                        </div>

                    </div>

                </form>
            </div>

            <!-- FOOTER -->
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="btnActualizarUsuario" class="btn btn-primary">
                    <i class="fa fa-save"></i> Guardar cambios
                </button>
            </div>

        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/personal/administrarUsuarios.js') }}"></script>
@endpush
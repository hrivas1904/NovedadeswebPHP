@extends('layouts.app')

@section('title', 'Espacio de Comunicados')

@push('styles')
<style>
    #editorComunicado .ql-editor {
        font-size: 1.5rem;
    }
</style>
@endpush

@section('content')

    <div class="container-fluid">

        <div class="d-flex align-items-start gap-3 my-3">
            <div class="icon-box">
                <i class="fa-regular fa-user"></i>
            </div>
            <div>
                <h3 class="tituloVista mb-0">¡Hola, <strong>{{ Auth::user()->name }}</strong>!</h3>
                <p class="mb-0 text-muted">Aquí encontrarás avisos y novedades importantes.</p>
            </div>
        </div>

        <div class="card p-4 mb-3">
            @if (in_array(Auth::user()->rol, ['Administrador/a']))
                <div class="row g-3 mb-3">
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card card-hover-radial p-2" id="cardKpiColabActivos" style="cursor: pointer;">
                            <div class="d-flex gap-3">
                                <div class="icon-box" style="background: var(--color-accent-green); color: var(--bg-card);">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">COLABORADORES ACTIVOS</h6>
                                    <h2 id="lblColabsActivos" class="mb-0">0</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card card-hover-radial p-2" id="cardKpiNovMes" style="cursor: pointer;">
                            <div class="d-flex gap-3">
                                <div class="icon-box" style="background: var(--color-accent-green); color: var(--bg-card);">
                                    <i class="fa-solid fa-folder-open"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">NOVEDADES DEL MES</h6>
                                    <h2 id="lblNovedadesMes" class="mb-0">0</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card card-hover-radial p-2" id="cardKpiAdPendientes" style="cursor: pointer;">
                            <div class="d-flex gap-3">
                                <div class="icon-box" style="background: var(--color-accent-green); color: var(--bg-card);">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">ADELANTOS PENDIENTES</h6>
                                    <h2 id="lblAdelantosPend" class="mb-0">0</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="card card-hover-radial p-2" id="cardKpiTicketAbiertos" style="cursor: pointer;">
                            <div class="d-flex gap-3">
                                <div class="icon-box" style="background: var(--color-accent-green); color: var(--bg-card);">
                                    <i class="fa-solid fa-ticket"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">TICKETS ABIERTOS</h6>
                                    <h2 id="lblTicketsAbiertos" class="mb-0">0</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if (in_array(Auth::user()->rol, ['Administrador/a', 'Supervisor/a Calidad']))
                <div id="publicarNotificacion" class="row mb-4">
                    <div class="col-12">
                        <div class="card p-3">
                            <div class="d-flex align-items-start gap-3 my-3">
                                <div class="icon-box" style="background: var(--color-default); color: var(--bg-card);">
                                    <i class="fa-solid fa-pen"></i>
                                </div>
                                <div>
                                    <h3 class="tituloVista mb-0">Publicar nuevo aviso</h3>
                                    <p class="mb-0 text-muted">Escribí el asunto y el contenido del aviso.</p>
                                </div>
                            </div>
                            <input id="txtNotificacionTitulo" class="form-control mb-2" placeholder="Escriba el asunto...">
                            <div id="editorComunicado" style="height:150px; font-family:1rem;"></div>
                            <input type="hidden" id="txtNotificacion">

                            <div id="emojiPicker" class="border rounded p-2 shadow-sm"
                                style="display:none; position:absolute; z-index:1000; background:#fff;">
                                <span class="emoji-option" style="cursor:pointer; font-size:1.4rem; margin:2px;">❗</span>
                                <span class="emoji-option" style="cursor:pointer; font-size:1.4rem; margin:2px;">📌</span>
                                <span class="emoji-option" style="cursor:pointer; font-size:1.4rem; margin:2px;">⚠️</span>
                                <span class="emoji-option" style="cursor:pointer; font-size:1.4rem; margin:2px;">✅</span>
                                <span class="emoji-option" style="cursor:pointer; font-size:1.4rem; margin:2px;">📅</span>
                                <span class="emoji-option" style="cursor:pointer; font-size:1.4rem; margin:2px;">🔔</span>
                            </div>

                            <div class="d-flex justify-content-end mt-3 gap-3">
                                <button type="button" id='btnCancelarRedactarComunicado' class="btn btn-secondary">
                                    Cancelar
                                </button>
                                <button type="button" id='btnRedactarComunicado' class="btn btn-primary">
                                    <i class="fa-solid fa-bullhorn"></i>
                                    Publicar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row d-flex">
                <div class="col-xl-9 col-12 d-flex">
                    <div class="col-12">
                        <div class="card card-header-fixed p-2">
                            <div class="card-header">
                                <div class="d-flex gap-3 align-items-center">
                                    <div class="icon-box d-flex align-items-center justify-content-center"
                                        style="
                                            width: 50px;
                                            height: 50px;
                                            border-radius: 50%;
                                            background: rgba(0, 145, 213, 0.1);
                                            color: var(--color-second);
                                            flex-shrink: 0;">
                                        <i class="fa-solid fa-bullhorn"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mt-1" style="color: var(--color-default)">AVISOS</h5>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="card-body contenedor-scroll {{ in_array(Auth::user()->rol, ['Administrador/a', 'Supervisor/a Calidad']) ? 'scroll-admin' : 'scroll-user' }}"">
                                <div id="listaNotificaciones">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-md-6 col-12 d-none">
                    <div class="card p-2">
                        <div class="card-header">
                            <div class="d-flex gap-3 align-items-center">
                                <div class="icon-box d-flex align-items-center justify-content-center"
                                    style="
                                    width: 50px;
                                    height: 50px;
                                    border-radius: 50%;
                                    background: rgba(0, 145, 213, 0.1);
                                    color: var(--color-second);
                                    flex-shrink: 0;">
                                    <i class="fa-solid fa-list"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mt-1" style="color: var(--color-default)">MI RESÚMEN</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style="height: 400px">
                            <div class="d-flex gap-3 align-items-start my-2" id="kpiMisNovedades"
                                style="cursor: pointer;">
                                <div class="icon-box d-flex align-items-center justify-content-center"
                                    style="
                                    width: 50px;
                                    height: 50px;
                                    border-radius: 50%;
                                    background: rgba(0, 145, 213, 0.1);
                                    color: var(--color-second);
                                    flex-shrink: 0;">
                                    <i class="fa-solid fa-folder-open"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mt-1" style="color: var(--color-default)">Mis novedades</h6>
                                    <p id="lblMisNovedades" class="text-muted">0</p>
                                </div>
                            </div>
                            <div class="d-flex gap-3 align-items-center my-2" id="kpiMisSolicitudes"
                                style="cursor: pointer;">
                                <div class="icon-box d-flex align-items-center justify-content-center"
                                    style="
                                    width: 50px;
                                    height: 50px;
                                    border-radius: 50%;
                                    background: rgba(0, 145, 213, 0.1);
                                    color: var(--color-second);
                                    flex-shrink: 0;">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mt-1" style="color: var(--color-default)">Mis solicitudes</h6>
                                    <p id="lblMisSolicitudes" class="text-muted">0</p>
                                </div>
                            </div>
                            <div class="d-flex gap-3 align-items-center my-2" id="kpiMisTickets"
                                style="cursor: pointer;">
                                <div class="icon-box d-flex align-items-center justify-content-center"
                                    style="
                                    width: 50px;
                                    height: 50px;
                                    border-radius: 50%;
                                    background: rgba(0, 145, 213, 0.1);
                                    color: var(--color-second);
                                    flex-shrink: 0;">
                                    <i class="fa-solid fa-ticket"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mt-1" style="color: var(--color-default)">Mis tickets</h6>
                                    <p id="lblMisTickets" class="text-muted">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-12 d-flex">
                    <div class="card card-header-fixed p-2">
                        <div class="card-header">
                            <div class="d-flex gap-3 align-items-center justify-content-between">
                                <div class="icon-box d-flex align-items-center justify-content-center"
                                    style="
                                        width: 50px;
                                        height: 50px;
                                        border-radius: 50%;
                                        background: rgba(0, 145, 213, 0.1);
                                        color: var(--color-second);
                                        flex-shrink: 0;">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mt-1" style="color: var(--color-default)">PRÓXIMOS EVENTOS</h5>
                                </div>
                                @if (in_array(Auth::user()->rol, ['Administrador/a', 'Supervisor/a Calidad']))
                                    <div class="ms-auto">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#modalProximoEvento">
                                            <i class="fa-solid fa-calendar-plus"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div
                            class="card-body contenedor-scroll {{ in_array(Auth::user()->rol, ['Administrador/a', 'Supervisor/a Calidad']) ? 'scroll-admin' : 'scroll-user' }}"">
                            <div id="divCardFeriado">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('modals')
    <div class="modal fade" id="modalProximoEvento" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Programar nuevo evento</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoEventoProgramado">
                        <div class="row g-3">
                            <div class="col-xl-5 col-md-6 col-12">
                                <label for="inputFechaEvento" class="form-label">Fecha del evento</label>
                                <input class="form-control" id="inputFechaEvento" name="fechaEvento" type="date">
                            </div>
                            <div class="col-xl-7 col-md-6 col-12">
                                <label for="selectTipoEvento" class="form-label">Tipo de evento</label>
                                <select class="form-select w-100" id="selectTipoEvento" name="tipoEvento">
                                    <option selected disabled value="">Seleccione evento...</option>
                                    <option value="Capacitación">Capacitación</option>
                                    <option value="Feriado nacional">Feriado nacional</option>
                                    <option value="Feriado provincial">Feriado provincial</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="inputTituloEvento" class="form-label">Título del evento</label>
                                <input class="form-control" id="inputTituloEvento" name="tituloEvento" type="text">
                            </div>
                            <div class="col-12">
                                <label for="inputDescripEvento" class="form-label">Descripción del evento</label>
                                <input class="form-control" id="inputDescripEvento" name="descripEvento" type="text">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarEvento">
                        Guardar evento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetalleEvento" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Editar evento</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoEventoProgramado">
                        <div class="row g-3">
                            <div class="col-xl-5 col-md-6 col-12">
                                <label for="inputFechaEvento" class="form-label">Fecha del evento</label>
                                <input class="form-control" id="inputFechaEventoEdit" name="fechaEventoEdit"
                                    type="date">
                                <input type="hidden" id="inputIdEventoEdit" name="idEvento">
                            </div>
                            <div class="col-xl-7 col-md-6 col-12">
                                <label for="selectTipoEvento" class="form-label">Tipo de evento</label>
                                <select class="form-select w-100" id="selectTipoEventoEdit" name="tipoEventoEdit">
                                    <option selected disabled value="">Seleccione evento...</option>
                                    <option value="Capacitación">Capacitación</option>
                                    <option value="Feriado nacional">Feriado nacional</option>
                                    <option value="Feriado provincial">Feriado provincial</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="inputTituloEvento" class="form-label">Título del evento</label>
                                <input class="form-control" id="inputTituloEventoEdit" name="tituloEventoEdit"
                                    type="text">
                            </div>
                            <div class="col-12">
                                <label for="inputDescripEvento" class="form-label">Descripción del evento</label>
                                <input class="form-control" id="inputDescripEventoEdit" name="descripEventoEdit"
                                    type="text">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnEditarEvento">
                        Editar evento
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="{{ asset('js/home/mensajes.js') }}"></script>

    <script>
        const USER_ROLE = "{{ Auth::user()->rol }}";
    </script>

    <script>
        const icons = Quill.import('ui/icons');
        icons['emoji'] = '<i class="fa-regular fa-face-smile"></i>'; // usa Font Awesome que ya tenés

        const quill = new Quill('#editorComunicado', {
            theme: 'snow',
            placeholder: 'Escriba el comunicado...',
            modules: {
                toolbar: {
                    container: [
                        ['bold', 'italic', 'underline'],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        [{
                            'align': []
                        }],
                        ['emoji']
                    ],
                    handlers: {
                        emoji: function() {
                            const bounds = this.quill.getBounds(this.quill.getSelection()?.index || 0);
                            const toolbar = document.querySelector('.ql-toolbar');
                            const picker = document.getElementById('emojiPicker');

                            picker.style.top = (toolbar.offsetTop + toolbar.offsetHeight) + 'px';
                            picker.style.left = toolbar.offsetLeft + 'px';
                            picker.style.display = picker.style.display === 'none' ? 'block' : 'none';
                        }
                    }
                }
            }
        });

        // Insertar emoji al hacer clic
        document.querySelectorAll('.emoji-option').forEach(el => {
            el.addEventListener('click', function() {
                const range = quill.getSelection(true);
                quill.insertText(range.index, this.textContent, 'user');
                quill.setSelection(range.index + this.textContent.length);
                document.getElementById('emojiPicker').style.display = 'none';
            });
        });

        $("#btnRedactarComunicado").click(function() {
            $("#txtNotificacion").val(quill.root.innerHTML);
        });
    </script>
@endpush

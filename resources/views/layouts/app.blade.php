<!DOCTYPE html>
<html lang="es">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <title>@yield('title', 'Sistema')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <link rel="stylesheet" href="{{ asset('css/paletaColores.css') }}">
    <link rel="stylesheet" href="{{ asset('css/botones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="{{ asset('css/styleDt.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modales.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cronograma.css') }}">
    <link rel="stylesheet" href="{{ asset('css/calendario.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tickets.css') }}">
    <link rel="stylesheet" href="{{ asset('css/componentes/cssFiltroEcommerce.css') }}">
    <link rel="stylesheet" href="{{ asset('css/componentes/cssNavbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/componentes/cssDivAvisos.css') }}">
    <link rel="stylesheet" href="{{ asset('css/componentes/cssCards.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ayuda.css') }}">
</head>

<body>
    <nav class="navbar navbar-expand-xl navbar-dark fixed-top shadow-sm"
        style="background-color: var(--color-default);">
        <div class="container-fluid">

            <a class="navbar-brand d-flex align-items-center" href="{{ route('index') }}">
                <img src="{{ asset('img/logo-hp3c-white.png') }}" height="40" class="me-2 logo-navbar">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">

                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">

                    @if (Auth::user()->rol === 'Administrador/a')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <i class="bx bx-grid-alt"></i> Dashboard
                            </a>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bx bx-user"></i> Colaboradores
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('miLegajo') }}">Mi legajo</a></li>

                            @if (Auth::user()->rol === 'Coordinador/a L2' ||
                                    Auth::user()->rol === 'Administrador/a' ||
                                    Auth::user()->rol === 'Coordinador/a')
                                <li><a class="dropdown-item" href="{{ route('nominaPersonal') }}">Colaboradores</a></li>
                            @endif

                            @if (Auth::user()->rol === 'Coordinador/a L2' || Auth::user()->rol === 'Administrador/a')
                                <li><a class="dropdown-item" href="{{ route('calendarioServicios') }}">Calendario recepción</a></li>
                            @endif

                            @if (Auth::user()->rol === 'Administrador/a')
                                <!--<li><a class="dropdown-item" href="{{ route('registroAsistencia') }}">Monotributistas</a></li>

                                <li><a class="dropdown-item" href="{{ route('registroAsistencia') }}">Médicos</a></li>

                                <li><a class="dropdown-item" href="{{ route('registroAsistencia') }}">Marcar asistencia</a></li>

                                <li><a class="dropdown-item" href="{{ route('controlTarjas') }}">Tarjas</a></li>-->
                            @endif

                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-folder-open"></i> Novedades
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('novedades.misNovedades') }}">Mis
                                    novedades</a></li>
                            @if (in_array(Auth::user()->rol, ['Administrador/a', 'Coordinador/a', 'Coordinador/a L2']))
                                <li><a class="dropdown-item" href="{{ route('controlNovedades') }}">Registro de
                                        novedades</a></li>
                                <li><a class="dropdown-item" href="{{ route('novedades.historico') }}">Histórico
                                        novedades</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('personal.solicitudes') }}">Solicitud
                                    adelanto sueldo</a></li>
                        </ul>
                    </li>

                    @if (in_array(Auth::user()->rol, ['Administrador/a', 'Supervisor/a Calidad']))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-award"></i> Calidad
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('encuestasCalidad') }}">Importar
                                        encuestas</a></li>
                                <li><a class="dropdown-item" href="{{ route('dashboardCalidad') }}">Dashboard</a>
                                </li>
                                <!--<li><a class="dropdown-item" href="{{ route('respuestasEncuestas') }}">Resultados</a>
                                </li>-->
                            </ul>
                        </li>
                    @endif

                    @if (Auth::user()->rol === 'Administrador/a')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-gear"></i> Ajustes
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('configNovedades') }}">Conceptos
                                        novedades</a></li>
                                <li><a class="dropdown-item" href="{{ route('administrarUsuarios') }}">Administrar
                                        usuarios</a></li>
                                <li><a class="dropdown-item"
                                        href="{{ route('obraSocial.administrarObraSociales') }}">Obras
                                        sociales</a></li>
                                <li><a class="dropdown-item" href="{{ route('parametrizacionesGenerales') }}">Parámetros generales</a></li>
                            </ul>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link" href="https://capacitacion.hp3c.com.ar/login/index.php" target="_blank">
                            <i class='bx bx-book'></i> Capacitaciones
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('ayuda') }}">
                            <i class="fa-regular fa-circle-question"></i> Ayuda
                        </a>
                    </li>

                </ul>

                <div class="d-flex align-items-center gap-3">

                    <div class="user-chip text-white">                       

                        <div class="user-info text-end small">
                            <div><strong>{{ Auth::user()->name }}</strong></div>
                            <div class="user-role">{{ Auth::user()->rol }}</div>
                        </div>

                        <!--<div class="user-avatar">
                            @if (Auth::user()->foto)
                                <img src="{{ asset('storage/' . Auth::user()->foto) }}" alt="Perfil">
                            @else
                                <span class="avatar-initial">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                            @endif
                        </div>-->

                    </div>

                    <button class="btn btn-light position-relative" id="btnAlertas">
                        <i class="fa-solid fa-bell"></i>
                        <span id="contadorAlertas"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            0
                        </span>
                    </button>

                    <div id="dropdownAlertas" class="dropdown-alertas">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="fw-semibold">Notificaciones</span>
                            <button id="btnLimpiarAlertas" class="btn btn-sm text-danger p-0">
                                Limpiar
                            </button>
                        </div>
                        <ul id="listaAlertas" class="list-unstyled mb-0"></ul>
                    </div>

                    <!--<button id="toggleTheme" class="btn btn-light">
                        <i id="themeIcon" class="fa-solid fa-moon"></i>
                    </button>-->

                    <form id="logout-form" action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-light">
                            <i class='bx bx-log-out'></i>
                        </button>
                    </form>

                </div>

            </div>
        </div>
    </nav>

    <main class="container-fluid mt-5 pt-4">
        @yield('content')
    </main>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.bootstrap5.min.js"></script>

    <script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/3.0.0/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.bootstrap5.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.print.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script src="{{ asset('js/home/alertas.js') }}"></script>
    <script src="{{ asset('js/home/darkMode.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

    <script>
        const VAPID_PUBLIC_KEY = "{{ env('VAPID_PUBLIC_KEY') }}";
    </script>

    <script src="/js/push.js"></script>

    @stack('modals')
    @stack('scripts')

</body>

</html>

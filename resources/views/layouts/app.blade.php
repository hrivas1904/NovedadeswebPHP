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
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">

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
    <link rel="stylesheet" href="{{ asset('css/componentes/cssSideBar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/componentes/cssDivAvisos.css') }}">
    <link rel="stylesheet" href="{{ asset('css/componentes/cssCards.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ayuda.css') }}">
</head>

<body>
    <div class="wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">

            <div class="sidebar-header">
                <a class="sidebar-brand" href="{{ route('index') }}">
                    <img src="{{ asset('img/logo-hp3c-white.png') }}" class="logo-full" alt="HP3C">
                    <img src="{{ asset('img/logo-hp3c-icon.png') }}" class="logo-icon" alt="HP3C">
                </a>

                <button
                    type="button"
                    class="sidebar-close-btn"
                    id="sidebarClose"
                    aria-label="Cerrar menú"
                    title="Cerrar menú">
                    <i class="bx bx-x"></i>
                </button>
            </div>

            <div class="sidebar-body">
                <ul class="sidebar-nav">

                    <li class="nav-section-title section-rrhh"><span class="section-dot"></span>Recursos Humanos</li>

                    @if (Auth::user()->rol === 'Administrador/a')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="bx bx-grid-alt"></i>
                            <span class="link-text">Dashboard</span>
                        </a>
                    </li>
                    @endif

                    <li class="nav-item has-submenu">
                        <a class="nav-link" href="#" role="button" aria-expanded="false">
                            <i class="bx bx-user"></i>
                            <span class="link-text">Colaboradores</span>
                        </a>
                        <ul class="submenu">
                            <li><a class="submenu-link" href="{{ route('miLegajo') }}">Mi legajo</a></li>
                            @if (Auth::user()->rol === 'Coordinador/a L2' ||
                            Auth::user()->rol === 'Administrador/a' ||
                            Auth::user()->rol === 'Coordinador/a')
                            <li><a class="submenu-link" href="{{ route('nominaPersonal') }}">Colaboradores</a></li>
                            @endif
                            @if (Auth::user()->rol === 'Coordinador/a L2' || Auth::user()->rol === 'Administrador/a')
                            <li><a class="submenu-link" href="{{ route('calendarioServicios') }}">Calendario recepción</a></li>
                            @endif
                        </ul>
                    </li>

                    <li class="nav-item has-submenu">
                        <a class="nav-link" href="#" role="button" aria-expanded="false">
                            <i class="fa-solid fa-folder-open"></i>
                            <span class="link-text">Novedades</span>
                        </a>
                        <ul class="submenu">
                            <li><a class="submenu-link" href="{{ route('novedades.misNovedades') }}">Mis novedades</a></li>
                            @if (in_array(Auth::user()->rol, ['Administrador/a', 'Coordinador/a', 'Coordinador/a L2']))
                            <li><a class="submenu-link" href="{{ route('controlNovedades') }}">Registro de novedades</a></li>
                            <li><a class="submenu-link" href="{{ route('novedades.historico') }}">Histórico novedades</a></li>
                            @endif
                            <li><a class="submenu-link" href="{{ route('personal.solicitudes') }}">Solicitud adelanto sueldo</a></li>
                        </ul>
                    </li>

                    <!-- ===== SECCIÓN: CALIDAD ===== -->
                    @if (in_array(Auth::user()->rol, ['Administrador/a', 'Supervisor/a Calidad']))
                    <li class="nav-section-title section-calidad"><span class="section-dot"></span>Calidad</li>

                    <li class="nav-item has-submenu">
                        <a class="nav-link" href="#" role="button" aria-expanded="false">
                            <i class="fa-solid fa-award"></i>
                            <span class="link-text">Calidad</span>
                        </a>
                        <ul class="submenu">
                            <li><a class="submenu-link" href="{{ route('encuestasCalidad') }}">Importar encuestas</a></li>
                            <li><a class="submenu-link" href="{{ route('dashboardCalidad') }}">Dashboard</a></li>
                        </ul>
                    </li>
                    @endif

                    <!-- ===== SECCIÓN: ADMINISTRACIÓN ===== -->
                    @if (Auth::user()->rol != 'Colaborador/a')
                    <li class="nav-section-title section-administracion"><span class="section-dot"></span>Administración</li>

                    <li class="nav-item has-submenu">
                        <a class="nav-link" href="#" role="button" aria-expanded="false">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span class="link-text">Administración</span>
                        </a>
                        <ul class="submenu">
                            <li><a class="submenu-link" href="{{ route('pedidosComprasView') }}">Cargar pedido de compras</a></li>
                            <li><a class="submenu-link" href="{{ route('panelAdminView') }}">Pedidos de compras</a></li>
                            @if (in_array(Auth::id(), [1,2,5,6]))
                            <li><a class="submenu-link" href="{{ route('productosProveedoresView') }}">Productos y Proveedores</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- ===== SECCIÓN: AJUSTES ===== -->
                    @if (Auth::user()->rol === 'Administrador/a')
                    <li class="nav-section-title section-ajustes"><span class="section-dot"></span>Ajustes</li>

                    <li class="nav-item has-submenu">
                        <a class="nav-link" href="#" role="button" aria-expanded="false">
                            <i class="fa-solid fa-gear"></i>
                            <span class="link-text">Ajustes</span>
                        </a>
                        <ul class="submenu">
                            <li><a class="submenu-link" href="{{ route('configNovedades') }}">Conceptos novedades</a></li>
                            <li><a class="submenu-link" href="{{ route('administrarUsuarios') }}">Administrar usuarios</a></li>
                            <li><a class="submenu-link" href="{{ route('obraSocial.administrarObraSociales') }}">Obras sociales</a></li>
                            <li><a class="submenu-link" href="{{ route('parametrizacionesGenerales') }}">Parámetros generales</a></li>
                        </ul>
                    </li>
                    @endif

                    <li class="nav-section-title section-general"><span class="section-dot"></span>General</li>

                    <li class="nav-item">
                        <a class="nav-link" href="https://capacitacion.hp3c.com.ar/login/index.php" target="_blank">
                            <i class='bx bx-book'></i>
                            <span class="link-text">Capacitaciones</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('ayuda') }}">
                            <i class="fa-regular fa-circle-question"></i>
                            <span class="link-text">Ayuda</span>
                        </a>
                    </li>

                </ul>
            </div>

            <div class="sidebar-footer p-2">
                <form id="logout-form" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link btn-logout w-100 d-flex align-content-center">
                        <i class="bx bx-log-out fs-5 me-2"></i>
                        <span class="link-text fs-6">Cerrar sesión</span>
                    </button>
                </form>
            </div>

        </aside>

        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- CONTENT -->
        <div class="main-wrapper" id="mainWrapper">

            <header class="topbar">
                <button type="button" class="btn-mobile-toggle" id="mobileSidebarToggle" aria-label="Abrir menú" title="Abrir menú">
                    <i class="fa-solid fa-bars fw-bold fs-3"></i>
                </button>

                <div class="mx-3 d-none d-lg-block text-white">
                    <h4 class="mb-0 fw-bold">GESTIÓN HP3C</h4>
                    <h6 class="mb-0 fw-normal">Plataforma de Gestión Integral</h6>
                </div>

                <div class="topbar-right">

                    <div class="user-chip text-white">
                        <div class="user-info text-end small">
                            <div><strong>{{ Auth::user()->name }}</strong></div>
                            <div class="user-role">{{ Auth::user()->rol }}</div>
                        </div>
                    </div>

                    <button class="btn btn-light position-relative" id="btnAlertas">
                        <i class="fa-solid fa-bell"></i>
                        <span id="contadorAlertas" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">0</span>
                    </button>

                    <div id="dropdownAlertas" class="dropdown-alertas">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="fw-semibold">Notificaciones</span>
                            <button id="btnLimpiarAlertas" class="btn btn-sm text-danger p-0">Limpiar</button>
                        </div>
                        <ul id="listaAlertas" class="list-unstyled mb-0"></ul>
                    </div>

                </div>
            </header>

            <main class="container-fluid content-area">
                @yield('content')
            </main>

        </div>

    </div>


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

    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <script src="{{ asset('js/home/alertas.js') }}"></script>
    <script src="{{ asset('js/home/darkMode.js') }}"></script>
    <script src="{{ asset('js/sideBar.js') }}"></script>

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
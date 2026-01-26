<!DOCTYPE html>
<html lang="es">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <title>@yield('title', 'Sistema')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/styleDt.css') }}">
    <link rel="stylesheet" href="{{ asset('css/botones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modales.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cronograma.css') }}">
    <link rel="stylesheet" href="{{ asset('css/calendario.css') }}">

</head>

<body>
    <div class="sidebar">
        <div class="logo_details">
            <a href="{{ route('index') }}" class="logo-link">
                <img src="{{ asset('img/logo_2.png') }}" alt="Logo" class="logo-img">
            </a>
            <i class="bx bx-menu" id="btn"></i>
        </div>

        <ul class="nav-list">
            @if (Auth::user()->rol === 'Administrador/a')
            <li>
                <a href="{{ route('dashboard') }}">
                    <i class="bx bx-grid-alt"></i>
                    <span class="link_name">Dashboard</span>
                </a>
                <span class="tooltip">Dashboard</span>
            </li>
            @endif

            <li class="submenu">
                <input type="checkbox" id="menu-personal">
                <label for="menu-personal">
                    <i class="bx bx-user"></i>
                    <span class="link_name">Gestión del personal</span>
                    <i class="bx bx-chevron-down arrow"></i>
                </label>
                <ul class="sub-menu">
                    <li><a href="{{ route('nominaPersonal') }}">Nómina de Personal</a></li>
                    @if (Auth::user()->rol === 'Administrador/a')
                    <!--<li><a href="{{ route('registroAsistencia') }}">Registro de Asistencia</a></li>-->
                    <!--<li><a href="{{ route('controlAsistencia') }}">Control de Asistencia</a></li>-->
                    <!--<li><a href="{{ route('cronogramaPersonal') }}">Cronograma del Personal</a></li>-->                    
                    @endif
                    <li><a href="{{ route('calendarioServicios') }}"">Calendario de Servicios</a></li>
                </ul>
                <span class="tooltip">Personal</span>
            </li>


            <li class="submenu">
                <input type="checkbox" id="menu-novedades">
                <label for="menu-novedades">
                    <i class="bx bx-folder"></i>
                    <span class="link_name">Novedades</span>
                    <i class="bx bx-chevron-down arrow"></i>
                </label>
                <ul class="sub-menu">
                    <li><a href="{{ route('controlNovedades') }}">Control de Novedades</a></li>
                    @if (Auth::user()->rol === 'Administrador/a')
                    <li><a href="{{ route('configNovedades') }}">Configuración de Novedades</a></li>
                    @endif
                </ul>
                <span class="tooltip">Novedades</span>
            </li>

            <li class="submenu">
                <input type="checkbox" id="menu-calidad">
                <label for="menu-calidad">
                    <i class='bx  bx-badge-check'></i>
                    <span class="link_name">Gestión de calidad</span>
                    <i class="bx bx-chevron-down arrow"></i>
                </label>
                <ul class="sub-menu">
                    <li><a href="{{ route('dashboardCalidad') }}">Dashboard</a></li>
                    <li><a href="{{ route('cmiCalidad') }}">CMI</a></li>                    
                    <li><a href="{{ route('encuestasCalidad') }}">Encuestas</a></li>
                </ul>
                <span class="tooltip">Calidad</span>
            </li>

            @if (Auth::user()->rol === 'Administrador/a')
            <li class="submenu">
                <input type="checkbox" id="menu-config">
                <label for="menu-config">
                    <i class="bx bx-cog"></i>
                    <span class="link_name">Configuraciones</span>
                    <i class="bx bx-chevron-down arrow"></i>
                </label>
                <ul class="sub-menu">
                    <li><a href="{{ route('ajustes') }}">Configuraciones</a></li>
                    <li><a href="#">Usuarios y Permisos</a></li>
                </ul>
                <span class="tooltip">Configuraciones</span>
            </li>
            @endif

            <li>
                <a href="{{ route('ayuda') }}">
                    <i class='bx  bx-help-circle'></i>
                    <span class="link_name">Ayuda</span>
                </a>
                <span class="tooltip">Ayuda</span>
            </li>

            <div class="branding-footer">
                <div class="branding-profile-box">
                    <div class="branding-user-details">
                        <span class="branding-user-name">{{ Auth::user()->name }}</span>

                        <span class="branding-user-role">
                            {{ Auth::user()->rol }}
                        </span>

                        <span class="branding-user-role">
                            @if (Auth::user()->area)
                            {{ Auth::user()->area->NOMBRE }}
                            @else
                            @endif
                        </span>
                    </div>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>

                    <button type="button" class="branding-logout-trigger"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class='bx bx-log-out'></i>
                    </button>
                </div>
            </div>
        </ul>
    </div>
    <section class="home-section px-2 py-3">
        @yield('content')
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.0/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.bootstrap5.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>



    @stack('modals')
    @stack('scripts')

</body>

</html>
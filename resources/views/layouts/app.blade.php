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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"
        integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="{{ asset('css/styleDt.css') }}">
    <link rel="stylesheet" href="{{ asset('css/botones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modales.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cronograma.css') }}">
    <link rel="stylesheet" href="{{ asset('css/calendario.css') }}">
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">

</head>

<body>
    <nav class="navbar-top">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="{{ route('index') }}">
                    <img src="{{ asset('img/logo_2.png') }}" alt="Logo">
                </a>
            </div>

            <ul class="nav-links">
                @if (Auth::user()->rol === 'Administrador/a')
                    <li>
                        <a href="{{ route('dashboard') }}"><i class="bx bx-grid-alt"></i> Dashboard</a>
                    </li>
                @endif

                <li class="nav-dropdown">
                    <a href="javascript:void(0)" class="dropbtn"><i class="bx bx-user"></i> Personal <i
                            class="bx bx-chevron-down"></i></a>
                    <div class="dropdown-content">
                        <a href="{{ route('nominaPersonal') }}">Personal activo</a>
                        <a href="{{ route('nominaPersonalBaja') }}"">Personal de baja</a>
                        <a href="{{ route('calendarioServicios') }}">Calendario</a>
                    </div>
                </li>

                <li class="nav-dropdown">
                    <a href="javascript:void(0)" class="dropbtn"><i class="bx bx-folder"></i> Novedades <i
                            class="bx bx-chevron-down"></i></a>
                    <div class="dropdown-content">
                        <a href="{{ route('controlNovedades') }}">Control</a>
                        @if (Auth::user()->rol === 'Administrador/a')
                            <a href="{{ route('configNovedades') }}">Configuración</a>
                        @endif
                    </div>
                </li>

                <li class="nav-dropdown">
                    <a href="javascript:void(0)" class="dropbtn"><i class='bx bx-badge-check'></i> Calidad <i
                            class="bx bx-chevron-down"></i></a>
                    <div class="dropdown-content">
                        <a href="{{ route('dashboardCalidad') }}">Dashboard SGC</a>
                        <a href="{{ route('cmiCalidad') }}">CMI - BSC</a>
                    </div>
                </li>
            </ul>

            <div class="nav-profile">
                <div class="user-info">
                    <span class="user-name">{{ Auth::user()->name }}</span>
                    <span class="user-role">{{ Auth::user()->rol }}</span>
                    <span class="branding-user-role user-area">
                        @if (Auth::user()->area)
                            {{ Auth::user()->area->NOMBRE }}
                        @else
                        @endif

                        </span>
                </div>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="logout-btn">
                    <i class='bx bx-log-out'></i>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf
                </form>
            </div>

            <i class='bx bx-menu mobile-menu-btn' id="mobile-btn"></i>
        </div>
    </nav>

    <section class="home-section py-3" style="padding: 90px 30px 30px 30px;">
        @yield('content')
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/navbar.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"
        integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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

@section('title', 'Inicio')

<link rel="stylesheet" href="{{ asset('css/home.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="use.fontawesome.com" crossorigin="anonymous">

<div class="hero-container">
    <div class="hero-overlay">

        <h1 class="display-4 mb-5">¡Bienvenido, <strong>{{ Auth::user()->name }}</strong>!</h1>

        <div class="hero-grid">

            <div class="hero-card">
                <button class="hero-btn">
                    <i class="fa-solid fa-users"></i>
                    Gestión del Personal
                </button>
                <div class="hero-submenu">
                    <a href="/nomina">Nómina del Personal</a>
                    @if(Auth::user()->rol === 'Administrador/a')
                    <a href="/registroAsistencia">Registro de Asistencia</a>
                    <a href="/registroAsistencia">Control de Asistencia</a>
                    <a href="/cronograma">Cronograma del Personal</a>
                    @endif
                </div>
            </div>

            
            <div class="hero-card">
                <button class="hero-btn">
                    Gestión de Novedades
                </button>
                <div class="hero-submenu">
                    <a href="/controlNovedades">Control de Novedades</a>
                    @if(Auth::user()->rol === 'Administrador/a')
                    <a href="/configNovedades">Configuración de Novedades</a>
                    @endif
                </div>
            </div>

            @if(Auth::user()->rol === 'Administrador/a')

            <div class="hero-card">
                <button class="hero-btn">
                    <i class="fa-solid fa-users"></i>
                    Gestión del Personal
                </button>
                <div class="hero-submenu">
                    <a href="/ajustes">Configuraciones</a>
                    <a href="#">Usuarios y permisos</a>
                </div>
            </div>


            <div class="hero-card">
                <a href="/dashboard" class="hero-btn simple">
                    Dashboard de Control
                </a>
            </div>

            @endif

        </div>
    </div>

</div>
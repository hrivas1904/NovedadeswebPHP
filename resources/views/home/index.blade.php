@section('title', 'Inicio')

<link rel="stylesheet" href="{{ asset('css/home.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="use.fontawesome.com" crossorigin="anonymous">

<div class="hero">
    <div class="hero-overlay">

        <h1 class="hero-title">
            ¡Hola, <strong>{{ Auth::user()->name }}</strong>!
        </h1>

        <div class="hero-menu">

            <div class="menu-card">
                <button class="menu-btn">
                    <i class="fa-solid fa-users"></i>
                    Gestión del Personal
                </button>

                <div class="menu-dropdown">
                    <a href="/nomina">Nómina del Personal</a>
                    @if(Auth::user()->rol === 'Administrador/a')
                    <a href="/cronograma">Cronograma del Personal</a>
                    @endif
                </div>
            </div>

            <div class="menu-card">
                <button class="menu-btn">
                    Gestión de Novedades
                </button>

                <div class="menu-dropdown">
                    <a href="/controlNovedades">Control de Novedades</a>

                    @if(Auth::user()->rol === 'Administrador/a')
                        <a href="/configNovedades">Configuración de Novedades</a>
                    @endif
                </div>
            </div>

            @if(Auth::user()->rol === 'Administrador/a')
            <div class="menu-card">
                <button class="menu-btn">
                    Configuraciones
                </button>

                <div class="menu-dropdown">
                    <a href="/ajustes">Configuraciones</a>
                    <a href="#">Usuarios y permisos</a>
                </div>
            </div>

            <div class="menu-card">
                <a href="/dashboard" class="menu-btn simple">
                    Dashboard de Control
                </a>
            </div>
            @endif

        </div>
    </div>
</div>

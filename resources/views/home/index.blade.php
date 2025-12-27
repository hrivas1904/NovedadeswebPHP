@section('title', 'Inicio')

<link rel="stylesheet" href="{{ asset('css/home.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="use.fontawesome.com" crossorigin="anonymous">

<div class="hero-container">

    <div class="hero-overlay">

        <div class="hero-grid">

            <div class="hero-card">
                <button class="hero-btn">
                    <i class="fa-solid fa-users"></i>
                    Gestión del Personal
                </button>
                <div class="hero-submenu">
                    <a href="/nomina">Nómina</a>
                    <a href="/registroAsistencia">Asistencia</a>
                    <a href="/cronograma">Cronograma</a>
                </div>
            </div>

            <div class="hero-card">
                <button class="hero-btn">
                    Gestión de Novedades
                </button>
                <div class="hero-submenu">
                    <a href="/registroNovedades">Cargar Novedades</a>
                    <a href="/controlNovedades">Control</a>
                    <a href="/configNovedades">Configuración</a>
                </div>
            </div>

            <div class="hero-card">
                <a href="/ajustes" class="hero-btn simple">
                    Configuración
                </a>
            </div>


            <div class="hero-card">
                <a href="/dashboard" class="hero-btn simple">
                    Dashboard
                </a>
            </div>

        </div>
    </div>

</div>

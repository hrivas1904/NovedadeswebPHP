<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario | Sistema de Gestión</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-default: #004a7c;
            --color-second: #0091d5;
            --color-accent: #00b18d;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f8fafc;
            background-image:
                radial-gradient(at 0% 0%, rgba(0, 74, 124, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(0, 177, 141, 0.08) 0px, transparent 50%);
        }

        .card-usuario {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 550px;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .logo {
            display: block;
            margin: 0 auto 1.6rem;
            max-width: 180px;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h2 {
            color: var(--color-default);
            font-weight: 700;
            font-size: 1.5rem;
        }

        .form-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #475569;
        }

        .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            border-color: var(--color-second);
            box-shadow: 0 0 0 4px rgba(0, 145, 213, 0.1);
        }

        .btn-crear {
            background-color: var(--color-accent);
            color: white;
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 10px;
            border: none;
            width: 100%;
            margin-top: 1rem;
            transition: 0.3s;
        }

        .btn-crear:hover {
            background-color: var(--color-second);
            transform: translateY(-1px);
        }

        .error-message {
            background-color: #fef2f2;
            color: #991b1b;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            border: 1px solid #fee2e2;
        }

        .loader-overlay {
            position: fixed;
            inset: 0;
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .logo-spinner {
            width: 200px;
            animation: pulse 1.5s infinite ease-in-out;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: .6; }
            50% { transform: scale(1.15); opacity: 1; }
            100% { transform: scale(1); opacity: .6; }
        }
    </style>
</head>

<body>

    <div id="loader" class="loader-overlay d-none">
        <img src="{{ asset('img/logo_2.png') }}" class="logo-spinner">
    </div>

    <div class="card-usuario">
        <div class="header">
            <img src="{{ asset('img/logo_2.png') }}" class="logo">
            <h2>Crear Usuario</h2>
        </div>

        @if ($errors->any())
            <div class="error-message">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('usuario.guardar') }}" method="POST" onsubmit="showLoader()">
            @csrf

            <div class="mb-3">
                <label class="form-label">LEGAJO</label>
                <input type="number" name="legajo" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">CONTRASEÑA</label>
                <input id="inputPassword" type="password" name="password" class="form-control" required>

                <div class="d-flex justify-content-end mt-2">
                    <button type="button" id="btn-verPassword"
                        style="background:none;border:none;color:#0b3c6d"
                        onclick="togglePassword()">
                        Mostrar contraseña
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-crear">CREAR USUARIO</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        function togglePassword() {
            const input = document.getElementById('inputPassword');
            const btn = document.getElementById('btn-verPassword');

            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = 'Ocultar contraseña';
            } else {
                input.type = 'password';
                btn.textContent = 'Mostrar contraseña';
            }
        }

        function showLoader() {
            $("#loader").removeClass("d-none");
        }
    </script>

</body>
</html>
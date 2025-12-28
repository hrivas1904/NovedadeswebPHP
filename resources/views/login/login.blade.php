<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Sistema de Gestión</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-default: #004a7c;
            /* Azul Marino Logo */
            --color-second: #0091d5;
            /* Azul Brillante Logo */
            --color-accent: #00b18d;
            /* Verde Logo */
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            /* Gradiente sofisticado con identidad de marca */
            background-color: #f8fafc;
            background-image:
                radial-gradient(at 0% 0%, rgba(0, 74, 124, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(0, 177, 141, 0.08) 0px, transparent 50%);
        }

        .login-card {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 420px;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .login-logo {
            display: block;
            margin: 0 auto 1.5rem;
            max-width: 120px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            color: var(--color-default);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Estilos modernos para los inputs */
        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--color-second);
            box-shadow: 0 0 0 4px rgba(0, 145, 213, 0.1);
            outline: none;
        }

        .btn-login {
            background-color: var(--color-default);
            color: white;
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 10px;
            border: none;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 6px -1px rgba(0, 74, 124, 0.2);
        }

        .btn-login:hover {
            background-color: var(--color-second);
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(0, 145, 213, 0.2);
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
    </style>
</head>

<body>

    <div class="login-card">
        <div class="login-header">
            <img src="{{ asset('img/logo1.png') }}" alt="Empresa Logo" class="login-logo">
            <h2>Bienvenido</h2>
            <p>Por favor, ingresa usuario y contraseña</p>
        </div>

        @if($errors->any())
        <div class="error-message">
            @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">USUARIO</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">CONTRASEÑA</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn-login">INGRESAR</button>
        </form>
    </div>

</body>

</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POSTAR|AGBC</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=5" />
    <link rel="stylesheet" href="{{ asset('css/postal-theme.css') }}">
    <style>
        body {
            font-family: var(--postal-font);
            color: var(--postal-ink);
            box-sizing: border-box;
            padding: 24px;
            background: url('{{ asset('images/Servicio Prioritario foot.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            background-position: center;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
        }
        .logo {
            margin-bottom: 20px;
            text-align: center;
        }
        .logo img {
            max-width: 150px; /* Ajusta el tamaño del logo */
            height: auto;
        }
        .form-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 32px;
            box-sizing: border-box;
            border-radius: 16px;
            border-top: 5px solid var(--postal-blue);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-container label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-container button {
            background-color: var(--postal-yellow);
            font: inherit;
            font-weight: 700;
            min-height: 46px;
            color: var(--postal-ink);
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        .form-container button:hover {
            background-color: var(--postal-yellow-hover);
        }
        h1 { font-size: 24px; letter-spacing: -.6px; }
        .centrado p { color: var(--postal-muted); line-height: 1.5; margin-bottom: 24px; }
        .centrado {
            text-align: center;
        }
        .login-alert {
            margin-bottom: 16px;
            padding: 12px;
            border: 1px solid #b42318;
            border-radius: 4px;
            color: #8a1c13;
            background: #fff1f0;
        }
    </style>
</head>
<body>
    <!-- Logo -->
    <div class="logo">
        <img src="{{ asset('images/correos-bolivia.png') }}" alt="Correos de Bolivia">
    </div>

    <!-- Formulario -->
    <div class="form-container">
        <div class="centrado">
            <h1>Bienvenido a CalcuPost</h1>
            <p>Ingresa para gestionar los servicios y consultas postales.</p>
        </div>
        @if ($errors->any())
            <div class="login-alert" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('login') }}">
            <!-- CSRF Token -->
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <!-- Email Address -->
            <div>
                <label for="email">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            </div>


            <!-- Password -->
            <div>
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" autocomplete="current-password" required>
            </div>

            <!-- Login Button -->
            <div>
                <button type="submit">Iniciar sesión</button>
            </div>
        </form>
    </div>
</body>
</html>

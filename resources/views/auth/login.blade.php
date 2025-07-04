<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora Postal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('/images/Servicio Prioritario foot.jpg') no-repeat center center fixed;
            background-size: cover;
            background-position: center;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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
            padding: 20px;
            border-radius: 8px;
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
            background-color: #34447C;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        .form-container button:hover {
            background-color: #B99C46;
        }
        .centrado {
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Logo -->
    <div class="logo">
        <img src="{{ asset('images/AGBClogo.png') }}" alt="Logo">
    </div>

    <!-- Formulario -->
    <div class="form-container">
        <div class="centrado">
            <h2>HERRAMIENTA POSTAL PARA EL CÁLCULO PRECISO DE TARIFAS</h2>
            <h3>"POSTAR"</h3>
        </div>
        <form method="POST" action="{{ route('login') }}">
            <!-- CSRF Token -->
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <!-- Email Address -->
            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" required autofocus>
            </div>


            <!-- Password -->
            <div>
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>

            <!-- Login Button -->
            <div>
                <button type="submit">Log in</button>
            </div>
        </form>
    </div>
</body>
</html>
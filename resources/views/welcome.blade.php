<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>AGBC | CalcuPost</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-image: url('fondo.jpg');
            background-size: cover;
            background-position: center;
            font-family: 'Figtree', sans-serif;
        }

        .container {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            min-height: 100vh;
            padding: 0 20px;
        }

        .form-register {
            width: 400px;
            background: #24303c;
            padding: 30px;
            border-radius: 8px;
            font-family: 'Calibri', sans-serif;
            color: white;
            box-shadow: 7px 13px 37px rgba(0, 0, 0, 0.5);
            margin-right: 300px; /* Ajusta esta distancia */
        }

        .form-register h4 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .controls {
            width: 100%;
            background: #1e2a36;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 16px;
            border: 1px solid #1f53c5;
            font-size: 16px;
            color: white;
        }

        .form-register .botons {
            width: 100%;
            background: #1f53c5;
            border: none;
            padding: 12px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .form-register .botons:hover {
            background: #1741a1;
        }
    </style>
    @livewireStyles
</head>

<body>
    <div class="container">
        <section class="form-register">
            <h4>Calculadora Postal</h4>
            <input class="controls" type="text" name="pais" id="pais" placeholder="Ingrese el País">
            <input class="controls" type="text" name="ciudad" id="ciudad" placeholder="Ingrese la Ciudad de Envío">
            <input class="controls" type="text" name="servicio" id="servicio" placeholder="Ingrese un Servicio Postal">
            <input class="controls" type="number" name="peso" id="peso" placeholder="Ingrese el Peso Aprox. de su Paquete">
            <input class="botons" type="submit" value="Calcular">
        </section>
    </div>
</body>

</html>

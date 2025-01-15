<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>AGBC | CalcuPost</title>

    <link rel="icon" type="image/png" href="vendor/adminlte/dist/img/AGBClogo.png" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
            overflow-x: auto; /* Scroll horizontal si no cabe */
            /* overflow-y: hidden; Evita scroll vertical si no lo quieres */
        }

        body {
            background: url('/images/Fondo calculadora-3.jpg') no-repeat center center fixed;
            background-size: cover;
            background-position: center;
            font-family: 'Figtree', sans-serif;
        }

        .icono-mascota {
            position: absolute;
            width: 200px;
            height: auto;
            right: 120px;
            bottom: 0 !important;
            z-index: 9999;
        }

        .icono-mascota img {
            width: 100%;
            height: auto;
        }

        .container {
            display: flex;
            justify-content: flex-end !important;
            align-items: center;
            padding: 20px;
            flex-wrap: nowrap;
            position: relative;
        }

        .form-register {
            margin-top: 250px;
            width: 100%;
            max-width: 400px;
            background: #24303c;
            padding: 30px;
            border-radius: 8px;
            font-family: 'Calibri', sans-serif;
            color: white;
            box-shadow: 7px 13px 37px rgba(0, 0, 0, 0.5);
            margin: 50px;
            margin-left: auto !important;
            margin-right: 250px;
            margin-bottom: 100px;
        }

        .form-register h4 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
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

        .container h4 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }

        .container h3 {
            font-size: 1rem;
            margin: 15px 0 5px;
        }

        .resultado {
            margin-top: 20px;
            padding: 15px;
            background: #ffffff;
            border: 2px solid #2563eb;
            border-radius: 8px;
            text-align: center;
            color: #1f2937;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .resultado h5 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #B99C46;
        }

        .resultado p {
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0;
            color: #942d5c;
        }

        .image-container img {
            width: 100%;
            max-width: 400px;
            height: auto;
            border-radius: 8px;
            margin: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0);
        }

        .dimension-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 16px;
        }

        .dimension-item {
            flex: 1;
            min-width: 30%;
        }

        .controls {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        footer {
            text-align: center;
            background-color: #24303c;
            color: white;
            padding: 10px 20px;
            font-size: 0.9rem;
        }


        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                align-items: center;
            }

            .form-register {
                margin: 0;
            }

            .dimension-container {
                gap: 10px;
            }

            .dimension-item {
                width: 100%;
            }

            .icono-mascota img {
                margin: 10px;
                max-width: 300px;
            }

            .icono-mascota {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .form-register {
                padding: 20px;
            }

            .form-register h4 {
                font-size: 20px;
            }

            .controls {
                font-size: 14px;
            }

            .form-register .botons {
                font-size: 14px;
            }

            .resultado h5 {
                font-size: 1rem;
            }

            .resultado p {
                font-size: 1.2rem;
            }
        }
    </style>

    @livewireStyles
</head>

<body>
    <div class="container">
        @livewire('calculadora')
    </div>
    <footer>
        <p class="mb-2"><small>#EstamosSaliendoAdelante #RumboalBicentenario</small></p>
        <p class="mb-2"><small>&copy; {{ date('Y') }} Todos los derechos reservados - Agencia
                Boliviana de Correos <a href="mailto:mespinozarojas46@gmail.com" class="opacity-75"
                    title="Marco Antonio Espinoza Rojas">Copyright © MAER
                    {{ date('Y') }} </a></small></p>
        <p class="mb-2"><small>Contacto: (591-2) 2152423 - Av. Mariscal Santa Cruz Esq. C. Oruro Edif.
                Telecomunicaciones - agbc@correos.gob.bo</small></p>
    </footer>
</body>

</html>

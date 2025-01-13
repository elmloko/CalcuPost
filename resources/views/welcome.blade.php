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

        body {
            background: url('/images/Encomienda Postal.jpg') no-repeat center center fixed;
            background-size: cover;
            background-position: center;
            font-family: 'Figtree', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            flex-wrap: wrap;
            /* Permite que los elementos se acomoden en varias filas */
        }

        .form-register {
            width: 100%;
            max-width: 400px;
            background: #24303c;
            padding: 30px;
            border-radius: 8px;
            font-family: 'Calibri', sans-serif;
            color: white;
            box-shadow: 7px 13px 37px rgba(0, 0, 0, 0.5);
            margin: 20px;
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
            max-width: 500px;
            height: auto;
            border-radius: 8px;
            margin: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0);
        }

        .dimension-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            /* Espaciado entre los elementos */
            margin-bottom: 16px;
        }

        .dimension-item {
            flex: 1;
            /* Asegura que todos los elementos tengan el mismo ancho */
            min-width: 30%;
            /* Asegura que los elementos sean lo suficientemente grandes en pantallas pequeñas */
        }

        .controls {
            width: 100%;
            /* Asegura que los inputs ocupen el ancho completo del contenedor */
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 16px;
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

            .image-container img {
                margin: 10px;
                max-width: 300px;
            }

            .image-container {
                display: none;
                /* Oculta el contenedor de la imagen */
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
    @livewire('calculadora')
</body>

</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Calculadora Postal | Correos de Bolivia</title>

    <link rel="icon" type="image/png" href="vendor/adminlte/dist/img/AGBClogo.png" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800|sora:400,600,700,800&display=swap" rel="stylesheet" />

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { min-height: 100%; }

        :root {
            --cb-yellow: #f4c52a;
            --cb-blue: #0f4aa1;
            --cb-blue-deep: #0b3f8b;
            --cb-ink: #153257;
            --cb-page: #f4f7fc;
            --cb-surface: #ffffff;
            --cb-border: #dde5f0;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(180deg, #fbfcff 0%, var(--cb-page) 100%);
            color: var(--cb-ink);
        }

        .layout {
            width: min(1320px, calc(100% - 24px));
            margin: 10px auto 14px;
            display: grid;
            grid-template-columns: 42% 58%;
            gap: 16px;
        }

        .hero {
            min-height: 620px;
            border-radius: 20px;
            border: 1px solid var(--cb-border);
            box-shadow: 0 12px 28px rgba(15, 74, 161, 0.08);
            background: radial-gradient(circle at 15% 5%, #fff 0, #f5f8fe 60%, #ebf1fb 100%);
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .hero h1 { font-size: 54px; line-height: .95; color: var(--cb-blue); margin-bottom: 8px; }
        .hero h2 { font-size: 34px; color: #254f83; margin-bottom: 16px; }
        .hero p { font-size: 23px; max-width: 420px; color: #36557c; }
        .hero-wave {
            position: absolute;
            left: -80px;
            right: -80px;
            bottom: -120px;
            height: 420px;
            background:
                radial-gradient(ellipse at 26% 0, var(--cb-yellow) 0, var(--cb-yellow) 53%, transparent 54%),
                radial-gradient(ellipse at 71% 15%, var(--cb-blue) 0, var(--cb-blue) 55%, transparent 56%);
        }

        .hero img {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 0;
            max-height: 500px;
            z-index: 2;
        }

        .container { display: block; }

        .form-register {
            min-height: 620px;
            width: 100%;
            background: var(--cb-surface);
            border: 1px solid var(--cb-border);
            border-radius: 20px;
            box-shadow: 0 14px 34px rgba(15, 74, 161, 0.10);
            padding: 30px 32px;
            position: relative;
        }

        .form-register::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            border-radius: 20px 20px 0 0;
            background: linear-gradient(90deg, var(--cb-yellow) 0 80%, var(--cb-blue) 80% 100%);
        }

        .form-register h4 {
            font-size: 34px;
            color: var(--cb-blue);
            padding-bottom: 12px;
            margin-bottom: 20px;
            border-bottom: 2px solid #f0d778;
        }

        .container h3 {
            color: var(--cb-blue);
            font-size: 17px;
            margin: 16px 0 8px;
            font-weight: 700;
        }

        .controls {
            width: 100%;
            border: 1px solid #d4dfef;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 16px;
            color: #1f3b61;
            margin-bottom: 16px;
            background: #fff;
        }

        .controls:focus { outline: none; border-color: var(--cb-yellow); box-shadow: 0 0 0 3px rgba(244, 197, 42, .30); }

        .dimension-container { display: flex; gap: 12px; margin-bottom: 16px; }
        .dimension-item { flex: 1; }

        .form-register label { font-size: 16px; color: #203f68; }

        .form-register .botons {
            width: 100%;
            border: 1px solid #d4a000;
            border-radius: 12px;
            background: var(--cb-yellow);
            color: #332600;
            font-size: 22px;
            font-weight: 800;
            padding: 13px;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .form-note {
            margin-top: 6px;
            font-size: 14px;
            line-height: 1.4;
            color: #1a2f4b;
        }

        .resultado {
            margin-top: 16px;
            border: 1px solid #d9e3f1;
            border-radius: 12px;
            background: #f8fbff;
            padding: 16px;
        }

        .resultado h5 { font-size: 20px; color: var(--cb-blue); }
        .resultado p { font-size: 30px; color: var(--cb-blue-deep); font-weight: 800; }


        .site-footer {
            margin-top: 22px;
            background: radial-gradient(540px 240px at 0% 0%, rgba(255, 255, 255, .45), transparent 72%), linear-gradient(135deg, #FECC36 0%, #FECC36 58%, #FECC36 100%);
            color: var(--cb-blue);
            border-top: 2px solid rgba(4, 30, 66, .30);
            font-family: "Sora", "Figtree", sans-serif;
            line-height: 1.4;
            padding-top: 30px;
            padding-bottom: 14px;
        }
        .site-footer, .site-footer * { box-sizing: border-box; }
        .container-footer { width: min(1140px, calc(100% - 24px)); margin: 0 auto; }
        .footer-grid {
            display: grid;
            grid-template-columns: 1.2fr repeat(5, 1fr);
            gap: 1.15rem;
            padding: 10px 0 30px;
        }
        .footer-col h4 {
            font-size: .84rem;
            margin-bottom: .58rem;
            letter-spacing: .7px;
            text-transform: uppercase;
            color: var(--cb-blue);
            font-weight: 700;
        }
        .footer-col ul {
            list-style: none;
            display: grid;
            gap: .34rem;
            font-size: .88rem;
            color: rgba(4,30,66,.88);
            margin: 0;
            padding: 0;
            font-weight: 400;
        }
        .footer-col li { transition: color .2s cubic-bezier(.2, .75, .2, 1); }
        .footer-col li:hover { color: #1c58a7; }
        .footer-col a, .footer-col a:visited { color: inherit; text-decoration: none; }
        .footer-col a:hover, .footer-col a:focus-visible { color: #1c58a7; text-decoration: none; }
        .footer-brand img { width: 220px; height: 130px; object-fit: contain; display: block; }
        .footer-contact {
            border-top: 1px solid rgba(4, 30, 66, .22);
            border-bottom: 1px solid rgba(4, 30, 66, .22);
            padding: .85rem 0;
            text-align: center;
            color: rgba(4,30,66,.95);
            font-size: .9rem;
            background: rgba(255,255,255,.35);
            font-weight: 400;
        }
        .footer-copy { text-align: center; font-size: .9rem; color: rgba(4,30,66,.86); padding: .72rem 0 .5rem; font-weight: 400; }
        .footer-copy-tight { padding-top: 0; padding-bottom: 1rem; }

        @media (max-width: 1100px) {
            .layout { grid-template-columns: 1fr; }
            .hero { min-height: 420px; }
            .hero h1 { font-size: 40px; }
            .hero h2 { font-size: 28px; }
            .hero p { font-size: 20px; }
            .hero img { max-height: 320px; }
            .form-register { min-height: auto; }
            .footer-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 680px) {
            .form-register h4 { font-size: 28px; }
            .container h3, .controls, .form-register label { font-size: 15px; }
            .form-register .botons { font-size: 20px; }
            .container-footer { width: min(1140px, calc(100% - 20px)); }
        }
    </style>

    @livewireStyles
</head>

<body>
    <section class="layout">
        <aside class="hero">
            <h1>Calcula tu tarifa</h1>
            <h2>Rápido, fácil y confiable</h2>
            <p>Obtén el costo estimado de tu envío nacional o internacional en pocos pasos.</p>
            <div class="hero-wave"></div>
            <img src="{{ asset('images/Monito calculadora.png') }}" alt="Mascota Correos">
        </aside>

        <div class="container">
            @livewire('calculadora')
        </div>
    </section>

    <footer class="site-footer" id="contacto">
        <div class="container-footer footer-contact">
            Contacto: (591-2) 2152423 - Av. Mariscal Santa Cruz Esq. C. Oruro Edif. Telecomunicaciones - agbc@correos.gob.bo
        </div>
        <div class="footer-copy">&copy; 2026 Todos los derechos reservados - Correos de Bolivia</div>
        <div class="footer-copy footer-copy-tight">Copyright &copy; MAER 2026</div>

        {{-- Script original deshabilitado por CSP en este proyecto --}}
        {{--
        <script src="https://chatbot.correos.gob.bo:5000/widget-embed.js" data-lang="es" data-position="right" defer></script>
        --}}
    </footer>

    <script async src="https://www.googletagmanager.com/gtag/js?id=G-3K53BSN7HD"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-3K53BSN7HD');
    </script>
</body>

</html>

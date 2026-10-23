<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Calculadora Postal | Correos de Bolivia</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=5" />
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=5" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800|sora:400,600,700,800&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('css/postal-theme.css') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { min-height: 100%; }

        :root {
            --cb-yellow: var(--postal-yellow);
            --cb-blue: var(--postal-blue);
            --cb-blue-deep: #0b3f8b;
            --cb-ink: var(--postal-ink);
            --cb-border: var(--postal-line);
        }
        body { font-family: var(--postal-font); background: var(--postal-page); color: var(--cb-ink); }
        .site-header { background: #fecb34; border-top: 5px solid #fecb34; border-bottom: 1px solid #dfb22d; }
        .site-header__inner { width: min(1280px, calc(100% - 48px)); margin: auto; min-height: 76px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .site-brand { display: flex; align-items: center; gap: 20px; color: var(--cb-blue-deep); text-decoration: none; }
        .site-brand img { width: 104px; height: 40px; object-fit: contain; }
        .site-brand strong { border-left: 1px solid rgba(20, 47, 80, .2); padding-left: 20px; font-family: 'Sora', sans-serif; font-size: 20px; letter-spacing: -.8px; }
        .site-header__contact { color: var(--cb-blue); font-size: 14px; font-weight: 600; text-underline-offset: 4px; }
        .layout { width: min(1280px, calc(100% - 48px)); margin: 28px auto 36px; display: grid; grid-template-columns: minmax(0, .85fr) minmax(0, 1.15fr); gap: 24px; align-items: start; }
        .hero { border: 1px solid #d4dfef; border-radius: 22px; background: linear-gradient(120deg, #fff, #f1f5fd); overflow: hidden; }
        .hero-copy { min-height: 340px; padding: 40px 36px 64px; }
        .hero h1 { font-family: 'Sora', sans-serif; font-size: clamp(36px, 3.4vw, 50px); font-weight: 800; line-height: 1.12; letter-spacing: -1.6px; color: #0f4aa1; margin-bottom: 12px; }
        .hero h2 { font-size: clamp(22px, 2.2vw, 30px); font-weight: 700; line-height: 1.2; margin-bottom: 20px; color: #0b3f8b; }
        .hero p { font-size: 20px; line-height: 1.45; color: #153257; }
        .hero-art { height: 310px; position: relative; }
        .hero-wave { position: absolute; inset: 0; background: radial-gradient(ellipse 95% 140% at 0 0, #f4c52a 0 98%, #dcb435 99%, transparent 100%), #164b91; }
        .hero img { position: absolute; left: 50%; transform: translateX(-50%); bottom: 0; height: 340px; width: 100%; object-fit: contain; z-index: 1; }
        .container { min-width: 0; }
        .form-register { width: 100%; background: #fff; border: 1px solid var(--cb-border); border-top: 5px solid var(--cb-yellow); border-radius: 16px; padding: 28px 32px; }
        .calculator-heading { padding-bottom: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--cb-border); }
        .calculator-heading h2 { font-family: 'Sora', sans-serif; color: var(--cb-blue-deep); font-size: 25px; letter-spacing: -.8px; margin: 0 0 8px; }
        .calculator-heading p { color: var(--postal-muted); font-size: 14px; line-height: 1.5; }
        .field-label, .section-label { display: block; font-size: 14px; font-weight: 800; margin: 20px 0 8px; color: var(--cb-blue-deep); }
        .field-label:first-of-type { margin-top: 0; }
        .controls { width: 100%; min-width: 0; min-height: 46px; border: 1px solid #bbc8d7; border-radius: 8px; padding: 11px 12px; font: inherit; font-size: 15px; color: var(--cb-ink); background: #fff; transition: border-color .16s ease, box-shadow .16s ease; }
        .controls:hover:not(:disabled) { border-color: var(--cb-blue); }
        .controls:focus { outline: none; border-color: var(--cb-blue); box-shadow: 0 0 0 3px rgba(22,75,145,.13); }
        .controls:disabled { background: #f2f4f6; color: var(--postal-muted); cursor: not-allowed; }
        .dimension-container { display: flex; gap: 12px; }
        .dimension-item { flex: 1; min-width: 0; }
        .dimension-item > label:not(.additional-option) { display: block; font-size: 12px; font-weight: 700; color: var(--cb-blue-deep); margin-bottom: 6px; }
        .section-label span { font-weight: 400; color: var(--postal-muted); }
        .additional-option { min-height: 46px; display: flex; align-items: center; gap: 9px; font-size: 14px; font-weight: 700; color: var(--cb-blue-deep); padding: 10px 12px; border: 1px solid var(--cb-border); border-radius: 8px; cursor: pointer; }
        .additional-option:has(input:checked) { background: var(--postal-yellow-soft); border-color: #d3aa1b; }
        .additional-option input { width: 17px; height: 17px; flex-shrink: 0; accent-color: var(--cb-blue); }
        .form-register .botons { min-height: 48px; border: 1px solid #ddb117; border-radius: 8px; background: var(--cb-yellow); color: var(--cb-ink); font: inherit; font-weight: 800; cursor: pointer; transition: background .16s ease; }
        .form-register .botons:hover { background: var(--postal-yellow-hover); }
        .form-note { margin-top: 8px; font-size: 12px; line-height: 1.5; color: var(--postal-muted); }
        .resultado { margin-top: 24px; border: 1px solid #dfbf55; border-left: 5px solid var(--cb-yellow); border-radius: 8px; background: var(--postal-yellow-soft); padding: 20px; overflow-wrap: anywhere; }
        .resultado h3 { font-size: 15px; color: var(--cb-ink); margin-bottom: 8px; }
        .resultado p { font-size: 32px; color: var(--cb-blue-deep); font-weight: 800; font-variant-numeric: tabular-nums; }
        .resultado small { display: block; margin-top: 8px; line-height: 1.5; }
        @media (prefers-reduced-motion: no-preference) {
            .layout { animation: postal-enter .3s ease-out; }
            .resultado { animation: postal-enter .2s ease-out; }
        }

        .site-footer {
            margin-top: 22px;
            background: radial-gradient(540px 240px at 0% 0%, rgba(255, 255, 255, .45), transparent 72%), linear-gradient(135deg, var(--cb-yellow) 0%, var(--cb-yellow) 58%, var(--cb-yellow) 100%);
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
        .footer-brand img { width: 290px; height: 170px; object-fit: contain; display: block; }
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

        @media (max-width: 1000px) {
            .layout { grid-template-columns: minmax(0, 1fr); }
            .hero-copy { min-height: 300px; padding: 36px 40px 64px; }
            .hero h1 { font-size: 48px; }
            .hero h2 { font-size: 30px; }
            .hero p { max-width: 520px; font-size: 22px; }
            .hero-art { height: 340px; }
            .hero img { height: 370px; }
        }
        @media (max-width: 600px) {
            .site-header__inner, .layout { width: calc(100% - 24px); }
            .site-header__inner { min-height: 64px; }
            .site-brand { gap: 12px; }
            .site-brand strong { font-size: 17px; padding-left: 12px; }
            .site-brand img { width: 80px; }
            .site-header__contact { font-size: 12px; }
            .layout { margin: 16px auto 24px; gap: 16px; }
            .hero-copy { min-height: 0; padding: 28px 24px 64px; }
            .hero h1 { font-size: clamp(30px, 7.8vw, 44px); letter-spacing: -1.2px; }
            .hero h2 { font-size: clamp(21px, 5.5vw, 28px); }
            .hero p { font-size: 18px; }
            .hero-art { height: 260px; }
            .hero img { height: 290px; }
            .form-register { padding: 24px 18px; }
            .calculator-heading h2 { font-size: 22px; }
            .dimension-container { gap: 8px; }
            .additional-services { flex-direction: column; }
            .calculator-actions { align-items: stretch; }
            .calculator-actions .botons { flex-basis: 100%; }
            .calculator-clear { width: 100%; }
            .site-footer { padding: 20px 12px 8px; }
            .footer-contact { line-height: 1.8; }
        }
    </style>

    @livewireStyles
</head>

<body>
    <header class="site-header">
        <div class="site-header__inner">
            <a class="site-brand" href="{{ url('/') }}" aria-label="CalcuPost · Inicio">
                <img src="{{ asset('images/correos-bolivia.png') }}" alt="Correos de Bolivia" width="104" height="40">
                <strong>CalcuPost</strong>
            </a>
            <a class="site-header__contact" href="#contacto">Contacto</a>
        </div>
    </header>
    <main class="layout" aria-label="Cotiza tu envío postal">
        <aside class="hero">
            <div class="hero-copy">
                <h1>Calcula tu tarifa</h1>
                <h2>Rápido, fácil y confiable</h2>
                <p>Obtén el costo estimado de tu envío nacional o internacional en pocos pasos.</p>
            </div>
            <div class="hero-art">
                <div class="hero-wave" aria-hidden="true"></div>
                <img src="{{ asset('images/Monito calculadora25.png') }}" alt="Mascota Correos">
            </div>
        </aside>

        <div class="container">
            @livewire('calculadora')
        </div>
    </main>

    <footer class="site-footer" id="contacto">
        <div class="container-footer footer-contact">
            Contacto: (591-2) 2152423 - Av. Mariscal Santa Cruz Esq. C. Oruro Edif. Telecomunicaciones - agbc@correos.gob.bo
        </div>
        <div class="footer-copy">&copy; 2026 Todos los derechos reservados - Correos de Bolivia</div>
        <div class="footer-copy footer-copy-tight">Copyright &copy; MAER 2026</div>

    </footer>

    <script async src="https://www.googletagmanager.com/gtag/js?id=G-3K53BSN7HD"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-3K53BSN7HD');
    </script>
    @livewireScripts
</body>

</html>

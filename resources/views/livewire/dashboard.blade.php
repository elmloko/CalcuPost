<section class="postal-dashboard">
    <header class="postal-dashboard__header">
        <div>
            <span class="postal-dashboard__eyebrow">POSTAR · CENTRO DE CONTROL</span>
            <p>Estado consolidado de usuarios, tarifas y cobertura postal.</p>
        </div>
        <div class="postal-dashboard__stamp">
            <span>Datos actuales</span>
            <strong>{{ \App\Support\PostalTime::now()->format('d/m/Y') }}</strong>
        </div>
    </header>

    <div class="postal-dashboard__metrics">
        <article class="metric-card metric-card--blue">
            <span>Tarifas registradas</span>
            <strong>{{ number_format($totalTarifas) }}</strong>
            <small>{{ $categorias->count() }} servicios visibles</small>
        </article>
        <article class="metric-card metric-card--yellow">
            <span>Usuarios activos</span>
            <strong>{{ number_format($usuariosActivos) }}</strong>
            <small>{{ $usuarios }} usuarios en total</small>
        </article>
        <article class="metric-card metric-card--white">
            <span>Alcance de peso</span>
            <strong>{{ rtrim(rtrim(number_format((float) $pesoMaximo, 3, '.', ''), '0'), '.') }} kg</strong>
            <small>máximo configurado</small>
        </article>
        <article class="metric-card metric-card--white">
            <span>Administración</span>
            <strong>{{ $administradores }}</strong>
            <small>cuentas con control total</small>
        </article>
    </div>

    @php
        $maxTramos = max(1, $categorias->max('tarifas'));
        $porcentajeActivos = $usuarios ? round(($usuariosActivos / $usuarios) * 100) : 0;
    @endphp
    <div class="postal-dashboard__charts">
        <article class="postal-panel chart-panel">
            <div class="postal-panel__head">
                <div>
                    <span class="panel-kicker">GRÁFICA DE COBERTURA</span>
                    <h2>Tramos por servicio</h2>
                </div>
                <span class="chart-note">{{ $filtro === 'todos' ? 'Todos los servicios' : ucfirst($filtro) }}</span>
            </div>
            <div class="bar-chart">
                @foreach ($categorias->sortByDesc('tarifas')->take(6) as $categoria)
                    <div class="bar-chart__item">
                        <span>{{ $categoria->categoria }}</span>
                        <div><i style="height: {{ max(12, round(($categoria->tarifas / $maxTramos) * 100)) }}%"></i></div>
                        <b>{{ $categoria->tarifas }}</b>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="postal-panel user-chart">
            <span class="panel-kicker">GRÁFICA DE USUARIOS</span>
            <h2>Estado de cuentas</h2>
            <div class="user-chart__content">
                <div class="donut" style="--activos: {{ $porcentajeActivos }}%;"><b>{{ $porcentajeActivos }}%</b><span>activos</span></div>
                <div class="chart-legend">
                    <p><i class="legend-active"></i> Activos <b>{{ $usuariosActivos }}</b></p>
                    <p><i class="legend-inactive"></i> Inactivos <b>{{ $usuariosInactivos }}</b></p>
                    <small>Actualización en tiempo real al recargar.</small>
                </div>
            </div>
        </article>
    </div>

    <div class="postal-dashboard__grid">
        <article class="postal-panel postal-panel--coverage">
            <div class="postal-panel__head">
                <div>
                    <span class="panel-kicker">COBERTURA TARIFARIA</span>
                    <h2>Servicios disponibles</h2>
                </div>
                <div class="filter-group" aria-label="Filtrar servicios">
                    <button wire:click="filtrar('todos')" class="{{ $filtro === 'todos' ? 'is-active' : '' }}">Todos</button>
                    <button wire:click="filtrar('nacional')" class="{{ $filtro === 'nacional' ? 'is-active' : '' }}">Nacional</button>
                    <button wire:click="filtrar('internacional')" class="{{ $filtro === 'internacional' ? 'is-active' : '' }}">Internacional</button>
                </div>
            </div>

            <div class="service-list">
                @forelse ($categorias as $categoria)
                    @php($porcentaje = $totalTarifas ? round(($categoria->tarifas / $totalTarifas) * 100) : 0)
                    <button wire:click="seleccionarCategoria('{{ $categoria->categoria }}')"
                        class="service-row {{ $categoriaSeleccionada === $categoria->categoria ? 'is-selected' : '' }}">
                        <span class="service-row__name">{{ $categoria->categoria }}</span>
                        <span class="service-row__track"><i style="width: {{ max(10, $porcentaje) }}%"></i></span>
                        <strong>{{ $categoria->tarifas }}</strong>
                    </button>
                @empty
                    <p class="empty-state">No hay servicios para este filtro.</p>
                @endforelse
            </div>
        </article>

        <aside class="postal-panel postal-panel--detail">
            <span class="panel-kicker">DETALLE DEL SERVICIO</span>
            @if ($seleccionada)
                <h2>{{ $seleccionada->categoria }}</h2>
                <div class="detail-number">{{ $seleccionada->tarifas }}</div>
                <p>tramos tarifarios configurados</p>
                <dl>
                    <div><dt>Peso mínimo</dt><dd>{{ $seleccionada->peso_minimo }} kg</dd></div>
                    <div><dt>Peso máximo</dt><dd>{{ $seleccionada->peso_maximo }} kg</dd></div>
                    <div><dt>Modalidad</dt><dd>{{ str_contains($seleccionada->categoria, 'INT') || $seleccionada->categoria === 'ENCOMIENDA' ? 'Internacional' : 'Nacional' }}</dd></div>
                </dl>
            @else
                <h2>Selecciona un servicio</h2>
                <p class="detail-empty">Elige una fila para ver su rango de peso y cantidad de tramos.</p>
            @endif
        </aside>
    </div>

    <div class="postal-dashboard__footer">
        <span><b>{{ $usuariosInactivos }}</b> usuarios inactivos</span>
        <span><b>{{ number_format($totalTarifas) }}</b> precios listos para cotizar</span>
        <a href="{{ url('/tarifario') }}">Administrar tarifario</a>
    </div>
<style>
    .postal-dashboard { color: #153257; max-width: 1400px; margin: 0 auto; padding: .25rem 0 1.5rem; }
    .postal-dashboard__header { align-items: end; border-bottom: 1px solid #d9e4f2; display: flex; justify-content: space-between; padding: .5rem 0 1.35rem; }
    .postal-dashboard__eyebrow, .panel-kicker { color: #0f4aa1; font-size: .7rem; font-weight: 800; letter-spacing: .14em; }
    .postal-dashboard__header p { color: #587292; margin: .8rem 0 0; }
    .postal-dashboard__stamp { border-left: 3px solid var(--postal-yellow); color: #587292; padding-left: .8rem; text-align: right; }
    .postal-dashboard__stamp span { display: block; font-size: .78rem; }
    .postal-dashboard__stamp strong { color: #0f4aa1; font-size: 1.05rem; }
    .postal-dashboard__metrics { display: grid; gap: 1rem; grid-template-columns: repeat(4, minmax(0, 1fr)); margin: 1.5rem 0; }
    .metric-card { background: #fff; border: 1px solid #d9e4f2; border-radius: 14px; min-height: 136px; padding: 1.15rem 1.25rem; position: relative; }
    .metric-card::before { background: #0f4aa1; border-radius: 99px; content: ""; height: 7px; left: 1.25rem; position: absolute; top: 0; width: 42px; }
    .metric-card span, .metric-card small { color: #587292; display: block; font-size: .82rem; }
    .metric-card strong { display: block; font-size: 2rem; letter-spacing: -.05em; margin: .35rem 0; }
    .metric-card--blue { background: var(--postal-yellow-soft); border-color: #e6d69a; color: var(--postal-ink); }
    .metric-card--blue span, .metric-card--blue small { color: var(--postal-muted); }.metric-card--blue::before { background: var(--postal-yellow); }
    .metric-card--yellow { background: var(--postal-yellow); border-color: #e1b005; color: #332600; }.metric-card--yellow span, .metric-card--yellow small { color: #5c4900; }.metric-card--yellow::before { background: #0f4aa1; }
    .postal-dashboard__charts { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1.8fr) minmax(280px, .8fr); margin-bottom: 1rem; }.chart-panel h2, .user-chart h2 { font-size: 1.1rem; }.chart-note { color: #587292; font-size: .75rem; font-weight: 700; }.bar-chart { align-items: end; border-bottom: 1px solid #d9e4f2; display: flex; gap: .6rem; height: 180px; padding: .8rem .2rem 0; }.bar-chart__item { align-items: center; display: grid; flex: 1; grid-template-rows: 1fr 30px 18px; min-width: 0; text-align: center; }.bar-chart__item > span { align-self: end; color: #587292; font-size: .65rem; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }.bar-chart__item > div { align-items: end; background: #edf4ff; display: flex; height: 100%; justify-content: center; margin: .45rem 0 0; }.bar-chart__item i { background: #dfb018; border-radius: 5px 5px 0 0; display: block; transition: height .25s ease; width: min(32px, 75%); }.bar-chart__item b { color: #0f4aa1; font-size: .75rem; padding-top: .2rem; }.user-chart__content { align-items: center; display: flex; gap: 1rem; margin-top: 1.3rem; }.donut { align-items: center; background: conic-gradient(#0f4aa1 0 var(--activos), #d9e4f2 var(--activos) 100%); border-radius: 50%; display: flex; flex: 0 0 126px; flex-direction: column; height: 126px; justify-content: center; position: relative; width: 126px; }.donut::before { background: #fff; border-radius: inherit; content: ""; inset: 16px; position: absolute; }.donut b, .donut span { position: relative; z-index: 1; }.donut b { color: #0f4aa1; font-size: 1.25rem; }.donut span { color: #587292; font-size: .65rem; }.chart-legend { flex: 1; }.chart-legend p { color: #49678c; display: flex; font-size: .8rem; justify-content: space-between; margin: .5rem 0; }.chart-legend i { border-radius: 50%; height: 9px; margin: .25rem .4rem 0 0; width: 9px; }.chart-legend p i { margin-right: auto; }.chart-legend .legend-active { background: #0f4aa1; }.chart-legend .legend-inactive { background: #d9e4f2; }.chart-legend small { color: #7c92ad; display: block; font-size: .67rem; line-height: 1.3; margin-top: .8rem; }
    .postal-dashboard__grid { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1.8fr) minmax(280px, .8fr); }
    .postal-panel { background: #fff; border: 1px solid #d9e4f2; border-radius: 14px; padding: 1.3rem; }
    .postal-panel__head { align-items: start; display: flex; gap: 1rem; justify-content: space-between; margin-bottom: 1.1rem; }
    .postal-panel h2 { color: #0f4aa1; font-size: 1.2rem; margin: .3rem 0 0; }
    .filter-group { background: #f2f4f6; border-radius: 9px; display: flex; padding: 3px; }
    .filter-group button { background: transparent; border: 0; border-radius: 6px; color: #49678c; cursor: pointer; font-size: .75rem; font-weight: 700; padding: .4rem .55rem; }
    .filter-group button.is-active { background: var(--postal-yellow); color: var(--postal-ink); }
    .service-list { display: grid; gap: .35rem; max-height: 385px; overflow-y: auto; padding-right: .25rem; }
    .service-row { align-items: center; background: #fbfdff; border: 1px solid transparent; border-radius: 9px; color: #153257; cursor: pointer; display: grid; gap: .75rem; grid-template-columns: 150px minmax(60px, 1fr) 32px; padding: .65rem .75rem; text-align: left; width: 100%; }
    .service-row:hover, .service-row.is-selected { background: #edf4ff; border-color: #b8d0ed; }.service-row.is-selected { box-shadow: inset 3px 0 var(--postal-yellow); }
    .service-row__name { font-size: .8rem; font-weight: 700; }.service-row__track { background: #dce7f4; border-radius: 99px; height: 7px; overflow: hidden; }.service-row__track i { background: #0f4aa1; border-radius: inherit; display: block; height: 100%; }.service-row strong { color: #0f4aa1; text-align: right; }
    .postal-panel--detail { background: var(--postal-blue); border: 0; color: #fff; }.postal-panel--detail .panel-kicker { color: var(--postal-yellow); }.postal-panel--detail h2 { color: #fff; font-size: 1.45rem; }.detail-number { color: var(--postal-yellow); font-size: 3.2rem; font-weight: 800; letter-spacing: -.08em; margin-top: 1.2rem; }.postal-panel--detail p { color: #cfe1fb; margin: 0 0 1.3rem; }.postal-panel--detail dl { border-top: 1px solid rgba(255,255,255,.2); margin: 0; }.postal-panel--detail dl div { border-bottom: 1px solid rgba(255,255,255,.15); display: flex; justify-content: space-between; padding: .8rem 0; }.postal-panel--detail dt { color: #bcd2f3; font-size: .82rem; }.postal-panel--detail dd { font-weight: 700; margin: 0; }.detail-empty { line-height: 1.6; margin-top: 1.5rem !important; }.empty-state { color: #587292; padding: 2rem; text-align: center; }
    .postal-dashboard__footer { align-items: center; color: #587292; display: flex; flex-wrap: wrap; gap: 1.4rem; justify-content: space-between; margin-top: 1rem; padding: .9rem .25rem; }.postal-dashboard__footer b { color: #0f4aa1; }.postal-dashboard__footer a { color: #0f4aa1; font-weight: 700; }
    @media (max-width: 900px) { .postal-dashboard__metrics { grid-template-columns: repeat(2, 1fr); }.postal-dashboard__grid, .postal-dashboard__charts { grid-template-columns: 1fr; }.postal-dashboard__header { align-items: start; flex-direction: column; }.postal-dashboard__stamp { text-align: left; } }
    @media (max-width: 560px) { .postal-dashboard__metrics { grid-template-columns: 1fr; }.postal-panel__head { flex-direction: column; }.service-row { grid-template-columns: 115px minmax(45px, 1fr) 28px; }.bar-chart { gap: .3rem; }.user-chart__content { align-items: start; flex-direction: column; } }
</style>
</section>

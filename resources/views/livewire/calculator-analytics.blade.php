<section class="cr" wire:loading.class="cr-refresh">
    <header class="cr-head">
        <div><span class="cr-kicker">{{ $mode === 'performance' ? 'CALIDAD DEL CÁLCULO' : 'USO REAL DE LA CALCULADORA' }}</span>
            <h2>{{ $mode === 'performance' ? 'Respuesta y disponibilidad de tarifas' : 'Consultas en un vistazo' }}</h2>
            <p class="cr-muted">{{ $mode === 'performance' ? 'Detecta demoras y solicitudes que no encuentran un precio.' : 'Qué servicios se consultan, quiénes calculan y cómo evoluciona la actividad.' }}</p>
        </div>
        <div class="cr-actions">
            <label for="analytics-period-{{ $mode }}" class="mb-0">Periodo</label>
            <select id="analytics-period-{{ $mode }}" wire:model.live="days"><option value="7">Últimos 7 días</option><option value="30">Últimos 30 días</option><option value="90">Últimos 90 días</option></select>
            <button type="button" class="cr-button cr-button--light" wire:click="$refresh" wire:loading.attr="disabled">Actualizar</button>
            @can('administrar-sistema')<a class="cr-button" href="{{ route('records.index', $filters) }}">Ver registros</a>@endcan
        </div>
    </header>
    @if ($mode === 'performance')
        <div class="cr-metrics">
            <article class="cr-metric cr-metric--primary"><span>Tiempo medio de cálculo</span><strong>{{ $summary['total'] ? number_format($summary['average_ms']) : '—' }} <small>ms</small></strong><small>Validación y cálculo en servidor</small></article>
            <article class="cr-metric"><span>Percentil 95</span><strong>{{ $p95 !== null ? number_format($p95) : '—' }} <small>ms</small></strong><small>95% de consultas tardan esto o menos</small></article>
            <article class="cr-metric"><span>Consultas con tarifa</span><strong>{{ $summary['success_rate'] !== null ? $summary['success_rate'].'%' : '—' }}</strong><small>{{ $summary['calculated'] }} de {{ $summary['total'] }} consultas</small></article>
            <article class="cr-metric"><span>Cálculos de 1 s o más</span><strong>{{ $summary['slow'] }}</strong><small>Máximo registrado: {{ $summary['max_ms'] }} ms</small></article>
        </div>
    @else
        <div class="cr-metrics">
            <article class="cr-metric cr-metric--primary"><span>Consultas realizadas</span><strong>{{ number_format($summary['total']) }}</strong><small>{{ $summary['services'] }} servicios utilizados</small></article>
            <article class="cr-metric"><span>Cuentas que calcularon</span><strong>{{ $summary['accounts'] }}</strong><small>Cuentas únicas en este periodo</small></article>
            <article class="cr-metric"><span>Sesiones de visitantes</span><strong>{{ $summary['guests'] }}</strong><small>Calculan sin iniciar sesión</small></article>
            <article class="cr-metric"><span>Con tarifa disponible</span><strong>{{ $summary['success_rate'] !== null ? $summary['success_rate'].'%' : '—' }}</strong><small>{{ $summary['no_rate'] }} consultas sin tarifa</small></article>
        </div>
    @endif
    @if (!$summary['total'])
        <div class="cr-empty"><h3>Aún no hay cálculos en este periodo</h3><p>Las gráficas se completarán con las nuevas consultas registradas.</p><p><a href="{{ url('/') }}">Abrir calculadora postal</a></p></div>
    @else
        <div class="cr-grid">
            <article class="cr-panel">
                <div class="cr-panel-head"><div><span class="cr-kicker">{{ $mode === 'performance' ? 'TIEMPO POR SERVICIO' : 'SERVICIOS MÁS CONSULTADOS' }}</span><h3>{{ $mode === 'performance' ? 'Dónde tarda el cálculo' : 'Preferencias de servicio' }}</h3></div><small>Top {{ $services->count() }} · {{ $mode === 'performance' ? 'promedio en ms' : 'n.º de consultas' }}</small></div>
                @php($maxBar = max(1, $services->max($mode === 'performance' ? 'average_ms' : 'total')))
                @foreach ($services as $service)
                    @php($barValue = $mode === 'performance' ? round($service->average_ms) : $service->total)
                    @can('administrar-sistema')<a class="cr-bar-row" href="{{ route('records.index', $filters + ['service' => $service->service_code]) }}" title="Ver {{ $service->total }} consultas de {{ $service->name }}">
                    @else<div class="cr-bar-row">@endcan
                        <span>{{ $service->name }}</span><span class="cr-track"><i style="width:{{ round(100 * $barValue / $maxBar, 1) }}%"></i></span><strong>{{ number_format($barValue) }}{{ $mode === 'performance' ? ' ms' : '' }}</strong>
                    @can('administrar-sistema')</a>@else</div>@endcan
                @endforeach
                <div class="cr-insight">@if ($mode === 'performance')<b>{{ $summary['no_rate'] }} consultas sin tarifa.</b> Revisa destinos y rangos de peso; esto no significa que el servidor haya fallado.@else<b>{{ $services->first()->name }}</b> concentra {{ round(100 * $services->first()->total / $summary['total'], 1) }}% de las consultas del periodo.@endif</div>
            </article>
            <article class="cr-panel">
                <span class="cr-kicker">EVOLUCIÓN DIARIA</span><h3>{{ $mode === 'performance' ? 'Tiempo medio por día' : 'Actividad de consultas' }}</h3>
                @php($dailyKey = $mode === 'performance' ? 'average_ms' : 'total')
                @php($dailyMax = max(1, $timeline->max($dailyKey)))
                <div class="cr-timeline" role="group" aria-label="{{ $mode === 'performance' ? 'Tiempo medio de cálculo por día' : 'Consultas por día' }}">
                    @foreach ($timeline as $day)
                        @php($description = \Carbon\Carbon::parse($day['date'])->format('d/m').': '.$day['total'].' consultas, '.$day['average_ms'].' ms de promedio')
                        @can('administrar-sistema')<a class="cr-day" data-empty="{{ $day['total'] ? 'false' : 'true' }}" href="{{ route('records.index', ['from' => $day['date'], 'to' => $day['date']]) }}" title="{{ $description }}" aria-label="{{ $description }}">
                        @else<span class="cr-day" data-empty="{{ $day['total'] ? 'false' : 'true' }}" title="{{ $description }}" aria-label="{{ $description }}">@endcan
                            <i style="height:{{ $day['total'] ? max(3, round(100 * $day[$dailyKey] / $dailyMax)) : 0 }}%"></i>
                        @can('administrar-sistema')</a>@else</span>@endcan
                    @endforeach
                </div>
                <div class="cr-timeline-labels"><span>{{ \Carbon\Carbon::parse($filters['from'])->format('d/m/Y') }}</span><span>{{ \Carbon\Carbon::parse($filters['to'])->format('d/m/Y') }}</span></div>
                <p class="cr-note">{{ $mode === 'performance' ? 'Altura: tiempo promedio en milisegundos.' : 'Altura: número de consultas.' }} Pasa el cursor para ver los valores.@can('administrar-sistema') Haz clic para abrir el detalle del día.@endcan</p>
                <div class="cr-insight">{{ $summary['calculated'] }} cálculos con tarifa · {{ $summary['no_rate'] }} sin tarifa · Peso promedio: {{ number_format($summary['average_weight'], 2) }} kg</div>
            </article>
        </div>
        @if ($mode === 'dashboard')
            <div class="cr-grid">
                <article class="cr-panel"><span class="cr-kicker">ALCANCE DE LOS ENVÍOS CONSULTADOS</span><h3>Nacional e internacional</h3>
                    <div class="cr-split" role="img" aria-label="{{ $summary['national'] }} consultas nacionales y {{ $summary['international'] }} internacionales"><i style="width:{{ 100 * $summary['national'] / $summary['total'] }}%"></i><i style="width:{{ 100 * $summary['international'] / $summary['total'] }}%"></i></div>
                    <div class="cr-legend"><span>Nacional <b>{{ $summary['national'] }}</b></span><span>Internacional <b>{{ $summary['international'] }}</b></span></div>
                    <p class="cr-note">Nacional incluye documentos y paquetería. Las consultas no representan envíos confirmados.</p>
                </article>
                <article class="cr-panel"><span class="cr-kicker">DESTINOS MÁS CONSULTADOS</span><h3>Hacia dónde quieren enviar</h3>
                    @foreach ($destinations as $destination)<div class="cr-bar-row"><span>{{ $destination->name }}</span><span class="cr-track"><i style="width:{{ round(100 * $destination->total / max(1, $destinations->max('total'))) }}%"></i></span><strong>{{ $destination->total }}</strong></div>@endforeach
                </article>
            </div>
        @else
            <div class="cr-insight"><b>Qué mide este panel:</b> tiempo de validación, búsqueda de tarifa y cálculo antes de guardar el registro. No mide la carga completa de la página ni la red. Los errores de aplicación y las peticiones lentas se revisan abajo.</div>
        @endif
    @endif
    <p class="cr-note">{{ $days }} días, incluido hoy · Horario {{ \App\Support\PostalTime::LABEL }} · Actualizado {{ \App\Support\PostalTime::now()->format('H:i:s') }}. Las cuentas y sesiones se cuentan una sola vez por periodo; una sesión anónima no equivale a una persona única.</p>
</section>

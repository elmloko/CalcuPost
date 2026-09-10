@extends('adminlte::page')
@section('title', 'Registros de consultas')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/calculator-reports.css') }}">
@endsection
@section('content')
<section class="cr">
    <header class="cr-head">
        <div><span class="cr-kicker">POSTAR · HISTORIAL DE LA CALCULADORA</span><h2>Registros de consultas</h2><p class="cr-muted">Cada cálculo, con sus datos y su resultado. Sin perder las consultas anteriores.</p></div>
        <div class="cr-actions">
            <a class="cr-button cr-button--light" href="{{ url('/registros/tecnicos') }}">Errores técnicos</a>
            <a class="cr-button" href="{{ route('records.pdf', $filters) }}"><i class="fas fa-file-pdf" aria-hidden="true"></i> Descargar PDF</a>
        </div>
    </header>
    @if ($errors->any())<div class="cr-alert" role="alert">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <form class="cr-filters" method="get" action="{{ route('records.index') }}">
        <div><label for="from">Desde</label><input id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></div>
        <div><label for="to">Hasta</label><input id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></div>
        <div><label for="service">Servicio postal</label><select id="service" name="service"><option value="">Todos los servicios</option>@foreach ($services as $service)<option value="{{ $service->service_code }}" @selected(($filters['service'] ?? '') === $service->service_code)>{{ $service->name }}</option>@endforeach</select></div>
        <div><label for="modality">Modalidad</label><select id="modality" name="modality"><option value="">Todas las modalidades</option>@foreach (['NAT', 'INT', 'PAQUE', 'DOC'] as $modality)<option value="{{ $modality }}" @selected(($filters['modality'] ?? '') === $modality)>{{ \App\Models\CalculatorQuery::modalityLabel($modality) }}</option>@endforeach</select></div>
        <div><label for="status">Resultado</label><select id="status" name="status"><option value="">Todos los resultados</option><option value="calculated" @selected(($filters['status'] ?? '') === 'calculated')>Calculado</option><option value="no_rate" @selected(($filters['status'] ?? '') === 'no_rate')>Sin tarifa disponible</option></select></div>
        <div><label for="actor">Tipo de usuario</label><select id="actor" name="actor"><option value="">Todos</option><option value="account" @selected(($filters['actor'] ?? '') === 'account')>Cuenta identificada</option><option value="guest" @selected(($filters['actor'] ?? '') === 'guest')>Visitante anónimo</option></select></div>
        <div><label for="destination">Destino</label><select id="destination" name="destination"><option value="">Todos los destinos</option>@foreach ($destinations as $destination)<option value="{{ $destination->destination_code }}" @selected(($filters['destination'] ?? '') === $destination->destination_code)>{{ $destination->name }}</option>@endforeach</select></div>
        <div><label for="per_page">Consultas por página</label><select id="per_page" name="per_page">@foreach ([20, 50, 100] as $size)<option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 50) === $size)>{{ $size }}</option>@endforeach</select></div>
        <div class="cr-filter-actions"><button class="cr-button" type="submit">Aplicar filtros</button><a class="cr-button cr-button--light" href="{{ route('records.index') }}">Ver todo</a></div>
    </form>
    <div class="cr-metrics">
        <article class="cr-metric cr-metric--primary"><span>Consultas encontradas</span><strong>{{ number_format($summary['total']) }}</strong><small>{{ $summary['services'] }} servicios utilizados</small></article>
        <article class="cr-metric"><span>Con tarifa disponible</span><strong>{{ number_format($summary['calculated']) }}</strong><small>{{ $summary['no_rate'] }} sin tarifa</small></article>
        <article class="cr-metric"><span>Cuentas identificadas</span><strong>{{ $summary['accounts'] }}</strong><small>{{ $summary['guests'] }} sesiones anónimas adicionales</small></article>
        <article class="cr-metric"><span>Importe estimado promedio</span><strong>{{ number_format($summary['average_amount'], 2) }} <small>Bs.</small></strong><small>Solo cálculos con tarifa; no son ventas.</small></article>
    </div>
    <article class="cr-panel cr-panel--table">
        <div class="cr-panel-head"><div><span class="cr-kicker">DETALLE COMPLETO</span><h3>Consultas, de la más reciente a la más antigua</h3></div><small class="cr-table-count">{{ $records->total() }} consultas · página {{ $records->currentPage() }} de {{ $records->lastPage() }} · {{ \App\Support\PostalTime::LABEL }}</small></div>
        @if ($records->isEmpty())
            <div class="cr-empty"><h3>No hay consultas para estos filtros</h3><p>Los nuevos cálculos se guardan automáticamente desde esta actualización.</p><p><a href="{{ url('/') }}">Ir a la calculadora</a> o <a href="{{ route('records.index') }}">ver todo el historial</a>.</p></div>
        @else
            <div class="cr-table-wrap"><table class="cr-table">
                <thead><tr><th scope="col">Consulta / fecha</th><th scope="col">Usuario</th><th scope="col">Servicio</th><th scope="col">Destino</th><th scope="col">Peso</th><th scope="col">Resultado</th><th scope="col">Detalle</th></tr></thead>
                <tbody>@foreach ($records as $record)<tr>
                    <td><b class="cr-query-id">#{{ $record->id }}</b><small>{{ $record->local_created_at->format('d/m/Y H:i:s') }}</small></td>
                    <td>{{ $record->actor_name }}<small>{{ $record->actor_type === 'account' ? 'Cuenta identificada' : 'Sesión '.substr($record->actor_key, 0, 8) }}</small></td>
                    <td><b class="cr-service-name">{{ $record->service_name }}</b><small>{{ $record->modality_label }}</small></td>
                    <td>{{ $record->destination_name }}</td>
                    <td><span class="cr-weight">{{ number_format($record->weight_kg, 3) }} kg</span></td>
                    <td><span class="cr-badge {{ $record->status === 'no_rate' ? 'cr-badge--warn' : '' }}">{{ $record->status_label }}</span>@if ($record->total_amount !== null)<small><b>{{ number_format($record->total_amount, 2) }} Bs.</b></small>@endif</td>
                    <td><details><summary>Ver datos</summary><div class="cr-details">
                        <div><b>Tarifario:</b> {{ $record->category }}</div>
                        <div><b>Base:</b> {{ $record->base_amount !== null ? number_format($record->base_amount, 2).' Bs.' : 'Sin tarifa' }}</div>
                        <div><b>Dimensiones (cm):</b> {{ $record->dimensions['largo'] ?? '—' }} × {{ $record->dimensions['ancho'] ?? '—' }} × {{ $record->dimensions['alto'] ?? '—' }}</div>
                        <div><b>Adicionales:</b> {{ collect($record->extras)->filter()->keys()->map(fn ($extra) => ['certificado' => 'Certificado', 'expreso' => 'Expreso', 'recibo' => 'Aviso de recibo'][$extra] ?? $extra)->implode(', ') ?: 'Ninguno' }}</div>
                        <div><b>Cálculo en servidor:</b> {{ $record->duration_ms }} ms</div>
                    </div></details></td>
                </tr>@endforeach</tbody>
            </table></div>
            <div class="cr-pagination"><span class="cr-muted">Mostrando {{ $records->firstItem() }}–{{ $records->lastItem() }} de {{ $records->total() }} consultas · página {{ $records->currentPage() }} de {{ $records->lastPage() }}</span>{{ $records->links('pagination::bootstrap-4') }}</div>
        @endif
    </article>
    <p class="cr-note">El PDF incluye las {{ $summary['total'] }} consultas encontradas con estos filtros, repartidas en tantas páginas como sean necesarias (máximo 1.000 consultas por informe). Los datos se conservan tal como estaban al calcular, aunque luego cambie el servicio. El número precedido por # es el identificador de la consulta, no la cantidad total.</p>
</section>
@endsection

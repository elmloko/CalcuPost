<!doctype html>
<html lang="es"><head><meta charset="UTF-8"><title>Informe de consultas postales</title>
<style>
    @page { margin:32px 32px 46px; }
    body { font-family:'DejaVu Sans',sans-serif; color:#193657; font-size:9px; line-height:1.45; }
    .brand { color:#0f4aa1; font-weight:bold; font-size:10px; letter-spacing:2px; }
    h1 { font-size:25px; color:#082f68; margin:8px 0 3px; } h2 { font-size:14px; color:#082f68; margin:22px 0 9px; }
    .intro { border-bottom:4px solid #edc125; padding-bottom:13px; margin-bottom:13px; }
    .muted { color:#526b88; } .meta { margin:5px 0; font-size:9px; }
    .metrics { width:100%; border-collapse:collapse; margin:16px 0; }
    .metrics td { background:#edf4ff; padding:12px; width:25%; border-right:4px solid #fff; vertical-align:top; text-align:center; }
    .metrics strong { font-size:24px; display:block; color:#082f68; }
    .note { background:#fff8df; border-left:3px solid #d7a70a; padding:9px 12px; margin:12px 0 18px; }
    table.records { width:100%; border-collapse:collapse; table-layout:fixed; }
    .records th { background:#082f68; color:#fff; padding:8px 5px; font-size:8px; text-align:center; }
    .records td { padding:9px 5px; border-bottom:1px solid #d9e4f2; vertical-align:top; text-align:center; word-wrap:break-word; }
    .records tr:nth-child(even) td { background:#f3f7fc; }
    thead { display:table-header-group; } tr { page-break-inside:avoid; }
    .badge { color:#17613e; font-weight:bold; }.warn { color:#865d12; }
    .small { font-size:8px; color:#526b88; }.money { font-size:11px; font-weight:bold; }
    footer { position:fixed; bottom:-28px; left:0; right:0; border-top:1px solid #d9e4f2; padding-top:6px; font-size:8px; color:#526b88; }
</style></head><body>
<footer>POSTAR · Informe interno de consultas · Generado {{ \App\Support\PostalTime::now()->format('d/m/Y H:i') }} · {{ \App\Support\PostalTime::LABEL }}</footer>
<div class="intro"><div class="brand">POSTAR / CALCULADORA POSTAL</div><h1>Informe de consultas</h1><div class="muted">Actividad registrada, servicios solicitados y resultados de cotización.</div></div>
<div class="meta"><b>Periodo:</b> {{ !empty($filters['from']) ? \Carbon\Carbon::parse($filters['from'])->format('d/m/Y') : 'Desde el primer registro' }} al {{ !empty($filters['to']) ? \Carbon\Carbon::parse($filters['to'])->format('d/m/Y') : 'último registro' }} · <b>Horario:</b> {{ \App\Support\PostalTime::LABEL }}</div>
<div class="meta"><b>Filtros:</b>
    Servicio: {{ !empty($filters['service']) ? ($records->first()?->service_name ?? $filters['service']) : 'Todos' }};
    Modalidad: {{ !empty($filters['modality']) ? \App\Models\CalculatorQuery::modalityLabel($filters['modality']) : 'Todas' }};
    Resultado: {{ !empty($filters['status']) ? ($filters['status'] === 'calculated' ? 'Calculado' : 'Sin tarifa') : 'Todos' }};
    Usuarios: {{ !empty($filters['actor']) ? ($filters['actor'] === 'account' ? 'Cuentas identificadas' : 'Visitantes anónimos') : 'Todos' }};
    Destino: {{ !empty($filters['destination']) ? ($records->first()?->destination_name ?? $filters['destination']) : 'Todos' }}.
</div>
<table class="metrics"><tr>
    <td>Consultas registradas<strong>{{ number_format($summary['total']) }}</strong>{{ $summary['services'] }} servicios utilizados</td>
    <td>Con tarifa<strong>{{ number_format($summary['calculated']) }}</strong>{{ $summary['no_rate'] }} sin tarifa disponible</td>
    <td>Cuentas identificadas<strong>{{ $summary['accounts'] }}</strong>{{ $summary['guests'] }} sesiones anónimas adicionales</td>
    <td>Importe promedio<strong>{{ number_format($summary['average_amount'], 2) }} Bs.</strong>Solo cálculos con tarifa</td>
</tr></table>
<div class="note">Los importes son estimaciones, no ventas ni ingresos. Una sesión anónima no equivale necesariamente a una persona única. El informe conserva los datos originales de cada consulta.</div>
<h2>Detalle completo · {{ $records->count() }} consultas incluidas</h2>
<table class="records"><thead><tr><th style="width:11%">ID / Fecha</th><th style="width:14%">Usuario</th><th style="width:15%">Servicio / modalidad</th><th style="width:12%">Destino</th><th style="width:18%">Paquete / adicionales</th><th style="width:17%">Resultado</th><th style="width:7%">Tiempo</th></tr></thead><tbody>
@forelse ($records as $record)<tr>
    <td><b>#{{ $record->id }}</b><br>{{ $record->local_created_at->format('d/m/Y') }}<br><span class="small">{{ $record->local_created_at->format('H:i:s') }}</span></td>
    <td>{{ $record->actor_name }}<br><span class="small">{{ $record->actor_type === 'account' ? 'Cuenta identificada' : 'Sesión '.substr($record->actor_key, 0, 8) }}</span></td>
    <td><b>{{ $record->service_name }}</b><br>{{ $record->modality_label }}<br><span class="small">{{ $record->category }}</span></td>
    <td>{{ $record->destination_name }}</td>
    <td><b>{{ number_format($record->weight_kg, 3) }} kg</b><br><span class="small">L × A × H (cm):<br>{{ $record->dimensions['largo'] ?? '-' }} × {{ $record->dimensions['ancho'] ?? '-' }} × {{ $record->dimensions['alto'] ?? '-' }}</span><br>{{ collect($record->extras)->filter()->keys()->map(fn ($extra) => ['certificado' => 'Certificado', 'expreso' => 'Expreso', 'recibo' => 'Aviso de recibo'][$extra] ?? $extra)->implode(', ') ?: 'Sin adicionales' }}</td>
    <td><span class="badge {{ $record->status === 'no_rate' ? 'warn' : '' }}">{{ $record->status_label }}</span>@if ($record->total_amount !== null)<br><span class="money">{{ number_format($record->total_amount, 2) }} Bs.</span><br><span class="small">Base: {{ number_format($record->base_amount, 2) }} Bs.</span>@else<br><span class="small">No hay precio para este destino y peso.</span>@endif</td>
    <td>{{ $record->duration_ms }} ms</td>
</tr>@empty<tr><td colspan="7">No hay registros que coincidan con los filtros seleccionados.</td></tr>@endforelse
</tbody></table>
<p class="small">Fin del informe. Se incluyeron {{ $records->count() }} de {{ $summary['total'] }} consultas encontradas. Tiempo: validación y cálculo en el servidor, sin incluir la descarga ni la carga de la página. Dimensiones no informadas: “-”. El número precedido por # es el identificador de la consulta, no la cantidad total.</p>
</body></html>

@use('Laravel\Pulse\Facades\Pulse')
@extends('adminlte::page')

@section('title', 'Rendimiento')

@section('css')
    {!! Pulse::css() !!}
    @livewireStyles
    <link rel="stylesheet" href="{{ asset('css/calculator-reports.css') }}">
@endsection

@section('js')
    {!! Pulse::js() !!}
    @livewireScriptConfig
    <script>document.documentElement.classList.remove('dark');</script>
@endsection

@section('content')
    @livewire('calculator-analytics', ['mode' => 'performance'])
    <div class="pulse-dashboard">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
            <div>
                <h2 class="h4 m-0">Diagnóstico técnico</h2>
                <div class="text-muted small mt-2">Caché, errores y solicitudes lentas. Este bloque usa el periodo de Pulse.</div>
                @can('administrar-sistema')<a href="{{ url('/registros/tecnicos') }}" class="btn btn-outline-primary btn-sm mt-2">Abrir errores técnicos</a>@endcan
            </div>
            <div class="d-flex align-items-center" style="gap: 1.5rem">
                <livewire:pulse.period-selector />
            </div>
        </div>

        <div class="grid default:grid-cols-12 default:gap-6">
            <livewire:pulse.cache cols="6" />
            <livewire:pulse.slow-requests cols="6" />
            <livewire:pulse.exceptions cols="6" />
            <livewire:pulse.slow-queries cols="6" />
        </div>
    </div>
@endsection

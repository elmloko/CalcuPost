@extends('adminlte::page')

@section('title', 'Errores técnicos')

@section('adminlte_css_pre')
    @if ($assetsPublished)
        <link href="{{ asset(mix('app.css', config('log-viewer.assets_path'))) }}" rel="stylesheet">
    @else
        {!! \Opcodes\LogViewer\Facades\LogViewer::css() !!}
    @endif
@endsection

@section('css')
    <link href="{{ asset('css/log-viewer-brand.css') }}" rel="stylesheet">
@endsection

@section('content_header')
    <div class="log-viewer-heading">
        <h1>Errores técnicos</h1>
        <p>Eventos de la aplicación. <a href="{{ route('records.index') }}">Volver al historial de consultas</a>.</p>
    </div>
@endsection

@section('content')
    <div class="log-viewer-admin">
        <div id="log-viewer" class="flex h-full max-h-screen max-w-full">
            <router-view></router-view>
        </div>
    </div>
@endsection

@section('js')
    <script>window.LogViewer = @json($logViewerScriptVariables);</script>
    @if ($assetsPublished)
        <script src="{{ asset(mix('app.js', config('log-viewer.assets_path'))) }}"></script>
    @else
        {!! \Opcodes\LogViewer\Facades\LogViewer::js() !!}
    @endif
@endsection

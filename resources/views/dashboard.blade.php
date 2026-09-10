@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content')
    @livewire('calculator-analytics')
    @livewire('dashboard')
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/calculator-reports.css') }}">
@stop

@section('js')
    
@stop

@extends('adminlte::page')

@section('title', 'CalcuPost')

@section('template_title')
    Tarifario Postal
@endsection

@section('content')
    @livewire('tarifario')
    @include('footer')
@stop

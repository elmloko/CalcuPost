@extends('adminlte::page')

@section('title', 'POSTAR')

@section('template_title')
    Tarifario Postal
@endsection

@section('content')
    @livewire('tarifario')
    @include('footer')
@stop

@extends('adminlte::page')
@section('title', 'Mi perfil')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/profile-account.css') }}">
@endsection
@section('content')
<main class="account-page">
    <header class="account-heading">
        <div><span class="panel-kicker">CORREOS DE BOLIVIA · MI CUENTA</span><h1>Mi perfil</h1><p>Gestiona tus datos personales y la seguridad de tu cuenta.</p></div>
        <a class="btn btn-outline-primary" href="{{ route('dashboard') }}"><i class="fas fa-arrow-left mr-2" aria-hidden="true"></i>Volver al panel</a>
    </header>
    <section class="account-identity" aria-label="Cuenta actual">
        <span class="account-avatar"><i class="fas fa-user" aria-hidden="true"></i></span>
        <div><h2>{{ $user->name }}</h2><p>{{ $user->email }}</p></div>
        <span class="account-role">{{ $user->isAdministrator() ? 'Administrador' : 'Usuario' }}</span>
    </section>
    <div class="account-grid">
        <article class="account-card">@include('profile.partials.update-profile-information-form')</article>
        <article class="account-card">@include('profile.partials.update-password-form')</article>
    </div>
    <article class="account-card account-card--danger">@include('profile.partials.delete-user-form')</article>
</main>
@endsection
@section('js')
    @if ($errors->userDeletion->isNotEmpty())
        <script>$(function () { $('#confirm-user-deletion').modal('show'); });</script>
    @endif
@endsection

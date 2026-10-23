<section aria-labelledby="profile-information-title">
    <header class="account-card__heading"><span class="account-section-icon"><i class="fas fa-address-card" aria-hidden="true"></i></span><div><h2 id="profile-information-title">Datos personales</h2><p>Actualiza tu nombre y correo electrónico.</p></div></header>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>
    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')
        <div class="form-group">
            <label for="name">Nombre completo</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autocomplete="name" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            @error('name')<div class="invalid-feedback" id="name-error" role="alert">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')<div class="invalid-feedback" id="email-error" role="alert">{{ $message }}</div>@enderror
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="account-notice"><p>Tu correo electrónico aún no está verificado.</p><button form="send-verification" class="btn btn-link p-0">Reenviar correo de verificación</button></div>
                @if (session('status') === 'verification-link-sent')<p class="account-success" role="status">Se ha enviado un nuevo enlace de verificación a tu correo electrónico.</p>@endif
            @endif
        </div>
        <div class="account-actions"><button type="submit" class="btn btn-primary">Guardar cambios</button>
            @if (session('status') === 'profile-updated')<p class="account-success" role="status"><i class="fas fa-check-circle mr-1" aria-hidden="true"></i>Datos guardados.</p>@endif
        </div>
    </form>
</section>

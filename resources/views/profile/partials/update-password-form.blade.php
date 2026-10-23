<section aria-labelledby="profile-password-title">
    <header class="account-card__heading"><span class="account-section-icon"><i class="fas fa-lock" aria-hidden="true"></i></span><div><h2 id="profile-password-title">Cambiar contraseña</h2><p>Elige una contraseña segura que no utilices en otros servicios.</p></div></header>
    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')
        <div class="form-group">
            <label for="update_password_current_password">Contraseña actual</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" required autocomplete="current-password" @error('current_password', 'updatePassword') aria-invalid="true" aria-describedby="current-password-error" @enderror>
            @error('current_password', 'updatePassword')<div class="invalid-feedback" id="current-password-error" role="alert">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="update_password_password">Nueva contraseña</label>
            <input id="update_password_password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" required autocomplete="new-password" aria-describedby="new-password-help @error('password', 'updatePassword') new-password-error @enderror" @error('password', 'updatePassword') aria-invalid="true" @enderror>
            <small id="new-password-help" class="form-text text-muted">Utiliza al menos 8 caracteres.</small>
            @error('password', 'updatePassword')<div class="invalid-feedback" id="new-password-error" role="alert">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="update_password_password_confirmation">Confirmar nueva contraseña</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" required autocomplete="new-password" @error('password_confirmation', 'updatePassword') aria-invalid="true" aria-describedby="password-confirmation-error" @enderror>
            @error('password_confirmation', 'updatePassword')<div class="invalid-feedback" id="password-confirmation-error" role="alert">{{ $message }}</div>@enderror
        </div>
        <div class="account-actions"><button type="submit" class="btn btn-primary">Actualizar contraseña</button>
            @if (session('status') === 'password-updated')<p class="account-success" role="status"><i class="fas fa-check-circle mr-1" aria-hidden="true"></i>Contraseña actualizada.</p>@endif
        </div>
    </form>
</section>

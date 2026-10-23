<section aria-labelledby="profile-deactivation-title">
    <div class="account-deactivation">
        <div><h2 id="profile-deactivation-title">Desactivar mi cuenta</h2><p>Al desactivarla, se cerrará tu sesión y perderás el acceso al sistema con esta cuenta.</p></div>
        <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#confirm-user-deletion">Desactivar mi cuenta</button>
    </div>
    <div class="modal fade" id="confirm-user-deletion" tabindex="-1" role="dialog" aria-labelledby="deactivation-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content">
            <div class="modal-header"><h2 class="modal-title" id="deactivation-modal-title">¿Desactivar tu cuenta?</h2><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                <div class="modal-body">
                    <p>Se cerrará tu sesión y no podrás volver a acceder con esta cuenta. Introduce tu contraseña para confirmar.</p>
                    <div class="form-group mb-0"><label for="deactivation-password">Contraseña actual</label><input id="deactivation-password" name="password" type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" required autocomplete="current-password" @error('password', 'userDeletion') aria-invalid="true" aria-describedby="deactivation-password-error" @enderror>
                    @error('password', 'userDeletion')<div class="invalid-feedback" id="deactivation-password-error" role="alert">{{ $message }}</div>@enderror</div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger">Desactivar mi cuenta</button></div>
            </form>
        </div></div>
    </div>
</section>

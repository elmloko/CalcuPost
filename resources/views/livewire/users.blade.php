<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="tariff-table-card personnel-card">
                <div class="tariff-table-card__header personnel-card__header">
                    <div>
                        <span class="panel-kicker">DIRECTORIO INTERNO</span>
                        <h2>Personal AGBC</h2>
                        <p>Administra accesos, estado y datos del personal.</p>
                    </div>
                    <div class="personnel-card__actions">
                        <span class="tariff-table-card__count">{{ $users->total() }} personas</span>
                        <a href="{{ route('users.pdf') }}" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-file-pdf mr-1" aria-hidden="true"></i> PDF
                        </a>
                        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus mr-1" aria-hidden="true"></i> Crear persona
                        </a>
                    </div>
                </div>

                @if (session()->has('success'))
                    <div class="alert alert-success">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <div class="personnel-card__body">
                    <div class="row personnel-toolbar">
                        <div class="col-md-6">
                            <input type="text" wire:model="search" class="form-control"
                                placeholder="Buscar por nombre o email...">
                        </div>
                        <div class="col-md-2">
                            <button wire:click="searchUsers" class="btn btn-primary">Buscar</button>
                        </div>
                    </div>

                    <div class="table-responsive tariff-table-wrap">
                        <table class="table tariff-table personnel-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nombre Completo</th>
                                    <th>Email</th>
                                    <th>Regional</th>
                                    <th>Carnet Identidad</th>
                                    <th>Estado</th>
                                    <th>Tipo de cuenta</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr wire:key="personnel-row-{{ $user->id }}">
                                        <td><span class="personnel-number">{{ $users->firstItem() + $loop->index }}</span></td>
                                        <td class="personnel-name"><span class="personnel-avatar"><i class="fas fa-user" aria-hidden="true"></i></span><strong>{{ $user->name }}</strong></td>
                                        <td><a class="personnel-email" href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
                                        <td>{{ $user->city ?: '—' }}</td>
                                        <td>{{ $user->ci ?: '—' }}</td>
                                        <td><span class="personnel-status {{ $user->trashed() ? 'is-inactive' : 'is-active' }}">
                                                {{ $user->trashed() ? 'Inactivo' : 'Activo' }}
                                            </span></td>
                                        <td>
                                            <span class="personnel-role {{ $user->isAdministrator() ? 'is-admin' : '' }}">
                                                {{ $user->isAdministrator() ? 'Administrador' : 'Usuario' }}
                                            </span>
                                        </td>
                                        <td class="personnel-actions">
                                            @if ($user->trashed())
                                                <button wire:click="restore({{ $user->id }})"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fa fa-arrow-up"></i> {{ __('Reactivar') }}
                                                </button>
                                                <button wire:click="deletePermanently({{ $user->id }})"
                                                    wire:confirm="Esta acción eliminará definitivamente al usuario y no se puede deshacer. ¿Desea continuar?"
                                                    class="btn btn-sm btn-danger">
                                                    <i class="fa fa-trash"></i> {{ __('Eliminar') }}
                                                </button>
                                            @else
                                                <button wire:click="deactivate({{ $user->id }})"
                                                    wire:confirm="¿Desea desactivar este usuario? Podrá reactivarlo después."
                                                    class="btn btn-sm btn-warning">
                                                    <i class="fa fa-user-slash"></i> {{ __('Desactivar') }}
                                                </button>
                                                <a class="btn btn-sm btn-success"
                                                    href="{{ route('users.edit', $user->id) }}">
                                                    <i class="fa fa-fw fa-edit"></i> {{ __('Editar') }}
                                                </a>
                                                <button wire:click="setPasswordUser({{ $user->id }})"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fa fa-key"></i> {{ __('Cambiar Contraseña') }}
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row personnel-pagination">
                        <div class="col-md-6">
                            {{ $users->links() }}
                        </div>
                        <div class="col-md-6 text-right">
                            Mostrando {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} de {{ $users->total() }} personas
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cambiar la contraseña -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Cambiar Contraseña</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="password" wire:model="newPassword" class="form-control" placeholder="Nueva Contraseña">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" wire:click="updatePassword" class="btn btn-primary">Actualizar
                        Contraseña</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para controlar el modal -->
<script>
    window.addEventListener('openModal', event => {
        $('#changePasswordModal').modal('show');
    });

    window.addEventListener('closeModal', event => {
        $('#changePasswordModal').modal('hide');
    });
</script>

<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Users extends Component
{
    use WithPagination;

    public $search = ''; // Campo para búsqueda
    public $searchQuery = ''; // Campo que se actualizará al hacer clic en el botón
    public $newPassword = ''; // Nueva contraseña
    public $userIdBeingUpdated = null; // ID del usuario que será actualizado

    protected $paginationTheme = 'bootstrap'; // Usar paginación de Bootstrap

    // Método que se ejecuta cuando se presiona el botón de búsqueda
    public function searchUsers()
    {
        $this->searchQuery = $this->search; // Actualiza la query de búsqueda
        $this->resetPage(); // Resetea la paginación cuando se realiza una búsqueda
    }

    // Establecer el ID del usuario para cambiar la contraseña y abrir el modal
    public function setPasswordUser($userId)
    {
        $this->authorizeAdministrator();
        $this->userIdBeingUpdated = $userId;
        $this->dispatch('openModal'); // Disparar evento para abrir el modal
    }

    // Método para actualizar la contraseña del usuario
    public function updatePassword()
    {
        $this->authorizeAdministrator();

        $this->validate([
            'newPassword' => ['required', Password::defaults()],
            'userIdBeingUpdated' => ['required', 'integer'],
        ]);

        $user = User::find($this->userIdBeingUpdated);

        if ($user) {
            $user->password = Hash::make($this->newPassword); // Cambiar la contraseña
            $user->save();
            session()->flash('success', 'Contraseña actualizada correctamente.');
        } else {
            session()->flash('error', 'Usuario no encontrado.');
        }

        // Limpiar el campo de la nueva contraseña y el ID del usuario
        $this->newPassword = '';
        $this->userIdBeingUpdated = null;

        // Cerrar el modal después de la actualización
        $this->dispatch('closeModal');
    }

    public function deactivate(int $userId): void
    {
        $this->authorizeAdministrator();
        $user = User::findOrFail($userId);

        if (! $this->canModify($user)) {
            return;
        }

        if (! $user->deactivateUnlessLastAdministrator()) {
            session()->flash('error', 'No puede desactivar al último administrador activo.');
            return;
        }

        session()->flash('success', 'Usuario desactivado correctamente. Puede reactivarlo cuando lo necesite.');
    }

    public function restore(int $userId): void
    {
        $this->authorizeAdministrator();
        $user = User::withTrashed()->findOrFail($userId);

        if (! $user->trashed()) {
            session()->flash('error', 'El usuario ya se encuentra activo.');
            return;
        }

        $user->restore();
        session()->flash('success', 'Usuario reactivado correctamente.');
    }

    public function deletePermanently(int $userId): void
    {
        $this->authorizeAdministrator();
        $user = User::withTrashed()->findOrFail($userId);

        if (! $user->trashed()) {
            session()->flash('error', 'Primero debe desactivar al usuario.');
            return;
        }

        DB::transaction(function () use ($user) {
            $user->forceDelete();
        });

        session()->flash('success', 'Usuario eliminado definitivamente.');
    }

    private function authorizeAdministrator(): void
    {
        abort_unless(auth()->check() && auth()->user()->isAdministrator(), 403);
    }

    private function canModify(User $user): bool
    {
        if ($user->is(auth()->user())) {
            session()->flash('error', 'No puede desactivar ni eliminar su propia cuenta.');
            return false;
        }

        return true;
    }

    public function render()
    {
        $this->authorizeAdministrator();

        // Obtener los usuarios con paginación y filtrados por el campo de búsqueda
        $users = User::withTrashed()
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->searchQuery . '%')
                      ->orWhere('email', 'like', '%' . $this->searchQuery . '%');
            })
            ->paginate(10);

        return view('livewire.users', ['users' => $users]);
    }
}

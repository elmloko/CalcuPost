<?php

namespace App\Http\Controllers;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function index()
    {
        return view('user.index');
    }

    public function create()
    {
        $user = new User();
        return view('user.create', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'city'     => ['required', 'string', 'max:100'],
            'ci'       => ['required', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($request) {
            User::create([
                'name' => $request->string('name')->trim(),
                'email' => $request->string('email')->trim()->lower(),
                'password' => $request->input('password'),
                'city' => $request->input('city'),
                'ci' => $request->input('ci'),
                'email_verified_at' => now(),
            ]);
        });

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado correctamente');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('user.show', compact('user'));
    }

    public function pdf()
    {
        if (User::withTrashed()->count() > 1000) {
            return redirect()->route('users.index')->with('error', 'El PDF admite hasta 1.000 usuarios. Use una exportación por lotes para volúmenes mayores.');
        }
        $users = User::withTrashed()->orderBy('name')->get();

        return Pdf::loadView('user.pdf', compact('users'))
            ->setPaper('a4', 'landscape')
            ->download('usuarios.pdf');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('user.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'city'     => ['required', 'string', 'max:100'],
            'ci'       => ['required', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($request, $user) {
            $user->name = $request->string('name')->trim();
            $user->email = $request->string('email')->trim()->lower();
            $user->city = $request->input('city');
            $user->ci = $request->input('ci');

            if ($request->filled('password')) {
                $user->password = $request->input('password');
            }

            $user->save();
        });

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->is(auth()->user())) {
            return redirect()->route('users.index')->with('error', 'No puede desactivar su propia cuenta.');
        }
        if (! $user->deactivateUnlessLastAdministrator()) {
            return redirect()->route('users.index')->with('error', 'No puede desactivar al último administrador activo.');
        }
        return redirect()->route('users.index')
            ->with('success', 'Usuario dado de baja correctamente');
    }

    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('users.index')
            ->with('success', 'Usuario reactivado correctamente');
    }
}

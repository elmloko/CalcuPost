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
        $users = User::withTrashed()->paginate(); // Incluye usuarios eliminados

        return view('user.index', compact('users'))
            ->with('i', (request()->input('page', 1) - 1) * $users->perPage());
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
        $user->delete();

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

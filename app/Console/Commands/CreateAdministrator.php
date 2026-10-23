<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateAdministrator extends Command
{
    protected $signature = 'calcupost:create-admin';

    protected $description = 'Crear el primer administrador mediante entrada interactiva segura';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Este comando requiere una terminal interactiva.');

            return self::FAILURE;
        }

        if (User::where('is_admin', true)->exists()) {
            $this->error('Ya existe un administrador activo.');

            return self::FAILURE;
        }

        $data = [
            'name' => $this->ask('Nombre completo'),
            'email' => strtolower(trim((string) $this->ask('Correo electrónico'))),
            'password' => $this->secret('Contraseña (mínimo 12 caracteres)', false),
            'password_confirmation' => $this->secret('Confirmar contraseña', false),
        ];

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => trim($data['name']),
            'email' => $data['email'],
            'password' => $data['password'],
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        $this->info('Administrador creado correctamente.');

        return self::SUCCESS;
    }
}

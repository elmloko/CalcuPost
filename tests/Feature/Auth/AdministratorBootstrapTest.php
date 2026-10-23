<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdministratorBootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_does_not_create_default_administrator_or_promote_existing_email(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('users', 0);

        $user = User::factory()->create(['email' => 'admin@admin.test', 'is_admin' => false]);
        $this->seed(DatabaseSeeder::class);

        $this->assertFalse($user->refresh()->isAdministrator());
        $this->assertDatabaseCount('users', 1);
    }

    public function test_command_creates_administrator_with_hashed_password(): void
    {
        $this->administratorCommand()->assertSuccessful();

        $user = User::where('email', 'operator@example.test')->sole();
        $this->assertTrue($user->isAdministrator());
        $this->assertTrue(Hash::check('Operator-password-42', $user->password));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_command_does_not_promote_existing_account(): void
    {
        $user = User::factory()->create(['email' => 'operator@example.test', 'is_admin' => false]);

        $this->administratorCommand()->assertFailed();

        $this->assertFalse($user->refresh()->isAdministrator());
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_command_refuses_bootstrap_when_administrator_already_exists(): void
    {
        User::factory()->create(['is_admin' => true]);

        $this->artisan('calcupost:create-admin')->assertFailed();

        $this->assertDatabaseCount('users', 1);
    }

    public function test_command_rejects_non_interactive_execution(): void
    {
        $this->artisan('calcupost:create-admin', ['--no-interaction' => true])->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }

    private function administratorCommand(): \Illuminate\Testing\PendingCommand
    {
        return $this->artisan('calcupost:create-admin')
            ->expectsQuestion('Nombre completo', 'Operador')
            ->expectsQuestion('Correo electrónico', 'operator@example.test')
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'Operator-password-42')
            ->expectsQuestion('Confirmar contraseña', 'Operator-password-42');
    }
}

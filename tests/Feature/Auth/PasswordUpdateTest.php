<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect('/profile');
    }

    public function test_short_password_error_is_in_spanish(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from('/profile')->put('/password', [
            'current_password' => 'password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertRedirect('/profile')->assertSessionHasErrorsIn('updatePassword', [
            'password' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);
    }

    public function test_session_with_old_password_is_rejected_after_password_changes(): void
    {
        $user = User::factory()->create();
        $oldPasswordHash = $user->password;
        $user->update(['password' => 'new-password']);

        $this->actingAs($user)->withSession(['password_hash_web' => $oldPasswordHash])
            ->get('/profile')->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_changing_password_keeps_current_session_authenticated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors();

        $this->get('/profile')->assertOk();
        $this->assertAuthenticatedAs($user);
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/confirm-password');

        $response->assertStatus(200);
    }

    public function test_password_can_be_confirmed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_password_confirmation_rejects_non_string_input(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/confirm-password', ['password' => ['password']])
            ->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_password_confirmation_attempts_are_rate_limited(): void
    {
        $this->actingAs(User::factory()->create());

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->postJson('/confirm-password', ['password' => 'incorrect'])
                ->assertUnprocessable();
        }

        $this->postJson('/confirm-password', ['password' => 'incorrect'])
            ->assertTooManyRequests();
    }
}

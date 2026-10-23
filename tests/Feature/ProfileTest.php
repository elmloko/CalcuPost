<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk()
            ->assertSee('Mi perfil')
            ->assertSee('Datos personales')
            ->assertSee('Contraseña actual')
            ->assertSee('Guardar cambios')
            ->assertSee('Desactivar mi cuenta')
            ->assertDontSee('Profile Information')
            ->assertDontSee('Delete Account');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertSoftDeleted($user);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_administrator_can_view_the_personnel_directory(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);

        $this->actingAs($administrator)->get('/users')
            ->assertOk()
            ->assertSee('Personal AGBC')
            ->assertSee('personnel-table', false);
    }

    public function test_last_administrator_cannot_delete_own_profile(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);

        $this->actingAs($administrator)->from('/profile')->delete('/profile', [
            'password' => 'password',
        ])->assertRedirect('/profile')->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertNotNull($administrator->fresh());
    }

    public function test_administrator_cannot_deactivate_self_through_user_route(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);

        $this->actingAs($administrator)->delete('/users/'.$administrator->id)
            ->assertRedirect('/users');

        $this->assertNotNull($administrator->fresh());
    }

    public function test_only_administrators_can_view_pulse(): void
    {
        $regular = User::factory()->create();
        $administrator = User::factory()->create(['is_admin' => true]);

        $this->assertFalse(Gate::forUser($regular)->allows('viewPulse'));
        $this->assertTrue(Gate::forUser($administrator)->allows('viewPulse'));
    }

    public function test_profile_cannot_promote_user_or_mark_email_verified(): void
    {
        $user = User::factory()->unverified()->create(['is_admin' => false]);

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => true,
            'email_verified_at' => now()->toDateTimeString(),
        ])->assertSessionHasNoErrors();

        $this->assertFalse($user->refresh()->isAdministrator());
        $this->assertNull($user->email_verified_at);
    }

    public function test_regular_users_cannot_access_personnel_routes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        foreach (['/users', '/users/create', '/users/pdf', '/users/'.$user->id, '/users/'.$user->id.'/edit'] as $url) {
            $this->get($url)->assertForbidden();
        }

        $this->post('/users', [])->assertForbidden();
        $this->put('/users/'.$user->id, [])->assertForbidden();
        $this->delete('/users/'.$user->id)->assertForbidden();
        $this->post('/users/'.$user->id.'/restore')->assertForbidden();
    }

    public function test_personnel_password_target_rejects_array_instead_of_failing(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);

        \Livewire\Livewire::actingAs($administrator)->test(\App\Livewire\Users::class)
            ->set('userIdBeingUpdated', [$administrator->id])
            ->set('newPassword', 'new-password')
            ->call('updatePassword')->assertHasErrors('userIdBeingUpdated');

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password', $administrator->refresh()->password));
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Livewire\Users;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdministratorDeactivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_removing_one_administrator_preserves_the_last_even_with_previously_loaded_models(): void
    {
        $first = User::factory()->create(['is_admin' => true]);
        $second = User::factory()->create(['is_admin' => true]);

        $this->assertTrue($first->deactivateUnlessLastAdministrator());
        $this->assertFalse($second->deactivateUnlessLastAdministrator());

        $this->assertSoftDeleted($first);
        $this->assertNotNull($second->fresh());
        $this->assertSame(1, User::where('is_admin', true)->count());
    }

    public function test_inactive_administrators_do_not_allow_deactivating_the_last_active_one(): void
    {
        User::factory()->create(['is_admin' => true, 'deleted_at' => now()]);
        $administrator = User::factory()->create(['is_admin' => true]);

        $this->assertFalse($administrator->deactivateUnlessLastAdministrator());
        $this->assertNotNull($administrator->fresh());
    }

    public function test_administrator_can_delete_own_profile_when_another_administrator_remains(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);
        $other = User::factory()->create(['is_admin' => true]);

        $this->actingAs($administrator)->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/')->assertSessionHasNoErrors();

        $this->assertGuest();
        $this->assertSoftDeleted($administrator);
        $this->assertNotNull($other->fresh());
    }

    public function test_personnel_route_can_deactivate_another_administrator(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);
        $other = User::factory()->create(['is_admin' => true]);

        $this->actingAs($administrator)->delete('/users/'.$other->id)
            ->assertRedirect('/users')->assertSessionHas('success');

        $this->assertSoftDeleted($other);
        $this->assertNotNull($administrator->fresh());
    }

    public function test_livewire_personnel_cannot_deactivate_self_but_can_deactivate_another_administrator(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);
        $other = User::factory()->create(['is_admin' => true]);

        $component = Livewire::actingAs($administrator)->test(Users::class);
        $component->call('deactivate', $administrator->id)->assertHasNoErrors();
        $this->assertNotNull($administrator->fresh());

        $component->call('deactivate', $other->id)->assertHasNoErrors();
        $this->assertSoftDeleted($other);
        $this->assertNotNull($administrator->fresh());
    }
}

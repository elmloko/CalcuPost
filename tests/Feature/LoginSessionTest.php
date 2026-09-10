<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginSessionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDatabaseName() !== 'calcupost_test') {
            throw new \RuntimeException('Login tests require calcupost_test.');
        }
        DB::beginTransaction();
        config(['pulse.enabled' => false]);
        // Laravel normally bypasses CSRF in PHPUnit; enable it for these regressions.
        $this->app->instance(VerifyCsrfToken::class, new class($this->app, $this->app['encrypter']) extends VerifyCsrfToken {
            protected function runningUnitTests() { return false; }
        });
    }

    protected function tearDown(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    public function test_local_test_cookie_is_browser_compatible_and_login_is_not_cached(): void
    {
        $this->assertFalse(config('session.secure'));
        $this->assertSame('lax', config('session.same_site'));
        $this->assertSame('calcupost_test_session', config('session.cookie'));
        $response = $this->get('/login')->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_expired_form_is_rejected_and_returns_a_fresh_form_without_password(): void
    {
        $this->withSession(['_token' => Str::random(40)])
            ->post('/login', ['_token' => 'expired', 'email' => 'admin@example.test', 'password' => 'never-flash-this'])
            ->assertStatus(303)->assertRedirect('/login')->assertSessionHasErrors('session')
            ->assertSessionHas('_old_input.email', 'admin@example.test')->assertSessionMissing('_old_input.password');
        $this->assertGuest();
        $this->get('/login')->assertOk()->assertSee('El formulario de acceso caducó.')
            ->assertSee('admin@example.test')->assertDontSee('never-flash-this');
    }

    public function test_missing_csrf_json_and_other_forms_still_return_419(): void
    {
        $this->postJson('/login', ['email' => 'admin@example.test', 'password' => 'invalid'])->assertStatus(419);
        $this->post('/logout')->assertStatus(419);
        $this->assertGuest();
    }

    public function test_valid_csrf_allows_login_and_logout(): void
    {
        $user = User::create(['name' => 'Login QA', 'email' => Str::uuid().'@example.test',
            'password' => 'test-password', 'is_admin' => true]);
        $token = Str::random(40);
        $this->withSession(['_token' => $token])->post('/login',
            ['_token' => $token, 'email' => $user->email, 'password' => 'test-password'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout', ['_token' => session()->token()])->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_invalid_password_error_is_visible_and_password_is_not_preserved(): void
    {
        $user = User::create(['name' => 'Login QA', 'email' => Str::uuid().'@example.test', 'password' => 'test-password']);
        $token = Str::random(40);
        $this->withSession(['_token' => $token])->from('/login')->post('/login',
            ['_token' => $token, 'email' => $user->email, 'password' => 'wrong-password'])
            ->assertRedirect('/login')->assertSessionHasErrors('email')->assertSessionMissing('_old_input.password');
        $this->assertGuest();
        $this->get('/login')->assertOk()->assertSee('role="alert"', false)->assertDontSee('wrong-password');
    }
}

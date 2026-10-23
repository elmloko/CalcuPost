<?php

namespace Tests\Feature;

use App\Http\Middleware\TrustHosts;
use Illuminate\Http\Request;
use Tests\TestCase;

class TrustedHostTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://calcupost.example.test']);
        $this->app->instance(TrustHosts::class, new class($this->app) extends TrustHosts {
            protected function shouldSpecifyTrustedHosts(): bool
            {
                return true;
            }
        });
    }

    protected function tearDown(): void
    {
        Request::setTrustedHosts([]);
        parent::tearDown();
    }

    public function test_configured_host_can_render_login(): void
    {
        $this->get('https://calcupost.example.test/login')->assertOk();
    }

    public function test_untrusted_host_is_rejected_before_generating_authentication_links(): void
    {
        $this->get('https://attacker.example/login')->assertStatus(400);
    }
}

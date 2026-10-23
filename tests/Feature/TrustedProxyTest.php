<?php

namespace Tests\Feature;

use App\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    protected function tearDown(): void
    {
        Request::setTrustedProxies([], 0);
        parent::tearDown();
    }

    public function test_forwarded_https_and_client_ip_are_accepted_from_configured_cidr(): void
    {
        config(['trustedproxy.proxies' => ['10.20.0.0/24']]);

        $request = $this->requestFrom('10.20.0.15');

        $this->assertTrue($request->isSecure());
        $this->assertSame('203.0.113.7', $request->ip());
        $this->assertSame('https://localhost/login', $request->getSchemeAndHttpHost().'/login');
    }

    public function test_untrusted_client_cannot_spoof_forwarded_https_or_ip(): void
    {
        config(['trustedproxy.proxies' => ['10.20.0.15']]);

        $request = $this->requestFrom('198.51.100.20');

        $this->assertFalse($request->isSecure());
        $this->assertSame('198.51.100.20', $request->ip());
    }

    public function test_no_proxy_is_trusted_by_default(): void
    {
        config(['trustedproxy.proxies' => []]);

        $this->assertFalse($this->requestFrom('10.20.0.15')->isSecure());
    }

    private function requestFrom(string $remoteAddress): Request
    {
        $request = Request::create('http://localhost/login', 'GET', [], [], [], [
            'REMOTE_ADDR' => $remoteAddress,
            'HTTP_X_FORWARDED_FOR' => '203.0.113.7',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ]);

        (new TrustProxies)->handle($request, fn () => response('OK'));

        return $request;
    }
}

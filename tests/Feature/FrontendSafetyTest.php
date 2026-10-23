<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class FrontendSafetyTest extends TestCase
{
    public function test_personnel_validation_messages_are_escaped(): void
    {
        $message = '<img src=x onerror=alert(1)>';
        $errors = (new ViewErrorBag)->put('default', new MessageBag(['name' => $message]));

        $this->view('user.form', ['user' => new User, 'errors' => $errors])
            ->assertSee($message)
            ->assertDontSee($message, false);
    }

    public function test_guest_forms_use_the_built_stylesheet(): void
    {
        $this->get('/forgot-password')->assertOk()
            ->assertSee('/build/assets/app-', false)
            ->assertDontSee('resources/js/app.js', false);
    }

    public function test_calculator_loads_one_livewire_runtime(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertSame(1, preg_match_all('/<script[^>]+src="[^"]*livewire(?:\.min)?\.js\?/', $html));
        $this->assertStringNotContainsString('resources/js/app.js', $html);
    }
}

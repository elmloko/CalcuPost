<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Pulse\Facades\Pulse;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Pulse::user(fn (User $user) => [
            'name' => $user->name,
            'extra' => $user->email,
        ]);

        Gate::define('viewPulse', function (?User $user) {
            return $user !== null;
        });

        Gate::define('administrar-sistema', fn (User $user) => $user->isAdministrator());
    }
}

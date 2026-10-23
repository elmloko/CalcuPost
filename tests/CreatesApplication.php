<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        if (app()->environment('testing') && $database !== 'calcupost_test') {
            throw new \RuntimeException('Tests must use the calcupost_test database.');
        }

        return $app;
    }
}

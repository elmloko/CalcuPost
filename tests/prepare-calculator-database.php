<?php

// Run with the project's PHP binary. This helper only prepares the isolated test database.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'mysql', 'database.connections.mysql.database' => 'calcupost_test']);
Illuminate\Support\Facades\DB::purge('mysql');
if (Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== 'calcupost_test') {
    throw new RuntimeException('Refusing to prepare a non-test database.');
}
Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
echo Illuminate\Support\Facades\Artisan::output();
echo "Prepared calcupost_test; application data untouched.\n";

<?php

// Read-only clock audit; never rewrites or removes historical timestamps.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['pulse.enabled' => false]);
$base = config('database.connections.mysql');
$base['url'] = null;
foreach (['calcupost', 'calcupost_test'] as $database) {
    config(['database.connections.clock_audit' => array_replace($base, ['database' => $database])]);
    Illuminate\Support\Facades\DB::purge('clock_audit');
    $db = Illuminate\Support\Facades\DB::connection('clock_audit');
    $records = $db->table('calculator_queries')->orderByDesc('id')->limit(3)->get(['id', 'created_at'])->map(function ($row) {
        $utc = Carbon\Carbon::parse($row->created_at, 'UTC');
        return ['id' => $row->id, 'stored_utc' => $utc->toDateTimeString(),
            'bolivia' => $utc->copy()->setTimezone('America/La_Paz')->toDateTimeString()];
    });
    echo json_encode(['database' => $database, 'now_utc' => now('UTC')->toDateTimeString(),
        'now_bolivia' => now('America/La_Paz')->toDateTimeString(),
        'future_records' => $db->table('calculator_queries')->where('created_at', '>', now('UTC')->toDateTimeString())->count(),
        'total' => $db->table('calculator_queries')->count(), 'latest' => $records],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
}

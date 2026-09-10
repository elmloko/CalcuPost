<?php

// Read-only diagnostics for the HTTP collection. Never prints credentials or user records.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::class;
$tariffs = [];
foreach (['EMS NAT', 'EMS INT', 'LC/AO NAT', 'SUPER DOC NAT'] as $category) {
    $tariffs[$category] = $db::table('tarifario')->where('categoria', $category)
        ->where('peso_min_kg', '<=', 1)->where('peso_max_kg', '>=', 1)
        ->first(['categoria', 'peso_min_kg', 'peso_max_kg', 'nacional', 'dest_a', 'local_1']);
}
echo json_encode([
    'tariffs' => $tariffs,
    'counts' => [
        'users' => $db::table('users')->count(),
        'tariffs' => $db::table('tarifario')->count(),
        'services' => $db::table('postal_services')->count(),
        'calculator_queries' => $db::table('calculator_queries')->count(),
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

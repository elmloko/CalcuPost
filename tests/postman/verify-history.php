<?php

// Read-only verification of the records returned by the official Postman CLI run.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'mysql', 'database.connections.mysql.database' => 'calcupost_test', 'pulse.enabled' => false]);
Illuminate\Support\Facades\DB::purge('mysql');
if (Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== 'calcupost_test') {
    throw new RuntimeException('History verification requires calcupost_test.');
}
$report = json_decode(file_get_contents(__DIR__.'/postman-results.json'), true, 512, JSON_THROW_ON_ERROR);
$ids = [];
$actors = [];
$statuses = [];
foreach ($report['run']['executions'] as $execution) {
    $bytes = $execution['response']['stream']['data'] ?? [];
    $body = json_decode(implode('', array_map('chr', $bytes)), true);
    if (!isset($body['registro_id'])) {
        continue;
    }
    $record = App\Models\CalculatorQuery::findOrFail($body['registro_id']);
    $expectedActor = hash_hmac('sha256', 'guest:api:'.$body['sesion_consulta'], config('app.key'));
    if ($record->actor_key !== $expectedActor || $record->category !== $body['categoria']
        || $record->destination_code !== $body['destino'] || $record->total_amount != $body['tarifa']) {
        throw new RuntimeException('Postman response does not match its saved history record.');
    }
    $ids[] = $record->id;
    $actors[] = $record->actor_key;
    $statuses[] = $record->status;
}
if (count(array_unique($ids)) !== 19 || count(array_unique($actors)) !== 1
    || count(array_filter($statuses, fn ($status) => $status === 'no_rate')) !== 1
    || App\Models\CalculatorQuery::where('actor_key', $actors[0])->count() !== 19) {
    throw new RuntimeException('Expected exactly 19 independent records (18 priced, 1 no-rate), grouped into one guest session.');
}
echo "PASS: 19 Postman records verified in calcupost_test; 18 calculations, 1 no-rate, 1 guest session.\n";

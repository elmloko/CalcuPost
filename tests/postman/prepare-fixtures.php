<?php

// Only the isolated test database is configured by this helper.
require __DIR__.'/../prepare-calculator-database.php';
config(['pulse.enabled' => false]);

// Mirror the current service catalogue and tariff table into the isolated database.
// The source connection is read-only; only calcupost_test is replaced.
$connectionConfig = config('database.connections.mysql');
$connectionConfig['url'] = null;
$sourceConfig = array_replace($connectionConfig, ['database' => 'calcupost']);
$targetConfig = array_replace($connectionConfig, ['database' => 'calcupost_test']);
config([
    'database.connections.postman_source' => $sourceConfig,
    'database.connections.postman_target' => $targetConfig,
]);
$source = Illuminate\Support\Facades\DB::connection('postman_source');
$target = Illuminate\Support\Facades\DB::connection('postman_target');
if ($source->getDatabaseName() !== 'calcupost' || $target->getDatabaseName() !== 'calcupost_test') {
    throw new RuntimeException('Unexpected database: refusing tariff sync.');
}

$tariffs = $source->table('tarifario')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
$services = $source->table('postal_services')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
if ($tariffs === [] || $services === []) {
    throw new RuntimeException('The main catalogue is empty; refusing to replace test fixtures.');
}

$oldCategories = $target->table('tarifario')->distinct()->pluck('categoria')->all();
$target->transaction(function () use ($target, $tariffs, $services) {
    $target->table('tarifario')->delete();
    foreach (array_chunk($tariffs, 100) as $chunk) {
        $target->table('tarifario')->insert($chunk);
    }
    $target->table('postal_services')->delete();
    foreach (array_chunk($services, 100) as $chunk) {
        $target->table('postal_services')->insert($chunk);
    }
});

foreach (array_unique(array_merge($oldCategories, array_column($tariffs, 'categoria'))) as $category) {
    $cacheIdentity = [
        $target->getConfig('driver'), $target->getConfig('host'), $target->getConfig('port'),
        $target->getDatabaseName(), $category,
    ];
    Illuminate\Support\Facades\Cache::forget('tarifario:v2:'.hash('sha256', json_encode($cacheIdentity, JSON_THROW_ON_ERROR)));
}

foreach ([
    ['EMS NAT', 'nacional', 17], ['EMS INT', 'dest_a', 228],
    ['MI ENCOMIENDA', 'cui_cap', 10], ['ENCOMIENDA', 'dest_a', 203],
    ['LC/AO NAT', 'nacional', 10], ['LC/AO INT', 'dest_a', 145],
    ['ECA NAT', 'nacional', 8], ['ECA INT', 'dest_a', 131],
    ['PLIEGOS NAT', 'nacional', 12], ['PLIEGOS INT', 'dest_a', 145],
    ['SACAS M NAT', 'nacional', 23], ['SACAS M INT', 'dest_a', 249],
    ['SUPER NAT', 'nacional', 52], ['SUPER PAQUE NAT', 'dest_a', 437],
    ['SUPER DOC NAT', 'dest_a', 324],
] as [$category, $column, $amount]) {
    if (! App\Models\PostalService::where('category', $category)->exists()) {
        throw new RuntimeException("Missing postal service fixture: {$category}");
    }
    $tariff = App\Models\Precios::where('categoria', $category)
        ->where('peso_min_kg', '<=', 1)->where('peso_max_kg', '>=', 1)
        ->orderByDesc('peso_min_kg')->first();
    if (! $tariff) {
        throw new RuntimeException("Missing 1 kg tariff fixture: {$category}");
    }
    $tariff->update([$column => $amount]);
    Illuminate\Support\Facades\Cache::forget(App\Models\Precios::tariffCacheKey($category));
}
echo count($tariffs)." tariffs and ".count($services)." service variants copied to calcupost_test.\n";
echo "Postman fixtures ready for all 15 service variants in calcupost_test.\n";

<?php

// Read-only verification against the extracted CalcuPost-main tariff.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$source = json_decode(file_get_contents(database_path('data/calcupost_main_tariffs.json')), true, 512, JSON_THROW_ON_ERROR)['tariffs'];
$categories = array_unique(array_column($source, 'categoria'));
$actual = Illuminate\Support\Facades\DB::table('tarifario')->whereIn('categoria', $categories)->get();
$differences = abs($actual->count() - count($source));
foreach ($source as $expected) {
    $matches = $actual->filter(fn ($row) => $row->categoria === $expected['categoria']
        && abs((float) $row->peso_min_kg - (float) $expected['peso_min_kg']) < 0.000001
        && abs((float) $row->peso_max_kg - (float) $expected['peso_max_kg']) < 0.000001);
    if ($matches->count() !== 1) {
        $differences++;
        continue;
    }
    $row = $matches->first();
    foreach ($expected as $column => $value) {
        if ($value === null || $column === 'categoria') {
            $differences += $value !== $row->{$column} ? 1 : 0;
        } else {
            $differences += $row->{$column} === null || abs((float) $value - (float) $row->{$column}) > 0.000001 ? 1 : 0;
        }
    }
}
echo Illuminate\Support\Facades\DB::connection()->getDatabaseName().": {$actual->count()} tarifas, ".count($categories)." categorías, {$differences} diferencias.\n";
exit($differences === 0 ? 0 : 1);

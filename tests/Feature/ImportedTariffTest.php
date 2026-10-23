<?php

namespace Tests\Feature;

use App\Models\PostalService;
use App\Models\Precios;
use App\Support\PostalCalculator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ImportedTariffTest extends TestCase
{
    public function test_import_restores_all_source_values_and_preserves_custom_tariffs(): void
    {
        if (DB::connection()->getDatabaseName() !== 'calcupost_test') {
            throw new \RuntimeException('Import tests require calcupost_test.');
        }
        DB::beginTransaction();
        try {
            $source = json_decode(file_get_contents(database_path('data/calcupost_main_tariffs.json')), true, 512, JSON_THROW_ON_ERROR)['tariffs'];
            $categories = array_unique(array_column($source, 'categoria'));
            $servicesBefore = DB::table('postal_services')->where('category', '!=', 'ECA NAT')->orderBy('id')->get();
            $ems = Precios::where('categoria', 'EMS INT')->where('peso_min_kg', 0.001)->firstOrFail();
            $ems->update(['dest_a' => 999, 'dest_f' => 777]);
            Precios::create(['categoria' => 'EMS INT', 'peso_min_kg' => 100, 'peso_max_kg' => 101, 'dest_a' => 999]);
            $custom = Precios::create(['categoria' => 'QA IMPORT CUSTOM', 'peso_min_kg' => 1, 'peso_max_kg' => 2, 'nacional' => 42]);
            DB::table('postal_services')->where('category', 'ECA NAT')->delete();
            Cache::put(Precios::tariffCacheKey('EMS INT'), collect(['stale']), 3600);

            $migration = require database_path('migrations/2026_10_05_000001_apply_calcupost_main_tariffs.php');
            $migration->up();

            $this->assertSame(283, Precios::whereIn('categoria', $categories)->count());
            foreach ($source as $expected) {
                $actual = DB::table('tarifario')->where('categoria', $expected['categoria'])
                    ->where('peso_min_kg', $expected['peso_min_kg'])->where('peso_max_kg', $expected['peso_max_kg'])->sole();
                foreach ($expected as $column => $value) {
                    if ($value === null || $column === 'categoria') {
                        $this->assertSame($value, $actual->{$column}, $expected['categoria'].' '.$column);
                    } else {
                        $this->assertEqualsWithDelta((float) $value, (float) $actual->{$column}, 0.000001, $expected['categoria'].' '.$column);
                    }
                }
            }
            $this->assertDatabaseHas('tarifario', ['id' => $ems->id, 'dest_a' => 186, 'dest_f' => null]);
            $this->assertDatabaseHas('tarifario', ['id' => $custom->id, 'nacional' => 42]);
            $this->assertEquals($servicesBefore, DB::table('postal_services')->where('category', '!=', 'ECA NAT')->orderBy('id')->get());
            $this->assertDatabaseHas('postal_services', ['code' => 'ECA', 'modality' => 'NAT', 'category' => 'ECA NAT']);
            $this->assertNull(Cache::get(Precios::tariffCacheKey('EMS INT')));
            $result = (new PostalCalculator)->calculate(PostalService::where('category', 'EMS INT')->firstOrFail(), [
                'peso' => 0.25, 'destino' => 'dest_a_argentina',
            ]);
            $this->assertSame('calculated', $result['status']);
            $this->assertSame(186.0, $result['total']);
        } finally {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        }
    }
}

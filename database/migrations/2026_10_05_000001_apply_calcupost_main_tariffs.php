<?php

use App\Models\Precios;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        $data = json_decode(file_get_contents(database_path('data/calcupost_main_tariffs.json')), true, 512, JSON_THROW_ON_ERROR);
        $tariffs = collect($data['tariffs'])->groupBy('categoria');
        if (count($data['tariffs']) !== 283 || $tariffs->count() !== 15) {
            throw new RuntimeException('The CalcuPost-main tariff extract is incomplete.');
        }

        DB::transaction(function () use ($data, $tariffs): void {
            $existing = DB::table('tarifario')->whereIn('categoria', $tariffs->keys())->lockForUpdate()->get();
            $services = DB::table('postal_services')->lockForUpdate()->get();
            $connection = DB::connection();
            $databaseKey = substr(hash('sha256', json_encode([
                $connection->getConfig('host'), $connection->getConfig('port'), $connection->getDatabaseName(),
            ])), 0, 12);
            $backupPath = storage_path('app/tarifario_before_calcupost_main_'.$databaseKey.'_'.now()->format('Y_m_d_His_u').'.json');
            $backup = json_encode([
                'database' => $connection->getDatabaseName(), 'source_sha256' => $data['source_sha256'],
                'tarifario' => $existing, 'postal_services' => $services,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            File::ensureDirectoryExists(dirname($backupPath));
            if (file_put_contents($backupPath, $backup, LOCK_EX) !== strlen($backup)) {
                throw new RuntimeException('Cannot save the previous tariff backup.');
            }

            // ECA national was removed locally; the supplied tariff includes it again.
            if (! $services->contains('category', 'ECA NAT')) {
                DB::table('postal_services')->insert([
                    'code' => 'ECA', 'name' => 'ECA', 'modality' => 'NAT',
                    'category' => 'ECA NAT', 'profile' => 'ECA NAT',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            foreach ($tariffs as $category => $rows) {
                if (! DB::table('postal_services')->where('category', $category)->exists()) {
                    throw new RuntimeException("Missing postal service for {$category}");
                }
                $byBand = $existing->where('categoria', $category)->groupBy(
                    fn ($row) => sprintf('%.3f:%.3f', $row->peso_min_kg, $row->peso_max_kg)
                );
                $kept = [];
                foreach ($rows as $row) {
                    $key = sprintf('%.3f:%.3f', $row['peso_min_kg'], $row['peso_max_kg']);
                    $matches = $byBand->get($key, collect());
                    if ($matches->count() > 1) {
                        throw new RuntimeException("Duplicate existing tariff band for {$category}: {$key}");
                    }
                    if ($matches->isNotEmpty()) {
                        $id = $matches->first()->id;
                        DB::table('tarifario')->where('id', $id)->update($row);
                        $kept[] = $id;
                    } else {
                        $kept[] = DB::table('tarifario')->insertGetId($row);
                    }
                }
                DB::table('tarifario')->where('categoria', $category)->whereNotIn('id', $kept)->delete();
            }
        });

        foreach ($tariffs->keys() as $category) {
            Cache::forget(Precios::tariffCacheKey($category));
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Restore the verified storage/app/tarifario_before_calcupost_main_*.json backup to revert these tariffs.');
    }
};

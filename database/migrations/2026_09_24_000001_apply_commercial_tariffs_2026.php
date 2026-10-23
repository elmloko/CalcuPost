<?php

use App\Models\Precios;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $path = database_path('data/commercial_tariffs_2026.json');
        $tariffs = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR)['tariffs'];

        DB::transaction(function () use ($tariffs): void {
            foreach ($tariffs as $category => $rows) {
                if ($category === 'ECA INT' && ! DB::table('postal_services')->where('category', $category)->exists()) {
                    DB::table('postal_services')->insert([
                        'code' => 'ECA', 'name' => 'ECA', 'modality' => 'INT',
                        'category' => $category, 'profile' => $category,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                if (! DB::table('postal_services')->where('category', $category)->exists()) {
                    throw new RuntimeException("Missing postal service for {$category}");
                }

                $existing = DB::table('tarifario')->where('categoria', $category)->lockForUpdate()->get();
                $byBand = $existing->groupBy(fn ($row) => sprintf('%.3f:%.3f', $row->peso_min_kg, $row->peso_max_kg));
                $kept = [];

                foreach ($rows as $row) {
                    $key = $row['peso_min_kg'].':'.$row['peso_max_kg'];
                    $matches = $byBand->get($key, collect());
                    if ($matches->count() > 1) {
                        throw new RuntimeException("Duplicate tariff band for {$category}: {$key}");
                    }
                    if ($matches->count() === 1) {
                        $id = $matches->first()->id;
                        DB::table('tarifario')->where('id', $id)->update($row);
                        $kept[] = $id;
                    } else {
                        $kept[] = DB::table('tarifario')->insertGetId(['categoria' => $category] + $row);
                    }
                }

                // The revised workbook replaces weight bands that no longer exist.
                DB::table('tarifario')->where('categoria', $category)->whereNotIn('id', $kept)->delete();
            }
        });

        foreach (array_keys($tariffs) as $category) {
            Cache::forget(Precios::tariffCacheKey($category));
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Restoring previous commercial tariffs requires a verified database backup.');
    }
};

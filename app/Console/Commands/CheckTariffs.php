<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckTariffs extends Command
{
    protected $signature = 'tariffs:check {--category= : Categoría a revisar}';

    protected $description = 'Revisa tramos de peso sin modificar tarifas';

    public function handle(): int
    {
        $rows = DB::table('tarifario')
            ->select('id', 'categoria', 'peso_min_kg', 'peso_max_kg')
            ->when($this->option('category'), fn ($query, $category) => $query->where('categoria', $category))
            ->orderBy('categoria')->orderBy('peso_min_kg')->orderBy('id')->get();

        $issues = 0;
        foreach ($rows->groupBy('categoria') as $category => $tariffs) {
            $previous = null;
            foreach ($tariffs as $tariff) {
                $minimum = (float) $tariff->peso_min_kg;
                $maximum = (float) $tariff->peso_max_kg;
                if ($minimum > $maximum) {
                    $this->warn("{$category}: tramo {$tariff->id} tiene mínimo mayor que máximo");
                    $issues++;
                }
                if ($previous !== null) {
                    $difference = $minimum - $previous['maximum'];
                    if ($difference <= 0.000001) {
                        $this->warn("{$category}: tramo {$previous['id']} se superpone con {$tariff->id}");
                        $issues++;
                    } elseif ($difference > 0.001001) {
                        $this->warn("{$category}: hueco entre tramos {$previous['id']} y {$tariff->id}");
                        $issues++;
                    }
                }
                if ($previous === null || $maximum > $previous['maximum']) {
                    $previous = ['id' => $tariff->id, 'maximum' => $maximum];
                }
            }
        }

        $this->info("{$issues} problema(s) en {$rows->groupBy('categoria')->count()} categoría(s).");

        return $issues === 0 ? self::SUCCESS : self::FAILURE;
    }
}

<?php

namespace App\Livewire;

use App\Support\CalculatorReport;
use App\Support\PostalTime;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CalculatorAnalytics extends Component
{
    public int $days = 30;

    #[Locked]
    public string $mode = 'dashboard';

    public function render()
    {
        abort_unless(auth()->check(), 403);
        $days = in_array($this->days, [7, 30, 90], true) ? $this->days : 30;
        $now = PostalTime::now();
        $filters = ['from' => $now->copy()->subDays($days - 1)->toDateString(), 'to' => $now->toDateString()];
        $data = CalculatorReport::analytics($filters);
        $data['services'] = $data['services']->sortByDesc($this->mode === 'performance' ? 'average_ms' : 'total')->take(8);
        $timeline = collect(range($days - 1, 0))->map(function ($ago) use ($data, $now) {
            $date = $now->copy()->subDays($ago)->toDateString();
            return ['date' => $date, 'total' => (int) ($data['daily'][$date]->total ?? 0),
                'average_ms' => round($data['daily'][$date]->average_ms ?? 0)];
        });

        return view('livewire.calculator-analytics', $data + compact('filters', 'timeline', 'days'));
    }
}

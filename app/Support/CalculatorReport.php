<?php

namespace App\Support;

use App\Models\CalculatorQuery;
use Illuminate\Database\Eloquent\Builder;

class CalculatorReport
{
    public static function query(array $filters): Builder
    {
        return CalculatorQuery::query()
            ->when($filters['from'] ?? null, fn ($q, $value) => $q->where('created_at', '>=', PostalTime::dayStartUtc($value)))
            ->when($filters['to'] ?? null, fn ($q, $value) => $q->where('created_at', '<', PostalTime::dayStartUtc($value, 1)))
            ->when($filters['service'] ?? null, fn ($q, $value) => $q->where('service_code', $value))
            ->when($filters['modality'] ?? null, fn ($q, $value) => $q->where('modality', $value))
            ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))
            ->when($filters['actor'] ?? null, fn ($q, $value) => $q->where('actor_type', $value))
            ->when($filters['destination'] ?? null, fn ($q, $value) => $q->where('destination_code', $value));
    }

    public static function summary(Builder $query): array
    {
        $row = (clone $query)->selectRaw("COUNT(*) AS total,
            SUM(status = 'calculated') AS calculated, SUM(status = 'no_rate') AS no_rate,
            COUNT(DISTINCT CASE WHEN actor_type = 'account' THEN actor_key END) AS accounts,
            COUNT(DISTINCT CASE WHEN actor_type = 'guest' THEN actor_key END) AS guests,
            AVG(duration_ms) AS average_ms, MAX(duration_ms) AS max_ms,
            AVG(total_amount) AS average_amount, AVG(weight_kg) AS average_weight,
            COUNT(DISTINCT service_code) AS services,
            SUM(modality IN ('INT', 'PAQUE', 'DOC')) AS international,
            SUM(duration_ms >= 1000) AS slow")->first();

        $total = (int) $row->total;
        return [
            'total' => $total, 'calculated' => (int) $row->calculated, 'no_rate' => (int) $row->no_rate,
            'accounts' => (int) $row->accounts, 'guests' => (int) $row->guests,
            'average_ms' => round((float) $row->average_ms), 'max_ms' => (int) $row->max_ms,
            'average_amount' => (float) $row->average_amount, 'average_weight' => (float) $row->average_weight,
            'services' => (int) $row->services, 'international' => (int) $row->international,
            'national' => $total - (int) $row->international, 'slow' => (int) $row->slow,
            'success_rate' => $total ? round(100 * $row->calculated / $total, 1) : null,
        ];
    }

    public static function analytics(array $filters): array
    {
        $query = self::query($filters);
        $summary = self::summary($query);
        $p95 = $summary['total'] ? (clone $query)->orderBy('duration_ms')
            ->offset(max(0, (int) ceil($summary['total'] * .95) - 1))->value('duration_ms') : null;

        return [
            'summary' => $summary, 'p95' => $p95,
            'services' => (clone $query)->selectRaw("service_code, MAX(service_name) AS name, COUNT(*) AS total,
                AVG(duration_ms) AS average_ms, SUM(status = 'no_rate') AS no_rate")
                ->groupBy('service_code')->orderByDesc('total')->orderBy('service_code')->get(),
            // Numeric offsets work even when MySQL timezone tables are not installed.
            'daily' => (clone $query)->selectRaw("DATE(CONVERT_TZ(created_at, '+00:00', ?)) AS day, COUNT(*) AS total, AVG(duration_ms) AS average_ms", [PostalTime::OFFSET])
                ->groupBy('day')->orderBy('day')->get()->keyBy('day'),
            'destinations' => (clone $query)->selectRaw('destination_code, MAX(destination_name) AS name, COUNT(*) AS total')
                ->groupBy('destination_code')->orderByDesc('total')->limit(5)->get(),
        ];
    }
}

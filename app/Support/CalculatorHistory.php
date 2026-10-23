<?php

namespace App\Support;

use App\Models\CalculatorQuery;
use App\Models\PostalService;
use App\Models\User;

class CalculatorHistory
{
    public function save(PostalService $service, array $calculation, int $started, ?User $user, string $visitor): CalculatorQuery
    {
        $data = $calculation['data'];
        $dimensions = [];
        foreach (['largo', 'ancho', 'alto'] as $dimension) {
            $dimensions[$dimension] = isset($data[$dimension]) && $data[$dimension] !== '' ? (float) $data[$dimension] : null;
        }
        $actor = $user ? 'account:'.$user->id : 'guest:'.$visitor;
        return CalculatorQuery::create([
            'user_id' => $user?->id, 'actor_type' => $user ? 'account' : 'guest',
            'actor_key' => hash_hmac('sha256', $actor, config('app.key')),
            'actor_name' => $user?->name ?? 'Visitante anónimo',
            'service_code' => $service->code, 'service_name' => $service->name,
            'category' => $service->category, 'modality' => $service->modality,
            'destination_code' => $data['destino'],
            'destination_name' => config('postal-destinations')[$data['destino']],
            'weight_kg' => $data['peso'], 'dimensions' => $dimensions,
            'extras' => ['certificado' => (bool) ($data['certificado'] ?? false),
                'expreso' => (bool) ($data['espreso'] ?? false), 'recibo' => (bool) ($data['recibo'] ?? false),
                'reason' => $calculation['reason'] ?? null],
            'base_amount' => $calculation['base'], 'total_amount' => $calculation['total'],
            'status' => $calculation['status'],
            'duration_ms' => (int) round((hrtime(true) - $started) / 1e6), 'created_at' => now(),
        ]);
    }
}

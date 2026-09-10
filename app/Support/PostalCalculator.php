<?php

namespace App\Support;

use App\Models\PostalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PostalCalculator
{
    public function calculate(PostalService $service, array $input): array
    {
        $data = Validator::make($input, [
            'destino' => ['required', 'string', Rule::in(array_keys(PostalDestinations::forService($service)))],
            'peso' => 'required|numeric|gt:0|max:999999',
            'largo' => 'nullable|numeric|min:0|max:999999',
            'ancho' => 'nullable|numeric|min:0|max:999999',
            'alto' => 'nullable|numeric|min:0|max:999999',
            'certificado' => 'boolean', 'espreso' => 'boolean', 'recibo' => 'boolean',
        ], [
            'required' => 'Completa el campo :attribute.',
            'destino.in' => 'El destino no es válido para este servicio y modalidad.',
            'numeric' => ':attribute debe ser un número.',
            'gt' => 'El peso debe ser mayor que cero.',
            'min' => ':attribute no puede ser negativo.',
        ])->validate();

        // Only a destination in the server-owned catalog can determine a column.
        $parts = explode('_', $data['destino']);
        $column = $parts[0] === 'nacional' ? 'nacional' : implode('_', array_slice($parts, 0, 2));
        $base = DB::table('tarifario')->where('categoria', $service->category)
            ->where('peso_min_kg', '<=', $data['peso'])->where('peso_max_kg', '>=', $data['peso'])
            ->value($column);
        if ($base === null) {
            return ['data' => $data, 'base' => null, 'total' => null, 'status' => 'no_rate'];
        }

        $total = (float) $base;
        // Preserve the current pricing rule. A threshold change needs business confirmation.
        if (($data['largo'] ?? 0) + ($data['ancho'] ?? 0) + ($data['alto'] ?? 0) > 1.5) {
            $total *= 1.3;
        }
        $certifiedProfiles = [
            'LC/AO NAT', 'ECA NAT', 'PLIEGOS NAT', 'SACAS M NAT',
            'LC/AO INT', 'ECA INT', 'PLIEGOS INT', 'SACAS M INT',
        ];
        if (($data['certificado'] ?? false) && in_array($service->profile ?: $service->category, $certifiedProfiles, true)) {
            $total += $service->modality === 'INT' ? 8 : 2;
        }
        if ($data['espreso'] ?? false) {
            $total += 10;
        }
        return ['data' => $data, 'base' => (float) $base, 'total' => round($total, 2), 'status' => 'calculated'];
    }
}

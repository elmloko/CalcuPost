<?php

namespace App\Support;

use App\Models\PostalService;

class PostalDestinations
{
    public static function forService(?PostalService $service): array
    {
        if (!$service) {
            return [];
        }

        // Super Paquetería and Documentos are priced with the same A-G country
        // zones as international services, despite the legacy "NAT" suffix.
        $prefixes = in_array($service->modality, ['INT', 'PAQUE', 'DOC'], true) ? ['dest_'] : match ($service->profile) {
            'EMS NAT' => ['local_', 'nacional_'],
            'MI ENCOMIENDA' => ['cui_cap_', 'cui1', 'pro_dentro', 'pro_otro'],
            'LC/AO NAT', 'ECA NAT' => ['local_1', 'nacional_', 'pro_dentro', 'pro_otro', 'cui1', 'cui2'],
            'PLIEGOS NAT' => ['local_1', 'nacional_', 'pro_dentro', 'pro_otro'],
            'SACAS M NAT' => ['nacional_', 'pro_dentro'],
            'SUPER NAT' => ['nacional_'],
            default => ['local_1', 'nacional_', 'pro_dentro', 'pro_otro'],
        };

        return array_filter(config('postal-destinations'), function ($code) use ($prefixes) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with($code, $prefix)) {
                    return true;
                }
            }
            return false;
        }, ARRAY_FILTER_USE_KEY);
    }
}

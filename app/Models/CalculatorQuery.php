<?php

namespace App\Models;

use App\Support\PostalTime;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CalculatorQuery extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime',
        'dimensions' => 'array',
        'extras' => 'array',
        'weight_kg' => 'decimal:3',
        'base_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function getLocalCreatedAtAttribute(): ?Carbon
    {
        return $this->created_at?->copy()->setTimezone(PostalTime::ZONE);
    }

    public function getModalityLabelAttribute(): string
    {
        return self::modalityLabel($this->modality);
    }

    public static function modalityLabel(string $modality): string
    {
        return match ($modality) {
            'NAT' => 'Nacional', 'INT' => 'Internacional',
            'PAQUE' => 'Paquetería internacional', 'DOC' => 'Documentos internacionales',
            default => $modality,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'calculated' ? 'Calculado' : 'Sin tarifa';
    }
}

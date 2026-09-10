<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Precios extends Model
{
    use HasFactory;

    protected $table = 'tarifario';

    public $timestamps = false;

    public static function tariffCacheKey(string $category): string
    {
        $connection = (new static)->getConnection();
        // File cache is shared by both local servers, so the key must identify the database.
        return 'tarifario:v2:'.hash('sha256', json_encode([
            $connection->getConfig('driver'), $connection->getConfig('host'),
            $connection->getConfig('port'), $connection->getDatabaseName(), $category,
        ], JSON_THROW_ON_ERROR));
    }

    protected $fillable = [
        'categoria',
        'peso_min_kg',
        'peso_max_kg',
        'dest_a',
        'dest_b',
        'dest_c',
        'dest_d',
        'dest_e',
        'dest_f',
        'dest_g',
        'local_1',
        'local_2',
        'local_3',
        'local_4',
        'nacional',
        'cui_int',
        'cui_cap',
        'cui1',
        'cui2',
        'pro_dentro',
        'pro_otro',
    ];
}

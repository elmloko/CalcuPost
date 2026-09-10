<?php

namespace App\Support;

use Carbon\Carbon;

final class PostalTime
{
    // Storage remains UTC. Bolivia uses UTC-04 year-round (no daylight saving).
    public const ZONE = 'America/La_Paz';
    public const OFFSET = '-04:00';
    public const LABEL = 'Bolivia (UTC-04:00)';

    public static function now(): Carbon
    {
        return Carbon::now(self::ZONE);
    }

    public static function dayStartUtc(string $date, int $daysAfter = 0): Carbon
    {
        return Carbon::parse($date, self::ZONE)->addDays($daysAfter)->startOfDay()->utc();
    }
}

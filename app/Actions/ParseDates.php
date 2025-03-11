<?php

namespace App\Actions;

use Carbon\Carbon;
use Exception;

class ParseDates
{
    public static function handle(?string $date): ?Carbon
    {
        try {
            $date = match ($date) {
                is_numeric($date) => Carbon::createFromFormat('Y', $date),
                default => Carbon::parse($date),
            };
        } catch (Exception $exception) {
            $date = null;
        }

        return $date;
    }
}

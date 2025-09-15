<?php

namespace App\Actions;

use App\Models\User;

class GetMentions
{
    public static function handle(string $html)
    {
        return str($html)->matchAll('/(?<=@)\w+/')
            ->map(fn ($match) => User::firstWhere('handle', $match));
    }
}
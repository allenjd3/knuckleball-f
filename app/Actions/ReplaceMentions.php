<?php

namespace App\Actions;

use Illuminate\Support\Collection;

class ReplaceMentions
{
    public static function handle(string $html, Collection $mentions)
    {
        $searches = [];
        $replacements = [];
        foreach ($mentions as $mention) {
            $searches[] = "@{$mention->handle}";
            $replacements[] = '<a href="' . $mention->path() . '">@' . $mention->handle . '</a>';
        }

        return str_replace(
            $searches,
            $replacements,
            $html
        );
    }
}

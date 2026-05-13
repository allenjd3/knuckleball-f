<?php

namespace App\Enums;

enum SetEntryStatus: string
{
    case NeedIt      = 'need_it';
    case HaveItSigned = 'have_it_signed';

    public function label(): string
    {
        return match ($this) {
            self::NeedIt       => 'Need It',
            self::HaveItSigned => 'Have It Signed',
        };
    }
}

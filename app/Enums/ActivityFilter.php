<?php

namespace App\Enums;

enum ActivityFilter: string
{
    case All = 'all';
    case Pending = 'pending';
    case Returns = 'returns';
    case Failures = 'failures';

    public function label(): string
    {
        return match ($this) {
            self::All => 'All',
            self::Pending => 'Pending',
            self::Returns => 'Returns',
            self::Failures => 'Failures',
        };
    }

    public function emptyMessage(): string
    {
        return match ($this) {
            self::All => 'No activity yet.',
            self::Pending => 'No pending sends.',
            self::Returns => 'No returns yet.',
            self::Failures => 'No failures.',
        };
    }
}

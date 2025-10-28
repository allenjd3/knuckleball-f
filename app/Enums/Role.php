<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasLabel
{
    case USER = 'user';
    case ADMIN = 'admin';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::USER => 'User',
            self::ADMIN => 'Admin',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }
}

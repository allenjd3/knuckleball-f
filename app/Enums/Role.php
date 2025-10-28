<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasLabel
{
    case USER = 'user';
    case EDITOR = 'editor';
    case ADMIN = 'admin';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::USER => 'User',
            self::EDITOR => 'Editor',
            self::ADMIN => 'Admin',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isEditor(): bool
    {
        return $this === self::EDITOR;
    }
}

<?php

namespace App\Enums;

enum SendMethod: string
{
    case Mail = 'mail';
    case Email = 'email';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $method) => [$method->value => $method->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::Mail => 'By mail',
            self::Email => 'By email request',
        };
    }

    public function feedVerb(): string
    {
        return match ($this) {
            self::Mail => 'sent mail to',
            self::Email => 'sent an email request to',
        };
    }
}

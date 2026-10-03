<?php

namespace App\Enums;

enum AddressType: string
{
    case Mail = 'mail';
    case Email = 'email';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::Mail => 'Mailing address',
            self::Email => 'Email requests',
        };
    }
}

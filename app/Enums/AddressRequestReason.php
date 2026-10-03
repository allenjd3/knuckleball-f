<?php

namespace App\Enums;

enum AddressRequestReason: string
{
    case ReturnToSender = 'return_to_sender';
    case MissingAddress = 'missing_address';
    case Outdated = 'outdated';

    public function label(): string
    {
        return match ($this) {
            self::ReturnToSender => 'Returned to sender',
            self::MissingAddress => 'No address on file',
            self::Outdated => 'Address may be outdated',
        };
    }

    public function headline(): string
    {
        return match ($this) {
            self::MissingAddress => 'is looking for an address for',
            self::ReturnToSender, self::Outdated => 'is looking for a new address for',
        };
    }
}

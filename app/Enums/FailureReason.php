<?php

namespace App\Enums;

enum FailureReason: string
{
    case ReturnToSender = 'return_to_sender';
    case ReturnedUnsigned = 'returned_unsigned';
    case Declined = 'declined';
    case NotAuthentic = 'not_authentic';
    case NeverReturned = 'never_returned';
    case Other = 'other';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $reason) => [$reason->value => $reason->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::ReturnToSender => 'Return to sender (RTS)',
            self::ReturnedUnsigned => 'Returned unsigned',
            self::Declined => 'Declined to sign',
            self::NotAuthentic => 'Secretarial / autopen',
            self::NeverReturned => 'Never came back',
            self::Other => 'Other',
        };
    }
}

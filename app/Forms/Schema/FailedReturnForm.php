<?php

namespace App\Forms\Schema;

use App\Actions\RequestAddress;
use App\Enums\AddressRequestReason;
use App\Enums\FailureReason;
use App\Models\PostalMail;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;

class FailedReturnForm
{
    /**
     * Fields that drive follow-up actions but are not postal_mails columns.
     */
    private const array FORM_ONLY_FIELDS = ['request_address', 'request_address_note'];

    public static function schema(string $toggleLabel = 'Failed to return?'): array
    {
        return [
            Toggle::make('is_failed')
                ->label($toggleLabel)
                ->live(),
            Select::make('failure_reason')
                ->label('What happened?')
                ->options(FailureReason::options())
                ->visible(fn (Get $get): bool => (bool) $get('is_failed'))
                ->required(fn (Get $get): bool => (bool) $get('is_failed'))
                ->live(),
            Toggle::make('request_address')
                ->label('Ask the community for a new address')
                ->helperText('Posts a request to the feed so other collectors can share an updated address.')
                ->visible(fn (Get $get): bool => self::isReturnToSender($get))
                ->live(),
            Textarea::make('request_address_note')
                ->label('Note for the address request')
                ->placeholder('e.g. Came back marked "Moved, left no address"')
                ->maxLength(255)
                ->visible(fn (Get $get): bool => self::isReturnToSender($get) && (bool) $get('request_address')),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function withoutFormOnlyFields(array $data): array
    {
        return collect($data)->except(self::FORM_ONLY_FIELDS)->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function handleAddressRequest(PostalMail $postalMail, array $data): void
    {
        if (! data_get($data, 'request_address') || $postalMail->failure_reason !== FailureReason::ReturnToSender) {
            return;
        }

        RequestAddress::execute(
            user: $postalMail->user,
            signer: $postalMail->signer,
            reason: AddressRequestReason::ReturnToSender,
            postalMail: $postalMail,
            note: data_get($data, 'request_address_note'),
        );
    }

    private static function isReturnToSender(Get $get): bool
    {
        $reason = $get('failure_reason');

        return (bool) $get('is_failed')
            && ($reason instanceof FailureReason ? $reason : FailureReason::tryFrom((string) $reason)) === FailureReason::ReturnToSender;
    }
}

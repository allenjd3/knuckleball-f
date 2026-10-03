<?php

namespace App\Actions;

use App\Enums\AddressRequestReason;
use App\Models\AddressRequest;
use App\Models\PostalMail;
use App\Models\Signer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RequestAddress
{
    /**
     * Open an address request for a signer and post it to the feed. A user only
     * ever has one open request per signer, so repeat requests return the existing one.
     */
    public static function execute(
        User $user,
        Signer $signer,
        AddressRequestReason $reason,
        ?PostalMail $postalMail = null,
        ?string $note = null,
    ): AddressRequest {
        $existing = AddressRequest::query()
            ->open()
            ->where('user_id', $user->id)
            ->where('signer_id', $signer->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $signer, $reason, $postalMail, $note) {
            $addressRequest = AddressRequest::create([
                'user_id' => $user->id,
                'signer_id' => $signer->id,
                'postal_mail_id' => $postalMail?->id,
                'reason' => $reason,
                'note' => $note,
            ]);

            CreateFeedItem::execute($addressRequest, $note);

            return $addressRequest;
        });
    }
}

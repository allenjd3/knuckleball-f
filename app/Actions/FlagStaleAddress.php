<?php

namespace App\Actions;

use App\Enums\FailureReason;
use App\Models\Address;
use App\Models\PostalMail;
use App\Models\Signer;

class FlagStaleAddress
{
    /**
     * Distinct collectors reporting RTS before the address is flagged for review.
     */
    public const int FLAG_THRESHOLD = 2;

    /**
     * Distinct collectors reporting RTS before the address is archived.
     */
    public const int ARCHIVE_THRESHOLD = 3;

    /**
     * Re-evaluate a signer's current mailing address after an RTS report.
     */
    public static function execute(Signer $signer): void
    {
        $address = $signer->addresses()->mailing()->live()->latest()->first();

        if (! $address) {
            return;
        }

        $reports = self::reportCount($address);

        if ($reports >= self::FLAG_THRESHOLD && ! $address->rts_flagged_at) {
            $address->rts_flagged_at = now();
        }

        if ($reports >= self::ARCHIVE_THRESHOLD) {
            $address->expires_at = now();
        }

        if ($address->isDirty()) {
            $address->save();
        }
    }

    /**
     * Distinct collectors whose sends to this signer, made since the address was
     * published, came back return to sender.
     */
    public static function reportCount(Address $address): int
    {
        return PostalMail::query()
            ->where('signer_id', $address->signer_id)
            ->where('is_failed', true)
            ->where('failure_reason', FailureReason::ReturnToSender)
            ->where('date_sent', '>=', ($address->published_at ?? $address->created_at)->copy()->startOfDay())
            ->distinct()
            ->count('user_id');
    }
}

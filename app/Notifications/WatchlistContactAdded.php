<?php

namespace App\Notifications;

use App\Enums\AddressType;
use App\Models\Address;
use App\Models\Player;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WatchlistContactAdded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Player $player,
        public readonly AddressType $contactType,
    ) {}

    public static function forAddress(Address $address): ?self
    {
        $player = $address->signer?->signable;

        return $player instanceof Player ? new self($player, $address->type) : null;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'watchlist_contact_added',
            'player_name' => $this->player->name,
            'player_path' => $this->player->path(),
            'contact_type' => $this->contactType->value,
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\AddressRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AddressRequestFulfilled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly AddressRequest $addressRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $player = $this->addressRequest->signer?->signable;

        return [
            'type' => 'address_request_fulfilled',
            'address_request_id' => $this->addressRequest->id,
            'player_name' => $player?->name,
            'player_path' => $player?->path(),
        ];
    }
}

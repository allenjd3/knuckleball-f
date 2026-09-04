<?php

namespace App\Notifications;

use App\Models\Pack;
use App\Models\Player;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PackPlayerAdded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Pack $pack,
        public readonly Player $player,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'pack_player_added',
            'pack_id' => $this->pack->id,
            'pack_name' => $this->pack->name,
            'pack_slug' => $this->pack->slug,
            'player_id' => $this->player->id,
            'player_name' => $this->player->name,
            'player_slug' => $this->player->slug,
        ];
    }
}

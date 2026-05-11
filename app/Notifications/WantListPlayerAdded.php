<?php

namespace App\Notifications;

use App\Models\Player;
use App\Models\WantList;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WantListPlayerAdded extends Notification
{
    use Queueable;

    public function __construct(
        public WantList $wantList,
        public Player $player,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'want_list_id' => $this->wantList->id,
            'want_list_name' => $this->wantList->name,
            'want_list_path' => $this->wantList->path(),
            'player_id' => $this->player->id,
            'player_name' => $this->player->name,
            'player_path' => $this->player->path(),
            'owner_name' => $this->wantList->user->name,
        ];
    }
}

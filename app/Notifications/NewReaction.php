<?php

namespace App\Notifications;

use App\Models\Feed;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReaction extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User $reactor,
        public readonly Feed $feed,
        public readonly string $emoji,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'         => 'new_reaction',
            'reactor_name' => $this->reactor->name,
            'reactor_slug' => $this->reactor->slug,
            'reactor_photo' => $this->reactor->profile_photo_url,
            'emoji'        => $this->emoji,
            'feed_id'      => $this->feed->id,
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\Feed;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewComment extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User $commenter,
        public readonly Feed $feed,
        public readonly string $body,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'            => 'new_comment',
            'commenter_name'  => $this->commenter->name,
            'commenter_slug'  => $this->commenter->slug,
            'commenter_photo' => $this->commenter->profile_photo_url,
            'body'            => \Str::limit($this->body, 80),
            'feed_id'         => $this->feed->id,
        ];
    }
}

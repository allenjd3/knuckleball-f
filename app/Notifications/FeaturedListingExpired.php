<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeaturedListingExpired extends Notification
{
    use Queueable;

    public function __construct(public readonly Event $event) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your featured listing for \u{201C}{$this->event->name}\u{201D} has expired")
            ->line("Your featured listing for **{$this->event->name}** expired today.")
            ->line('Feature it again to keep it at the top of search results.')
            ->action('Feature This Event', $this->event->path())
            ->line('Thanks for using Knuckleball!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'event_id'   => $this->event->id,
            'event_name' => $this->event->name,
            'event_url'  => $this->event->path(),
            'message'    => "Your featured listing for \u{201C}{$this->event->name}\u{201D} has expired.",
        ];
    }
}

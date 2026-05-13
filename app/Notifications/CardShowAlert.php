<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CardShowAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Event $event) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'event_id'   => $this->event->id,
            'event_name' => $this->event->name,
            'event_path' => $this->event->path(),
            'date'       => $this->event->formattedDate(),
            'city'       => $this->event->city,
            'state'      => $this->event->state,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Card Show near you — {$this->event->name}")
            ->greeting("Card show alert!")
            ->line("A card show near you is coming up: **{$this->event->name}**")
            ->line("{$this->event->formattedDate()} · {$this->event->city}, {$this->event->state}")
            ->action('View Event', $this->event->path());
    }
}

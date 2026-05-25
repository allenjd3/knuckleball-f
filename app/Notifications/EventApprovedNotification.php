<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventApprovedNotification extends Notification implements ShouldQueue
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
            'event_id' => $this->event->id,
            'event_name' => $this->event->name,
            'event_type' => $this->event->type,
            'event_path' => $this->event->path(),
            'player_name' => $this->event->player?->name,
            'date' => $this->event->formattedDate(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->event->type === 'player_signing' ? 'Player Signing' : 'Card Show';

        return (new MailMessage)
            ->subject("Your {$label} listing is live — {$this->event->name}")
            ->greeting('Good news!')
            ->line("Your {$label} listing **{$this->event->name}** has been approved and is now live.")
            ->action('View Listing', $this->event->path())
            ->line('Thank you for contributing to the Knuckleball community.');
    }
}

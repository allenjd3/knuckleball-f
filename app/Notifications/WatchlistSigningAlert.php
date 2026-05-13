<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WatchlistSigningAlert extends Notification implements ShouldQueue
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
            'event_id'    => $this->event->id,
            'event_name'  => $this->event->name,
            'event_path'  => $this->event->path(),
            'player_name' => $this->event->player?->name,
            'event_type'  => $this->event->event_subtype,
            'date'        => $this->event->formattedDate(),
            'city'        => $this->event->city,
            'state'       => $this->event->state,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $player = $this->event->player?->name ?? 'a player on your Watchlist';
        $type   = $this->event->event_subtype === 'mail_in' ? 'Mail-In Signing' : 'In-Person Signing';

        return (new MailMessage)
            ->subject("New {$type}: {$player} — {$this->event->name}")
            ->greeting("New signing alert!")
            ->line("A {$type} opportunity for **{$player}** you're watching has been posted.")
            ->line("**{$this->event->name}** · {$this->event->formattedDate()}")
            ->action('View Event', $this->event->path());
    }
}

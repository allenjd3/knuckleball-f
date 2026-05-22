<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionStarted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $planName,
        public readonly string $interval,
        public readonly float $amount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $intervalLabel = $this->interval === 'year' ? 'yearly' : 'monthly';

        return (new MailMessage)
            ->subject("Your {$this->planName} subscription is active")
            ->line("Your **{$this->planName}** subscription is now active.")
            ->line("You're being billed \${$this->amount} {$intervalLabel}.")
            ->action('Manage Billing', route('billing.index'))
            ->line('Thanks for using Knuckleball!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'plan_name' => $this->planName,
            'interval' => $this->interval,
            'amount' => $this->amount,
            'message' => "Your {$this->planName} subscription is now active.",
        ];
    }
}

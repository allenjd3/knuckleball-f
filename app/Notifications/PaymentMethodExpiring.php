<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentMethodExpiring extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $lastFour,
        public readonly int $expiryMonth,
        public readonly int $expiryYear,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expiry = sprintf('%02d/%d', $this->expiryMonth, $this->expiryYear);

        return (new MailMessage)
            ->subject('Your payment method is expiring soon')
            ->line("Your card ending in **{$this->lastFour}** expires {$expiry}.")
            ->line('Update your payment method now to avoid any interruption to your subscription.')
            ->action('Update Payment Method', route('billing.index'))
            ->line('Thanks for using Knuckleball!');
    }

    public function toDatabase(object $notifiable): array
    {
        $expiry = sprintf('%02d/%d', $this->expiryMonth, $this->expiryYear);

        return [
            'last_four' => $this->lastFour,
            'expiry_month' => $this->expiryMonth,
            'expiry_year' => $this->expiryYear,
            'message' => "Your card ending in {$this->lastFour} expires {$expiry}.",
        ];
    }
}

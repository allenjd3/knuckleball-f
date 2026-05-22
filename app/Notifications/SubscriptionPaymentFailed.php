<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionPaymentFailed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $planName,
        public readonly float $amount,
        public readonly ?string $nextRetryAt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("There was a problem with your {$this->planName} payment")
            ->line("We weren't able to process your \${$this->amount} payment for your **{$this->planName}** subscription.");

        if ($this->nextRetryAt) {
            $message->line("We'll automatically retry on {$this->nextRetryAt}.");
        }

        return $message
            ->action('Update Payment Method', route('billing.index'))
            ->line('Please update your payment method to keep your subscription active.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'plan_name' => $this->planName,
            'amount' => $this->amount,
            'next_retry_at' => $this->nextRetryAt,
            'message' => "Payment failed for your {$this->planName} subscription.",
        ];
    }
}

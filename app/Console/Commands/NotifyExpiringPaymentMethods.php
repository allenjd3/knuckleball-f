<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\PaymentMethodExpiring;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyExpiringPaymentMethods extends Command
{
    protected $signature = 'notify:expiring-payment-methods';

    protected $description = 'Notify subscribers whose payment method expires next month';

    public function handle(): void
    {
        $nextMonth = Carbon::now()->addMonth();

        User::whereHas('subscriptions', fn ($q) => $q->whereIn('stripe_status', ['active', 'trialing']))
            ->whereNotNull('stripe_id')
            ->whereNotNull('pm_last_four')
            ->each(function (User $user) use ($nextMonth): void {
                $paymentMethod = $user->defaultPaymentMethod();

                if (! $paymentMethod || $paymentMethod->type !== 'card') {
                    return;
                }

                $card = $paymentMethod->asStripePaymentMethod()->card;
                $expiresAt = Carbon::createFromDate($card->exp_year, $card->exp_month, 1)->endOfMonth();

                if ($expiresAt->between(Carbon::now(), $nextMonth->endOfMonth())) {
                    $user->notify(new PaymentMethodExpiring($user->pm_last_four, $card->exp_month, $card->exp_year));
                }
            });
    }
}

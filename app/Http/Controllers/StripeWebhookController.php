<?php

namespace App\Http\Controllers;

use App\Models\FeaturedListing;
use App\Models\FeaturedShop;
use App\Models\User;
use App\Notifications\SubscriptionPaymentFailed;
use App\Notifications\SubscriptionStarted;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierWebhookController
{
    public function handlePaymentIntentSucceeded(array $payload): Response
    {
        $intentId = $payload['data']['object']['id'] ?? null;

        if ($intentId) {
            $listing = FeaturedListing::where('stripe_payment_intent_id', $intentId)->first();
            $listing?->event?->update(['is_featured' => true]);
        }

        return $this->successMethod();
    }

    public function handlePaymentIntentPaymentFailed(array $payload): Response
    {
        Log::warning('Stripe PaymentIntent failed', [
            'intent_id' => $payload['data']['object']['id'] ?? null,
        ]);

        return $this->successMethod();
    }

    public function handleCustomerSubscriptionCreated(array $payload): Response
    {
        $subscription = $payload['data']['object'];
        $stripeUserId = $subscription['customer'] ?? null;
        $status = $subscription['status'] ?? null;

        if ($status !== 'active' || ! $stripeUserId) {
            return $this->successMethod();
        }

        $user = User::where('stripe_id', $stripeUserId)->first();

        if (! $user) {
            return $this->successMethod();
        }

        $planName = $this->resolvePlanName($subscription['metadata']['name'] ?? null);
        $plan = $subscription['items']['data'][0]['plan'] ?? [];
        $interval = $plan['interval'] ?? 'month';
        $amount = ($plan['amount'] ?? 0) / 100;

        $user->notify(new SubscriptionStarted($planName, $interval, $amount));

        return $this->successMethod();
    }

    public function handleInvoicePaymentFailed(array $payload): Response
    {
        $invoice = $payload['data']['object'];
        $stripeUserId = $invoice['customer'] ?? null;
        $subscriptionId = $invoice['subscription'] ?? null;

        if (! $stripeUserId) {
            return $this->successMethod();
        }

        $user = User::where('stripe_id', $stripeUserId)->first();

        if (! $user) {
            return $this->successMethod();
        }

        $planName = 'Subscription';

        if ($subscriptionId) {
            $sub = $user->subscriptions()->where('stripe_id', $subscriptionId)->first();

            if ($sub) {
                $planName = $this->resolvePlanName($sub->name);
            }
        }

        $amount = ($invoice['amount_due'] ?? 0) / 100;
        $nextRetry = isset($invoice['next_payment_attempt'])
            ? Carbon::createFromTimestamp($invoice['next_payment_attempt'])->format('M j, Y')
            : null;

        $user->notify(new SubscriptionPaymentFailed($planName, $amount, $nextRetry));

        return $this->successMethod();
    }

    public function handleChargeDisputeCreated(array $payload): Response
    {
        $dispute = $payload['data']['object'];
        $paymentIntentId = $dispute['payment_intent'] ?? null;

        Log::warning('Stripe dispute created', [
            'dispute_id' => $dispute['id'] ?? null,
            'charge_id' => $dispute['charge'] ?? null,
            'payment_intent_id' => $paymentIntentId,
            'amount' => ($dispute['amount'] ?? 0) / 100,
            'reason' => $dispute['reason'] ?? 'unknown',
        ]);

        if ($paymentIntentId) {
            $listing = FeaturedListing::where('stripe_payment_intent_id', $paymentIntentId)->first();
            $listing?->event?->update(['is_featured' => false]);
        }

        return $this->successMethod();
    }

    public function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        // Cashier handles subscription state. We sync our featured flags.
        parent::handleCustomerSubscriptionUpdated($payload);

        $stripeSubId = $payload['data']['object']['id'] ?? null;
        $status = $payload['data']['object']['status'] ?? null;

        // Activate any processing FeaturedShop records when the subscription becomes active.
        // This covers the edge case where 3DS completes but the client-side callback never fired.
        if ($stripeSubId && $status === 'active') {
            $featuredShop = FeaturedShop::where('stripe_subscription_id', $stripeSubId)
                ->where('status', 'processing')
                ->first();

            if ($featuredShop) {
                $featuredShop->update(['status' => 'active']);
                $featuredShop->cardShop?->update(['is_featured' => true]);
            }
        }

        if ($stripeSubId && in_array($status, ['canceled', 'past_due', 'unpaid', 'incomplete_expired'])) {
            // Featured event via subscription
            $listing = FeaturedListing::where('stripe_subscription_id', $stripeSubId)->first();
            $listing?->event?->update(['is_featured' => false]);

            // Featured shop subscription
            $featuredShop = FeaturedShop::where('stripe_subscription_id', $stripeSubId)->first();
            if ($featuredShop) {
                $featuredShop->update(['status' => 'cancelled']);
                $featuredShop->cardShop?->update(['is_featured' => false]);
            }
        }

        return $this->successMethod();
    }

    public function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        // Cashier handles subscription state. We sync our featured flags.
        parent::handleCustomerSubscriptionDeleted($payload);

        $stripeSubId = $payload['data']['object']['id'] ?? null;
        $stripeUserId = $payload['data']['object']['customer'] ?? null;

        if (! $stripeSubId) {
            return $this->successMethod();
        }

        // Un-feature any events tied to this subscription
        $listing = FeaturedListing::where('stripe_subscription_id', $stripeSubId)->first();
        $listing?->event?->update(['is_featured' => false]);

        // Un-feature any shop tied to this subscription
        $featuredShop = FeaturedShop::where('stripe_subscription_id', $stripeSubId)->first();
        if ($featuredShop) {
            $featuredShop->update(['status' => 'cancelled']);
            $featuredShop->cardShop?->update(['is_featured' => false]);
        }

        // If this was the promoter subscription, un-feature all user events
        if ($stripeUserId) {
            $user = User::where('stripe_id', $stripeUserId)->first();
            if ($user && ! $user->subscribed('promoter')) {
                $user->events()->where('status', 'approved')->where('is_featured', true)->each(function ($event) {
                    // Only un-feature if there's no other active listing
                    $hasActiveListing = FeaturedListing::where('event_id', $event->id)
                        ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                        ->exists();
                    if (! $hasActiveListing) {
                        $event->update(['is_featured' => false]);
                    }
                });
            }
        }

        return $this->successMethod();
    }

    private function resolvePlanName(?string $name): string
    {
        return match ($name) {
            'promoter' => 'Promoter Pass',
            'shop_featured' => 'Featured Shop',
            default => 'Subscription',
        };
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\FeaturedListing;
use App\Models\FeaturedShop;
use App\Models\User;
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

    public function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        // Cashier handles subscription state. We sync our featured flags.
        parent::handleCustomerSubscriptionUpdated($payload);

        $stripeSubId = $payload['data']['object']['id'] ?? null;
        $status = $payload['data']['object']['status'] ?? null;

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
}

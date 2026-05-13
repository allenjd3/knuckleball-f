<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\FeaturedListing;
use Laravel\Cashier\Cashier;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowEvent extends Component
{
    public int    $eventId;
    public bool   $checkoutOpen    = false;
    public bool   $checkoutSuccess = false;
    public string $selectedPlan    = 'one_time';
    public string $paymentError    = '';

    public function mount(Event $event): void
    {
        if ($event->status !== 'approved') {
            abort_unless(
                auth()->check() && (auth()->id() === $event->user_id || auth()->user()->isSuperAdmin()),
                404
            );
        }
        $this->eventId = $event->id;
    }

    #[Computed]
    public function event(): Event
    {
        return Event::with(['player.media', 'expectedSigners.media', 'user', 'featuredListing', 'cardShop'])
            ->findOrFail($this->eventId);
    }

    public function canFeature(): bool
    {
        if (! auth()->check()) return false;
        $user = auth()->user();
        return $user->id === $this->event->user_id || $user->isSuperAdmin();
    }

    public function saveToWatchlist(): void
    {
        $player = $this->event->player;
        if (! $player || ! auth()->check()) return;
        auth()->user()->watchlist()->syncWithoutDetaching([$player->id]);
        $this->dispatch('notify', message: 'Added to Watchlist.');
    }

    public function openCheckout(string $plan = 'one_time'): void
    {
        $this->selectedPlan    = $plan;
        $this->paymentError    = '';
        $this->checkoutSuccess = false;
        $this->checkoutOpen    = true;

        // Create the appropriate Stripe intent and send client_secret to JS
        $this->createIntent();
    }

    private function createIntent(): void
    {
        $user = auth()->user();
        $user->createOrGetStripeCustomer();

        if ($this->selectedPlan === 'one_time') {
            $amount = match ($this->event->type) {
                'card_show', 'comic_con', 'memorabilia_show' => config('stripe_products.amounts.featured_card_show'),
                default => config('stripe_products.amounts.featured_signing'),
            };
            $intent = Cashier::stripe()->paymentIntents->create([
                'amount'               => $amount,
                'currency'             => 'usd',
                'customer'             => $user->stripe_id,
                'description'          => "Featured listing: {$this->event->name}",
                'capture_method'       => 'automatic',
                'payment_method_types' => ['card'],
            ]);
            $this->dispatch('stripe-intent-ready', clientSecret: $intent->client_secret, intentType: 'payment');
        } else {
            $intent = $user->createSetupIntent();
            $this->dispatch('stripe-intent-ready', clientSecret: $intent->client_secret, intentType: 'setup');
        }
    }

    public function confirmPayment(string $paymentIntentId): void
    {
        $this->paymentError = '';

        try {
            $pi = Cashier::stripe()->paymentIntents->retrieve($paymentIntentId);

            if ($pi->status !== 'succeeded') {
                $this->paymentError = 'Payment not confirmed. Please try again.';
                return;
            }

            $event  = $this->event;
            $amount = match ($event->type) {
                'card_show', 'comic_con', 'memorabilia_show' => 19.99,
                default => 9.99,
            };

            FeaturedListing::create([
                'event_id'                => $event->id,
                'user_id'                 => auth()->id(),
                'plan_type'               => 'one_time',
                'stripe_payment_intent_id' => $pi->id,
                'amount_paid'             => $amount,
                'starts_at'               => now(),
                'expires_at'              => FeaturedListing::expiresAt('one_time'),
            ]);

            $event->update(['is_featured' => true]);
            $this->checkoutSuccess = true;
            $this->checkoutOpen    = false;
            unset($this->event);
        } catch (\Exception $e) {
            $this->paymentError = $e->getMessage();
        }
    }

    public function createPromoterSubscription(string $paymentMethodId): void
    {
        $this->paymentError = '';

        try {
            $user  = auth()->user();
            $event = $this->event;

            $priceId = config("stripe_products.prices.promoter_{$this->selectedPlan}");

            if (! $priceId) {
                $this->paymentError = 'Promoter Pass is not yet configured. Please contact support.';
                return;
            }

            $user->updateDefaultPaymentMethod($paymentMethodId);
            $subscription = $user->newSubscription('promoter', $priceId)->create($paymentMethodId);

            // Also feature this specific event
            $amount = $this->selectedPlan === 'yearly' ? 249.00 : 29.99;
            FeaturedListing::create([
                'event_id'               => $event->id,
                'user_id'                => $user->id,
                'plan_type'              => $this->selectedPlan,
                'stripe_subscription_id' => $subscription->stripe_id,
                'amount_paid'            => $amount,
                'starts_at'              => now(),
                'expires_at'             => null, // subscription-managed
            ]);

            $event->update(['is_featured' => true]);
            $this->checkoutSuccess = true;
            $this->checkoutOpen    = false;
            unset($this->event);
        } catch (\Exception $e) {
            $this->paymentError = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.show-event')->layout('layouts.app');
    }
}

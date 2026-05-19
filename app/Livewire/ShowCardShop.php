<?php

namespace App\Livewire;

use App\Models\CardShop;
use App\Models\Event;
use App\Models\FeaturedShop;
use Exception;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowCardShop extends Component
{
    public CardShop $shop;

    public bool $claimModalOpen = false;
    public string $claimNotes = '';

    public bool $shopCheckoutOpen = false;
    public bool $shopCheckoutSuccess = false;
    public string $shopSelectedPlan = 'monthly';
    public string $shopPaymentError = '';

    #[Computed]
    public function isSaved(): bool
    {
        return auth()->check() && auth()->user()->isSavedShop($this->shop);
    }

    #[Computed]
    public function savedCount(): int
    {
        return $this->shop->savedByUsers()->count();
    }

    #[Computed]
    public function upcomingEvents()
    {
        return Event::approved()
            ->where('card_shop_id', $this->shop->id)
            ->upcoming()
            ->orderBy('start_date')
            ->limit(5)
            ->get();
    }

    public function toggleSave(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $user = auth()->user();

        if ($user->isSavedShop($this->shop)) {
            $user->savedShops()->detach($this->shop->id);
        } else {
            $user->savedShops()->syncWithoutDetaching($this->shop->id);
        }

        unset($this->isSaved, $this->savedCount);
    }

    public function submitClaim(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $user = auth()->user();

        if ($this->shop->claims()->where('user_id', $user->id)->where('status', 'pending')->exists()) {
            $this->claimModalOpen = false;

            return;
        }

        $this->shop->claims()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'verification_notes' => $this->claimNotes,
        ]);

        $this->claimModalOpen = false;
        $this->claimNotes = '';
    }

    public function canFeatureShop(): bool
    {
        if (! auth()->check()) {
            return false;
        }
        $user = auth()->user();

        return $user->id === $this->shop->owner_user_id || $user->isSuperAdmin();
    }

    public function openShopCheckout(string $plan = 'monthly'): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $this->shopSelectedPlan = $plan;
        $this->shopPaymentError = '';
        $this->shopCheckoutSuccess = false;
        $this->shopCheckoutOpen = true;
        $this->createShopIntent();
    }

    public function selectShopPlan(string $plan): void
    {
        $this->shopSelectedPlan = $plan;
    }

    public function confirmShopSubscription(string $paymentMethodId): void
    {
        abort_unless($this->canFeatureShop(), 403);

        $this->shopPaymentError = '';

        try {
            $user = auth()->user();
            $priceId = config("stripe_products.prices.shop_{$this->shopSelectedPlan}");

            if (! $priceId) {
                $this->shopPaymentError = 'Shop subscription is not yet configured. Please contact support.';

                return;
            }

            $user->updateDefaultPaymentMethod($paymentMethodId);
            $subscription = $user->newSubscription('shop_featured', $priceId)->create($paymentMethodId);

            $amount = $this->shopSelectedPlan === 'yearly' ? 99.00 : 9.99;

            FeaturedShop::create([
                'card_shop_id' => $this->shop->id,
                'user_id' => $user->id,
                'plan_type' => $this->shopSelectedPlan,
                'stripe_subscription_id' => $subscription->stripe_id,
                'amount_paid' => $amount,
                'starts_at' => now(),
                'status' => 'active',
            ]);

            $this->shop->update(['is_featured' => true]);
            $this->shopCheckoutSuccess = true;
            $this->shopCheckoutOpen = false;
        } catch (Exception $e) {
            $this->shopPaymentError = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.show-card-shop')
            ->layout('layouts.app');
    }

    private function createShopIntent(): void
    {
        $user = auth()->user();
        $user->createOrGetStripeCustomer();
        $intent = $user->createSetupIntent();
        $this->dispatch('stripe-shop-intent-ready', clientSecret: $intent->client_secret);
    }
}

<?php

namespace App\Livewire;

use App\Models\FeaturedListing;
use App\Models\FeaturedShop;
use Exception;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BillingSettings extends Component
{
    public bool $promoterCheckoutOpen = false;
    public bool $promoterCheckoutSuccess = false;
    public string $promoterPlan = 'monthly';
    public string $promoterError = '';

    public bool $shopCheckoutOpen = false;
    public bool $shopCheckoutSuccess = false;
    public string $shopPlan = 'monthly';
    public string $shopError = '';

    public string $confirmCancel = ''; // 'promoter' | 'shop'

    public function mount(): void
    {
        abort_unless(auth()->check(), 401);
    }

    #[Computed]
    public function featuredListings()
    {
        return FeaturedListing::with('event')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
    }

    #[Computed]
    public function featuredShops()
    {
        return FeaturedShop::with('cardShop')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
    }

    #[Computed]
    public function promoterSubscription()
    {
        return auth()->user()->subscription('promoter');
    }

    #[Computed]
    public function shopSubscription()
    {
        return auth()->user()->subscription('shop_featured');
    }

    // ── Promoter Pass ───────────────────────────────────────────────────────

    public function openPromoterCheckout(string $plan = 'monthly'): void
    {
        $this->promoterPlan = $plan;
        $this->promoterError = '';
        $this->promoterCheckoutSuccess = false;
        $this->promoterCheckoutOpen = true;
        $this->createPromoterIntent();
    }

    public function selectPromoterPlan(string $plan): void
    {
        $this->promoterPlan = $plan;
    }

    public function confirmPromoterSubscription(string $paymentMethodId): void
    {
        $this->promoterError = '';

        try {
            $user = auth()->user();
            $priceId = config("stripe_products.prices.promoter_{$this->promoterPlan}");

            if (! $priceId) {
                $this->promoterError = 'Promoter Pass is not yet configured. Please contact support.';

                return;
            }

            $user->updateDefaultPaymentMethod($paymentMethodId);
            $user->newSubscription('promoter', $priceId)->create($paymentMethodId);
            $user->events()->where('status', 'approved')->update(['is_featured' => true]);

            $this->promoterCheckoutSuccess = true;
            $this->promoterCheckoutOpen = false;
            unset($this->promoterSubscription);
        } catch (Exception $e) {
            $this->promoterError = $e->getMessage();
        }
    }

    public function cancelPromoter(): void
    {
        try {
            auth()->user()->subscription('promoter')?->cancel();
            $this->confirmCancel = '';
            unset($this->promoterSubscription);
            $this->dispatch('notify', message: 'Promoter Pass cancelled. Access continues until the end of the billing period.');
        } catch (Exception $e) {
            $this->dispatch('notify', message: 'Could not cancel: ' . $e->getMessage());
        }
    }

    // ── Featured Shop ────────────────────────────────────────────────────────

    public function openShopCheckout(string $plan = 'monthly'): void
    {
        $this->shopPlan = $plan;
        $this->shopError = '';
        $this->shopCheckoutSuccess = false;
        $this->shopCheckoutOpen = true;
        $this->createShopIntent();
    }

    public function selectShopPlan(string $plan): void
    {
        $this->shopPlan = $plan;
    }

    public function cancelShopSubscription(): void
    {
        try {
            auth()->user()->subscription('shop_featured')?->cancel();
            $this->confirmCancel = '';
            unset($this->shopSubscription);
            $this->dispatch('notify', message: 'Shop subscription cancelled. Featured status continues until the end of the billing period.');
        } catch (Exception $e) {
            $this->dispatch('notify', message: 'Could not cancel: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.billing-settings')->layout('layouts.app');
    }

    private function createPromoterIntent(): void
    {
        $user = auth()->user();
        $user->createOrGetStripeCustomer();
        $intent = $user->createSetupIntent();
        $this->dispatch('stripe-promoter-intent-ready', clientSecret: $intent->client_secret);
    }

    private function createShopIntent(): void
    {
        $user = auth()->user();
        $user->createOrGetStripeCustomer();
        $intent = $user->createSetupIntent();
        $this->dispatch('stripe-shop-billing-intent-ready', clientSecret: $intent->client_secret);
    }
}

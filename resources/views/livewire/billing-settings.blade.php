<div class="max-w-3xl mx-auto py-10 px-4">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Billing & Listings</h1>
        <p class="text-gray-500 text-sm mt-1">Manage your featured listings, subscriptions, and payment settings.</p>
    </div>

    {{-- ── Promoter Pass ─────────────────────────────────────────────────── --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">Promoter Pass</h2>
            @if ($promoterCheckoutSuccess || (auth()->user()->isPromoter() && ! $this->promoterSubscription?->cancelled()))
                <span class="text-xs font-bold px-2.5 py-1 bg-amber-100 text-amber-700 rounded-full">Active</span>
            @endif
        </div>

        @if ($this->promoterSubscription && ! $this->promoterSubscription->cancelled())
            <div class="space-y-2 mb-4">
                <p class="text-sm text-gray-700">All your events are featured while your subscription is active.</p>
                @if ($this->promoterSubscription->asStripeSubscription()->current_period_end ?? null)
                    <p class="text-xs text-gray-400">
                        Next billing: {{ \Carbon\Carbon::createFromTimestamp($this->promoterSubscription->asStripeSubscription()->current_period_end)->format('M j, Y') }}
                    </p>
                @endif
            </div>
            @if ($confirmCancel === 'promoter')
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between gap-4">
                    <p class="text-sm text-red-700">Cancel your Promoter Pass? Your events will stop being featured at the end of the billing period.</p>
                    <div class="flex gap-2 shrink-0">
                        <button wire:click="$set('confirmCancel', '')" class="px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg hover:bg-gray-50">Keep it</button>
                        <button wire:click="cancelPromoter" class="px-3 py-1.5 text-xs font-semibold bg-red-600 text-white rounded-lg hover:bg-red-700">Yes, cancel</button>
                    </div>
                </div>
            @else
                <button wire:click="$set('confirmCancel', 'promoter')" class="text-xs text-gray-400 hover:text-red-500 transition-colors">Cancel subscription</button>
            @endif
        @elseif ($this->promoterSubscription?->cancelled())
            <p class="text-sm text-gray-500 mb-3">
                Your Promoter Pass is cancelled but active until
                {{ \Carbon\Carbon::createFromTimestamp($this->promoterSubscription->asStripeSubscription()->current_period_end)->format('M j, Y') }}.
            </p>
            <button wire:click="openPromoterCheckout" class="px-4 py-2 text-sm font-semibold text-white rounded-xl" style="background-color:#D93C3F;">
                Reactivate Promoter Pass
            </button>
        @else
            <p class="text-sm text-gray-500 mb-4">Feature all your events for as long as you're subscribed. Great for regular promoters.</p>
            <div class="grid grid-cols-2 gap-3">
                <button wire:click="openPromoterCheckout('monthly')"
                        class="border-2 rounded-xl p-4 text-left transition-colors
                            {{ $promoterPlan === 'monthly' ? 'border-[#D93C3F] bg-red-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <p class="text-sm font-bold text-gray-900">Monthly</p>
                    <p class="text-xl font-extrabold text-gray-900 mt-1">$29.99<span class="text-sm font-normal text-gray-500">/mo</span></p>
                </button>
                <button wire:click="openPromoterCheckout('yearly')"
                        class="relative border-2 rounded-xl p-4 text-left transition-colors
                            {{ $promoterPlan === 'yearly' ? 'border-[#D93C3F] bg-red-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <span class="absolute top-2 right-2 text-[10px] font-bold bg-green-100 text-green-700 px-1.5 py-0.5 rounded-full">Save $111</span>
                    <p class="text-sm font-bold text-gray-900">Yearly</p>
                    <p class="text-xl font-extrabold text-gray-900 mt-1">$249<span class="text-sm font-normal text-gray-500">/yr</span></p>
                </button>
            </div>
        @endif
    </div>

    {{-- ── Featured Events ───────────────────────────────────────────────── --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-6 mb-6">
        <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500 mb-4">Featured Events</h2>

        @if ($this->featuredListings->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">No featured events yet. Visit an event page to feature it.</p>
        @else
            <div class="space-y-3">
                @foreach ($this->featuredListings as $listing)
                    <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0 gap-4">
                        <div class="min-w-0">
                            @if ($listing->event)
                                <a href="{{ $listing->event->path() }}" class="text-sm font-semibold text-gray-900 hover:underline truncate block">
                                    {{ $listing->event->name }}
                                </a>
                            @else
                                <p class="text-sm font-semibold text-gray-400 italic">Event removed</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ ucfirst(str_replace('_', ' ', $listing->plan_type)) }} ·
                                ${{ number_format($listing->amount_paid, 2) }} ·
                                Started {{ $listing->starts_at->format('M j, Y') }}
                            </p>
                        </div>
                        <div class="shrink-0 text-right">
                            @if ($listing->isActive())
                                <span class="text-xs font-semibold text-green-600">Active</span>
                                @if ($listing->expires_at)
                                    <p class="text-xs text-gray-400">Expires {{ $listing->expires_at->format('M j, Y') }}</p>
                                @else
                                    <p class="text-xs text-gray-400">Subscription-managed</p>
                                @endif
                            @else
                                <span class="text-xs font-semibold text-gray-400">Expired</span>
                                @if ($listing->event)
                                    <a href="{{ $listing->event->path() }}" class="block text-xs text-[#D93C3F] hover:underline mt-0.5">Renew</a>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Featured Shops ────────────────────────────────────────────────── --}}
    @if ($this->featuredShops->isNotEmpty() || $this->shopSubscription)
        <div class="bg-white border border-gray-100 rounded-2xl p-6 mb-6">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500 mb-4">Featured Shops</h2>

            @foreach ($this->featuredShops as $fs)
                <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0 gap-4">
                    <div class="min-w-0">
                        @if ($fs->cardShop)
                            <a href="{{ $fs->cardShop->path() }}" class="text-sm font-semibold text-gray-900 hover:underline truncate block">
                                {{ $fs->cardShop->name }}
                            </a>
                        @else
                            <p class="text-sm font-semibold text-gray-400 italic">Shop removed</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ ucfirst($fs->plan_type) }} · ${{ number_format($fs->amount_paid, 2) }} · Started {{ $fs->starts_at->format('M j, Y') }}
                        </p>
                    </div>
                    <div class="shrink-0 text-right">
                        @if ($fs->status === 'active')
                            <span class="text-xs font-semibold text-green-600">Active</span>
                            @if ($this->shopSubscription && ! $this->shopSubscription->cancelled())
                                @if ($confirmCancel === 'shop')
                                    <div class="mt-1">
                                        <button wire:click="cancelShopSubscription" class="text-xs font-semibold text-red-600 hover:underline">Confirm cancel</button>
                                        <span class="text-gray-300 mx-1">·</span>
                                        <button wire:click="$set('confirmCancel', '')" class="text-xs text-gray-400 hover:underline">Keep</button>
                                    </div>
                                @else
                                    <button wire:click="$set('confirmCancel', 'shop')" class="block text-xs text-gray-400 hover:text-red-500 mt-0.5">Cancel</button>
                                @endif
                            @endif
                        @else
                            <span class="text-xs font-semibold text-gray-400">Cancelled</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Promoter Pass checkout modal ──────────────────────────────────── --}}
    @if ($promoterCheckoutOpen)
        <div x-data="knuckleballBillingPromoter('{{ config('cashier.key') }}')"
             x-init="init()"
             @stripe-promoter-intent-ready.window="onIntent($event.detail.clientSecret)"
             class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6" @click.stop>
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Knuckleball Promoter Pass</h2>
                        <p class="text-sm text-gray-500">
                            @if ($promoterPlan === 'yearly') $249/year @else $29.99/month @endif
                        </p>
                    </div>
                    <button wire:click="$set('promoterCheckoutOpen', false)" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="size-5" />
                    </button>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest mb-2">Card Details</label>
                    <div x-ref="cardElement" class="border border-gray-200 rounded-xl px-3 py-3 bg-white"></div>
                </div>
                @if ($promoterError)
                    <p class="text-sm text-red-600 mb-3">{{ $promoterError }}</p>
                @endif
                <p x-show="cardError" x-text="cardError" class="text-sm text-red-600 mb-3"></p>
                <button @click="submit" :disabled="loading || !clientSecret"
                        class="w-full py-3 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
                        style="background-color:#D93C3F;">
                    <span x-show="!loading">Activate Promoter Pass</span>
                    <span x-show="loading">Processing…</span>
                </button>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
function knuckleballBillingPromoter(stripeKey) {
    return {
        stripe: null, card: null, clientSecret: null, loading: false, cardError: '',
        init() {
            this.stripe = Stripe(stripeKey);
            const els = this.stripe.elements();
            this.$nextTick(() => {
                this.card = els.create('card', {
                    style: { base: { fontSize: '15px', color: '#111827', '::placeholder': { color: '#9ca3af' } } },
                    hidePostalCode: true,
                });
                this.card.mount(this.$refs.cardElement);
                this.card.on('change', (e) => { this.cardError = e.error ? e.error.message : ''; });
            });
        },
        onIntent(secret) { this.clientSecret = secret; },
        async submit() {
            if (this.loading || !this.clientSecret) return;
            this.loading = true; this.cardError = '';
            const { setupIntent, error } = await this.stripe.confirmCardSetup(this.clientSecret, {
                payment_method: { card: this.card },
            });
            if (error) { this.cardError = error.message; this.loading = false; return; }
            await $wire.confirmPromoterSubscription(setupIntent.payment_method);
            this.loading = false;
        },
    };
}
</script>
@endpush

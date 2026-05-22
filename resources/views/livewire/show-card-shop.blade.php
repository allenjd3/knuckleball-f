<div class="max-w-3xl mx-auto py-10 px-4">

    {{-- Back --}}
    <a href="{{ route('shops.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-400 hover:text-gray-600 mb-6">
        <x-heroicon-o-arrow-left class="size-4" /> Card Shops
    </a>

    {{-- Hero Photo (store photo) --}}
    @if ($shop->heroPhoto())
        <div class="h-56 rounded-2xl overflow-hidden bg-gray-100 mb-6">
            <img src="{{ $shop->heroPhoto() }}" class="w-full h-full object-cover" alt="{{ $shop->name }}" />
        </div>
    @endif

    {{-- Badges & Title --}}
    <div class="flex items-center gap-2 flex-wrap mb-2">
        @if ($shop->is_featured)
            <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-amber-100 text-amber-700">★ Featured</span>
        @endif
        @foreach ($shop->categories as $cat)
            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 text-green-700">{{ $cat->name }}</span>
        @endforeach
    </div>

    <div class="flex items-start justify-between gap-4 mb-1">
        {{-- Logo + name --}}
        <div class="flex items-center gap-3">
            @if ($shop->logoUrl())
                <img src="{{ $shop->logoUrl() }}" class="size-12 rounded-xl object-contain bg-gray-50 border border-gray-100 p-1 shrink-0" alt="{{ $shop->name }} logo" />
            @endif
            <h1 class="text-2xl font-bold text-gray-900">{{ $shop->name }}</h1>
        </div>

        {{-- Save button --}}
        <button wire:click="toggleSave"
                class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold border rounded-xl transition-colors shrink-0
                    {{ $this->isSaved ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400' }}">
            <x-heroicon-o-bookmark class="size-4" />
            {{ $this->isSaved ? 'Saved' : 'Save' }}
            <span class="text-xs font-normal opacity-70">({{ $this->savedCount }})</span>
        </button>
    </div>

    <p class="text-gray-500 text-sm mb-1">{{ $shop->city }}, {{ $shop->state }}</p>

    {{-- Open Now indicator --}}
    <div class="flex items-center gap-2 mb-6">
        @if ($shop->isOpenNow())
            <span class="inline-flex items-center gap-1 text-sm font-semibold text-green-600">
                <span class="size-2 rounded-full bg-green-500 inline-block"></span> Open now
            </span>
            <span class="text-gray-300">·</span>
            <span class="text-sm text-gray-500">{{ $shop->todayHours() }}</span>
        @else
            <span class="inline-flex items-center gap-1 text-sm text-gray-400">
                <span class="size-2 rounded-full bg-gray-300 inline-block"></span> Closed
            </span>
            @if ($shop->todayHours() !== 'Closed')
                <span class="text-gray-300">·</span>
                <span class="text-sm text-gray-500">Today: {{ $shop->todayHours() }}</span>
            @endif
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Main Column --}}
        <div class="md:col-span-2 space-y-6">

            {{-- Description --}}
            @if ($shop->description)
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-3">About</h2>
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $shop->description }}</p>
                </div>
            @endif

            {{-- Hours --}}
            @if ($shop->hours)
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-3">Hours</h2>
                    <div class="space-y-1.5">
                        @foreach (['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                            @php $h = $shop->hours[$day] ?? null; $isToday = strtolower(now()->format('l')) === $day; @endphp
                            <div class="flex justify-between text-sm {{ $isToday ? 'font-semibold text-gray-900' : 'text-gray-600' }}">
                                <span>{{ ucfirst($day) }}</span>
                                <span>{{ $h && !empty($h['open']) ? ($h['open'] . ' – ' . ($h['close'] ?? '?')) : 'Closed' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Photo Gallery --}}
            @if ($shop->photos && count($shop->photos) > 1)
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-3">Photos</h2>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($shop->photos as $photo)
                            <div class="aspect-square rounded-lg overflow-hidden bg-gray-100">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($photo) }}" class="w-full h-full object-cover" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Upcoming Card Shows --}}
            @if ($this->upcomingEvents->isNotEmpty())
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-3">Upcoming Events</h2>
                    <div class="space-y-3">
                        @foreach ($this->upcomingEvents as $event)
                            <a href="{{ $event->path() }}" class="flex items-center justify-between group">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 group-hover:underline">{{ $event->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $event->formattedDate() }}</p>
                                </div>
                                <x-heroicon-o-chevron-right class="size-4 text-gray-300 shrink-0" />
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">

            {{-- Contact --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-4 space-y-3">
                <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400">Contact</h2>

                @if ($shop->address)
                    <div class="flex gap-2 text-sm text-gray-700">
                        <x-heroicon-o-map-pin class="size-4 text-gray-400 shrink-0 mt-0.5" />
                        <span>{{ $shop->address }}<br>{{ $shop->city }}, {{ $shop->state }} {{ $shop->zip_code }}</span>
                    </div>
                @endif

                @if ($shop->phone)
                    <div class="flex gap-2 text-sm text-gray-700">
                        <x-heroicon-o-phone class="size-4 text-gray-400 shrink-0" />
                        <a href="tel:{{ $shop->phone }}" class="hover:underline">{{ $shop->phone }}</a>
                    </div>
                @endif

                @if ($shop->website)
                    <div class="flex gap-2 text-sm text-gray-700">
                        <x-heroicon-o-globe-alt class="size-4 text-gray-400 shrink-0" />
                        <a href="{{ $shop->website }}" target="_blank" rel="noopener" class="hover:underline truncate">
                            {{ parse_url($shop->website, PHP_URL_HOST) ?? $shop->website }}
                        </a>
                    </div>
                @endif

                @if ($shop->owner_name)
                    <div class="flex gap-2 text-sm text-gray-700">
                        <x-heroicon-o-user class="size-4 text-gray-400 shrink-0" />
                        <span>{{ $shop->owner_name }}</span>
                    </div>
                @endif
            </div>

            {{-- Map --}}
            @if ($shop->latitude && $shop->longitude)
                <x-map-single
                    :latitude="(float) $shop->latitude"
                    :longitude="(float) $shop->longitude"
                    :name="$shop->name"
                    :directionsUrl="$shop->googleMapsUrl()" />
            @elseif ($shop->address || $shop->city)
                <a href="{{ $shop->googleMapsUrl() }}" target="_blank" rel="noopener"
                   class="flex items-center justify-center gap-2 w-full py-2.5 text-sm font-semibold text-white rounded-xl"
                   style="background-color:#D93C3F;">
                    <x-heroicon-o-map-pin class="size-4" />
                    Get Directions
                </a>
            @endif

            {{-- Feature This Shop --}}
            @if ($this->canFeatureShop())
                @if ($shopCheckoutSuccess || $shop->is_featured)
                    <div class="flex items-center gap-2 justify-center text-sm font-semibold text-amber-600 bg-amber-50 border border-amber-200 rounded-xl py-2.5 px-3">
                        <span>★</span> Featured listing active
                    </div>
                @else
                    <button wire:click="openShopCheckout"
                            class="w-full text-sm font-semibold text-white rounded-xl py-2.5 transition-colors"
                            style="background-color:#D93C3F;">
                        ★ Feature This Shop
                    </button>
                @endif
            @endif

            {{-- Claim this shop --}}
            @auth
                @if (! $shop->owner_user_id)
                    <button wire:click="$set('claimModalOpen', true)"
                            class="w-full text-sm font-medium text-gray-500 border border-gray-200 rounded-xl py-2 hover:bg-gray-50 transition-colors">
                        Claim this shop
                    </button>
                @elseif ($shop->ownerUser->id === auth()->id())
                    <div class="text-xs text-gray-400 text-center px-2">
                        You are the verified owner of this shop.
                    </div>
                @endif
            @endauth

        </div>
    </div>

    {{-- Shop Checkout Modal --}}
    @if ($shopCheckoutOpen)
        <div x-data="knuckleballShopCheckout('{{ config('cashier.key') }}')"
             x-init="init()"
             @stripe-shop-intent-ready.window="onIntent($event.detail.clientSecret)"
             @stripe-shop-payment-action-required.window="onPaymentActionRequired($event.detail.clientSecret)"
             class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6" @click.stop>

                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-bold text-gray-900">Feature This Shop</h2>
                    <button wire:click="$set('shopCheckoutOpen', false)" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="size-5" />
                    </button>
                </div>

                {{-- Plan selector --}}
                <div class="grid grid-cols-2 gap-3 mb-5">
                    {{-- Monthly --}}
                    <button wire:click="selectShopPlan('monthly')"
                            class="relative border-2 rounded-xl p-4 text-left transition-colors
                                {{ $shopSelectedPlan === 'monthly' ? 'border-[#D93C3F] bg-red-50' : 'border-gray-200 hover:border-gray-300' }}">
                        <p class="text-sm font-bold text-gray-900">Monthly</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">$9.99<span class="text-sm font-normal text-gray-500">/mo</span></p>
                        <p class="text-xs text-gray-400 mt-1">Billed monthly</p>
                        @if ($shopSelectedPlan === 'monthly')
                            <span class="absolute top-2 right-2 size-4 rounded-full bg-[#D93C3F] flex items-center justify-center">
                                <x-heroicon-s-check class="size-2.5 text-white" />
                            </span>
                        @endif
                    </button>

                    {{-- Yearly --}}
                    <button wire:click="selectShopPlan('yearly')"
                            class="relative border-2 rounded-xl p-4 text-left transition-colors
                                {{ $shopSelectedPlan === 'yearly' ? 'border-[#D93C3F] bg-red-50' : 'border-gray-200 hover:border-gray-300' }}">
                        <span class="absolute top-2 right-10 text-[10px] font-bold uppercase tracking-wide bg-green-100 text-green-700 px-1.5 py-0.5 rounded-full">Save 2 months</span>
                        <p class="text-sm font-bold text-gray-900">Yearly</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">$99<span class="text-sm font-normal text-gray-500">/yr</span></p>
                        <p class="text-xs text-gray-400 mt-1">$8.25/month billed annually</p>
                        @if ($shopSelectedPlan === 'yearly')
                            <span class="absolute top-2 right-2 size-4 rounded-full bg-[#D93C3F] flex items-center justify-center">
                                <x-heroicon-s-check class="size-2.5 text-white" />
                            </span>
                        @endif
                    </button>
                </div>

                <p class="text-xs text-gray-500 mb-4">Your shop will appear first in search results with a Featured badge. Cancel any time.</p>

                {{-- Card Element --}}
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest mb-2">Card Details</label>
                    <div x-ref="shopCardElement" class="border border-gray-200 rounded-xl px-3 py-3 bg-white"></div>
                </div>

                {{-- Error --}}
                @if ($shopPaymentError)
                    <p class="text-sm text-red-600 mb-3">{{ $shopPaymentError }}</p>
                @endif
                <p x-show="cardError" x-text="cardError" class="text-sm text-red-600 mb-3"></p>

                <button @click="submit" :disabled="loading || !clientSecret"
                        class="w-full py-3 rounded-xl text-sm font-semibold text-white transition-colors disabled:opacity-50"
                        style="background-color:#D93C3F;">
                    <span x-show="!loading">Subscribe & Feature My Shop</span>
                    <span x-show="loading">Processing…</span>
                </button>

            </div>
        </div>
    @endif

    {{-- Claim Modal --}}
    @if ($claimModalOpen)
        <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center px-4" wire:click.self="$set('claimModalOpen', false)">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Claim this shop</h2>
                <p class="text-sm text-gray-500 mb-4">Tell us why you're the owner or manager of {{ $shop->name }}. Our team will review and verify your request.</p>
                <textarea wire:model="claimNotes"
                          rows="4"
                          placeholder="E.g. I am the owner and can provide business registration documents…"
                          class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30 mb-4"></textarea>
                <div class="flex gap-2 justify-end">
                    <button wire:click="$set('claimModalOpen', false)"
                            class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="submitClaim"
                            class="px-4 py-2 text-sm font-semibold text-white rounded-xl"
                            style="background-color:#D93C3F;">
                        Submit Claim
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
function knuckleballShopCheckout(stripeKey) {
    return {
        stripe: null,
        card: null,
        clientSecret: null,
        loading: false,
        cardError: '',

        init() {
            this.stripe = Stripe(stripeKey);
            const elements = this.stripe.elements();
            this.$nextTick(() => {
                this.card = elements.create('card', {
                    style: {
                        base: { fontSize: '15px', color: '#111827', '::placeholder': { color: '#9ca3af' } },
                    },
                });
                this.card.mount(this.$refs.shopCardElement);
                this.card.on('change', (e) => { this.cardError = e.error ? e.error.message : ''; });
            });
        },

        onIntent(secret) {
            this.clientSecret = secret;
        },

        async submit() {
            if (this.loading || !this.clientSecret) return;
            this.loading = true;
            this.cardError = '';

            const { setupIntent, error } = await this.stripe.confirmCardSetup(this.clientSecret, {
                payment_method: { card: this.card },
            });

            if (error) {
                this.cardError = error.message;
                this.loading = false;
                return;
            }

            await this.$wire.confirmShopSubscription(setupIntent.payment_method);
            this.loading = false;
        },

        async onPaymentActionRequired(clientSecret) {
            const { error } = await this.stripe.confirmCardPayment(clientSecret);
            if (error) {
                this.cardError = error.message;
                return;
            }
            await this.$wire.activateShopFeatured();
        },
    };
}
</script>
@endpush

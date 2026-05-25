<div class="max-w-3xl mx-auto py-10 px-4">

    @php $event = $this->event; @endphp

    {{-- Back --}}
    <a href="{{ route('events.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-400 hover:text-gray-600 mb-6">
        <x-heroicon-o-arrow-left class="size-4" /> Events
    </a>

    {{-- Hero --}}
    @if ($event->heroPhoto())
        <div class="h-56 rounded-2xl overflow-hidden bg-gray-100 mb-6">
            <img src="{{ $event->heroPhoto() }}" class="w-full h-full object-cover" alt="{{ $event->name }}" />
        </div>
    @endif

    {{-- Badges & Title --}}
    <div class="flex items-center gap-2 flex-wrap mb-2">
        <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-full
            {{ match($event->type) {
                'card_show'        => 'bg-blue-100 text-blue-700',
                'comic_con'        => 'bg-purple-100 text-purple-700',
                'memorabilia_show' => 'bg-green-100 text-green-700',
                default            => 'bg-indigo-100 text-indigo-700',
            } }}">
            {{ $event->getTypeLabel() }}
        </span>
        @if ($event->event_subtype)
            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">{{ $event->getSubtypeLabel() }}</span>
        @endif
        @if ($event->is_featured)
            <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-amber-100 text-amber-700">★ Featured</span>
        @endif
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $event->name }}</h1>
    <p class="text-gray-500 text-sm mb-6">{{ $event->formattedDate() }}
        @if ($event->start_time) · {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }}@endif
        @if ($event->end_time) – {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}@endif
    </p>

    {{-- Player card (for signings) --}}
    @if ($event->player)
        <div class="flex items-center gap-3 bg-white border border-gray-100 rounded-2xl p-4 mb-6">
            <div class="size-12 rounded-full overflow-hidden bg-gray-100 shrink-0">
                @if ($event->player->media?->url)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($event->player->media->url) }}" class="w-full h-full object-cover" />
                @else
                    <x-heroicon-o-user class="size-6 text-gray-300 m-3" />
                @endif
            </div>
            <div>
                <p class="text-xs uppercase tracking-widest text-gray-400 font-medium">Signing</p>
                <a href="{{ $event->player->path() }}" class="font-semibold text-gray-900 hover:underline">{{ $event->player->name }}</a>
            </div>
            @auth
                <button wire:click="saveToWatchlist" class="ml-auto text-xs font-semibold text-[#D93C3F] hover:underline">
                    + Watch Player
                </button>
            @endauth
        </div>
    @elseif ($event->custom_player_name)
        <div class="flex items-center gap-3 bg-white border border-gray-100 rounded-2xl p-4 mb-6">
            <div class="size-12 rounded-full overflow-hidden bg-gray-100 shrink-0 flex items-center justify-center">
                <x-heroicon-o-user class="size-6 text-gray-300" />
            </div>
            <div>
                <p class="text-xs uppercase tracking-widest text-gray-400 font-medium">Signing</p>
                <p class="font-semibold text-gray-900">{{ $event->custom_player_name }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Main column --}}
        <div class="md:col-span-2 space-y-6">

            {{-- Mail-in details (prominent) --}}
            @if ($event->isMailIn())
                <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 space-y-3">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-blue-700">Mail-In Instructions</h2>
                    @if ($event->submission_deadline)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Submission Deadline</span>
                            <span class="font-semibold">{{ $event->submission_deadline->format('M j, Y') }}</span>
                        </div>
                    @endif
                    @if ($event->expected_return_by)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Expected Return By</span>
                            <span class="font-semibold">{{ $event->expected_return_by->format('M j, Y') }}</span>
                        </div>
                    @endif
                    @if ($event->make_check_payable_to)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Make Check Payable To</span>
                            <span class="font-semibold">{{ $event->make_check_payable_to }}</span>
                        </div>
                    @endif
                    @if (!empty($event->payment_methods))
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Accepted Payments</span>
                            <span class="font-semibold">{{ implode(', ', array_map('ucfirst', $event->payment_methods)) }}</span>
                        </div>
                    @endif
                    @if ($event->return_envelope_required)
                        <p class="text-sm text-blue-700 font-medium">⚠ SASE (return envelope) required.</p>
                    @endif
                </div>
            @endif

            {{-- Description --}}
            @if ($event->description)
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-2">About This Event</h2>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $event->description }}</p>
                </div>
            @endif

            {{-- Pricing --}}
            @if (! empty($event->pricing_items))
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-3">Pricing</h2>
                    <div class="divide-y divide-gray-50">
                        @foreach ($event->pricing_items as $item)
                            <div class="flex items-center justify-between py-2 text-sm">
                                <span class="text-gray-700">{{ $item['label'] }}</span>
                                <span class="font-semibold text-gray-900">
                                    {{ $item['price'] !== null ? '$' . number_format($item['price'], 2) : '—' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    @if ($event->max_items || $event->personalization_allowed)
                        <div class="mt-3 pt-3 border-t border-gray-50 flex flex-wrap gap-3 text-xs text-gray-500">
                            @if ($event->max_items)
                                <span>Max {{ $event->max_items }} items per person</span>
                            @endif
                            @if ($event->personalization_allowed)
                                <span>· Personalization allowed</span>
                            @endif
                        </div>
                    @endif
                </div>
            @elseif ($event->max_items || $event->personalization_allowed || $event->fees_per_item)
                {{-- Legacy display for events created before pricing_items --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-3">Pricing</h2>
                    <div class="space-y-2 text-sm">
                        @if ($event->fees_per_item)
                            <div class="flex justify-between"><span class="text-gray-600">Fee per item</span><span class="font-semibold">${{ number_format($event->fees_per_item, 2) }}</span></div>
                        @endif
                        @if ($event->max_items)
                            <div class="flex justify-between"><span class="text-gray-600">Max items</span><span class="font-semibold">{{ $event->max_items }}</span></div>
                        @endif
                        @if ($event->personalization_allowed)
                            <div class="flex justify-between"><span class="text-gray-600">Personalization</span><span class="font-semibold">Yes</span></div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Card show photos --}}
            @if ($event->type === 'card_show' && !empty($event->photos))
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-3">Photos</h2>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($event->photos as $photo)
                            <div class="aspect-square rounded-lg overflow-hidden bg-gray-100">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($photo) }}" class="w-full h-full object-cover" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Expected signers (card shows) --}}
            @if ($event->expectedSigners->isNotEmpty())
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-3">Expected Signers</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ($event->expectedSigners as $signer)
                            <a href="{{ $signer->path() }}" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                                <div class="size-9 rounded-full overflow-hidden bg-gray-100 shrink-0">
                                    @if ($signer->media?->url)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($signer->media->url) }}" class="w-full h-full object-cover" />
                                    @else
                                        <x-heroicon-o-user class="size-4 text-gray-300 m-2.5" />
                                    @endif
                                </div>
                                <span class="text-sm font-medium text-gray-900 truncate">{{ $signer->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Additional info --}}
            @if ($event->special_instructions || $event->parking_info || $event->accessibility_notes || $event->age_restrictions)
                <div class="bg-white border border-gray-100 rounded-2xl p-5 space-y-3">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400">Additional Info</h2>
                    @if ($event->special_instructions)
                        <div><p class="text-xs font-semibold text-gray-500 mb-0.5">Special Instructions</p><p class="text-sm text-gray-700">{{ $event->special_instructions }}</p></div>
                    @endif
                    @if ($event->parking_info)
                        <div><p class="text-xs font-semibold text-gray-500 mb-0.5">Parking</p><p class="text-sm text-gray-700">{{ $event->parking_info }}</p></div>
                    @endif
                    @if ($event->accessibility_notes)
                        <div><p class="text-xs font-semibold text-gray-500 mb-0.5">Accessibility</p><p class="text-sm text-gray-700">{{ $event->accessibility_notes }}</p></div>
                    @endif
                    @if ($event->age_restrictions)
                        <div><p class="text-xs font-semibold text-gray-500 mb-0.5">Age Restrictions</p><p class="text-sm text-gray-700">{{ $event->age_restrictions }}</p></div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">

            {{-- CTA --}}
            @if ($event->registration_link)
                <a href="{{ $event->registration_link }}" target="_blank" rel="noopener"
                   class="block w-full text-center py-2.5 text-sm font-bold text-white rounded-xl"
                   style="background-color:#D93C3F;">
                    Register / Get Tickets ↗
                </a>
            @endif

            {{-- Location sidebar --}}
            @if ($event->isInPerson())
                <div class="bg-white border border-gray-100 rounded-2xl p-4 text-sm space-y-1">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Location</p>
                    @if ($event->venue_name)<p class="font-semibold text-gray-900">{{ $event->venue_name }}</p>@endif
                    @if ($event->address)<p class="text-gray-600">{{ $event->address }}</p>@endif
                    @if ($event->city)<p class="text-gray-600">{{ $event->city }}@if ($event->state), {{ $event->state }}@endif @if ($event->zip_code) {{ $event->zip_code }}@endif</p>@endif
                </div>

                @if ($event->latitude && $event->longitude)
                    <x-map-single
                        :latitude="(float) $event->latitude"
                        :longitude="(float) $event->longitude"
                        :name="$event->venue_name ?? $event->name"
                        :directionsUrl="$event->googleMapsUrl()"
                        height="220px" />
                @endif
            @endif

            {{-- Contact --}}
            @if ($event->contact_name || $event->contact_email)
                <div class="bg-white border border-gray-100 rounded-2xl p-4 text-sm">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Contact</p>
                    @if ($event->contact_name)<p class="font-semibold text-gray-900">{{ $event->contact_name }}</p>@endif
                    @if ($event->contact_email)
                        <a href="mailto:{{ $event->contact_email }}" class="text-[#D93C3F] hover:underline">{{ $event->contact_email }}</a>
                    @endif
                </div>
            @endif

            {{-- Feature this listing — always visible to creator/admin --}}
            @if ($this->canFeature())
                @php $oneTimePrice = in_array($event->type, ['card_show','comic_con','memorabilia_show']) ? '19.99' : '9.99'; @endphp
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-amber-700 mb-1">
                        {{ $event->is_featured ? '★ Currently Featured' : 'Boost This Listing' }}
                    </p>
                    @if ($event->status === 'pending')
                        <p class="text-xs text-amber-600 mb-3">Purchase now — your featured slot activates the moment your event is approved.</p>
                    @elseif ($event->is_featured)
                        <p class="text-xs text-amber-600 mb-3">Extend or upgrade your featured placement below.</p>
                    @else
                        <p class="text-xs text-amber-600 mb-3">Pinned placement, Featured badge, feed promotion, and Watchlist alerts for all relevant users.</p>
                    @endif

                    <div class="space-y-2">
                        <button wire:click="openCheckout('one_time')"
                                class="w-full text-sm font-semibold py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors">
                            One-time — ${{ $oneTimePrice }} <span class="text-xs font-normal opacity-80">· 30 days</span>
                        </button>

                        <div class="grid grid-cols-2 gap-2">
                            <button wire:click="openCheckout('monthly')"
                                    class="text-xs font-semibold py-2 bg-white border border-amber-300 text-amber-700 rounded-lg hover:bg-amber-50 transition-colors">
                                Monthly<br><span class="font-bold">$29.99</span><span class="font-normal">/mo</span>
                            </button>
                            <button wire:click="openCheckout('yearly')"
                                    class="relative text-xs font-semibold py-2 bg-white border border-amber-300 text-amber-700 rounded-lg hover:bg-amber-50 transition-colors">
                                <span class="absolute -top-2 left-1/2 -translate-x-1/2 text-[9px] font-bold bg-green-500 text-white px-1.5 py-0.5 rounded-full whitespace-nowrap">BEST VALUE</span>
                                Yearly<br><span class="font-bold">$249</span><span class="font-normal">/yr</span>
                            </button>
                        </div>
                        <p class="text-[10px] text-amber-500 text-center">Monthly/Yearly = Promoter Pass — all your events featured</p>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- Success banner --}}
    @if ($checkoutSuccess)
        <div class="fixed bottom-6 right-6 z-50 bg-green-600 text-white text-sm font-semibold px-5 py-3 rounded-2xl shadow-lg flex items-center gap-2">
            <x-heroicon-o-check-circle class="size-5" />
            Your listing is now featured!
        </div>
    @endif

    {{-- Checkout modal — Alpine.js + Stripe Elements --}}
    @if ($checkoutOpen)
        @php
            $isOneTime = $selectedPlan === 'one_time';
            $isPromoter = in_array($selectedPlan, ['monthly', 'yearly']);
            $planLabel = match($selectedPlan) {
                'one_time' => 'Feature This Event',
                'monthly'  => 'Promoter Monthly Pass',
                'yearly'   => 'Promoter Annual Pass',
            };
            $priceLabel = match($selectedPlan) {
                'one_time' => '$' . (in_array($event->type, ['card_show','comic_con','memorabilia_show']) ? '19.99' : '9.99'),
                'monthly'  => '$29.99/mo',
                'yearly'   => '$249/yr',
            };
        @endphp

        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
             x-data="knuckleballCheckout('{{ config('cashier.key') }}', '{{ $selectedPlan }}')"
             x-init="init()"
             @stripe-intent-ready.window="onIntent($event.detail.clientSecret, $event.detail.intentType)"
             wire:ignore.self>

            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl" @click.stop>

                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-gray-900 text-lg">{{ $planLabel }}</h3>
                    <button wire:click="$set('checkoutOpen', false)" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="size-5" />
                    </button>
                </div>

                {{-- Plan summary --}}
                <div class="bg-gray-50 rounded-xl p-4 mb-4 space-y-2">
                    <div class="flex items-baseline justify-between">
                        <span class="text-sm font-bold text-gray-900">{{ $planLabel }}</span>
                        <span class="text-lg font-bold text-gray-900">{{ $priceLabel }}</span>
                    </div>
                    @if ($isOneTime)
                        <ul class="text-xs text-gray-500 space-y-1">
                            <li>✓ Pinned placement above standard listings</li>
                            <li>✓ Featured badge on listing and feed card</li>
                            <li>✓ Promoted in the community feed</li>
                            <li>✓ Watchlist notifications pushed to all relevant users</li>
                            <li>✓ Active for 30 days</li>
                        </ul>
                    @else
                        <ul class="text-xs text-gray-500 space-y-1">
                            <li>✓ Unlimited featured event listings</li>
                            <li>✓ Priority placement above other featured listings</li>
                            <li>✓ Verified Promoter badge on your profile</li>
                            @if ($selectedPlan === 'yearly')
                                <li class="text-green-600 font-medium">✓ 2 months free vs. monthly</li>
                            @else
                                <li>✓ Cancel anytime</li>
                            @endif
                        </ul>
                    @endif
                </div>

                {{-- Stripe card element --}}
                <div x-ref="cardElement"
                     class="border border-gray-200 rounded-xl px-3 py-3 mb-4 min-h-[42px]"
                     :class="{ 'border-red-300': cardError }"></div>

                <p x-show="cardError" x-text="cardError" class="text-xs text-red-500 mb-3"></p>

                @if ($paymentError)
                    <p class="text-xs text-red-500 mb-3">{{ $paymentError }}</p>
                @endif

                <button @click="submit()"
                        :disabled="loading || !ready"
                        class="w-full py-3 text-sm font-bold text-white rounded-xl transition-opacity disabled:opacity-50"
                        style="background-color:#D93C3F;">
                    <span x-show="!loading">
                        {{ $isOneTime ? 'Pay ' . $priceLabel . ' Now' : 'Subscribe ' . $priceLabel }}
                    </span>
                    <span x-show="loading">Processing…</span>
                </button>

                <p class="text-xs text-gray-400 text-center mt-3">
                    Secured by Stripe. Your card info never touches our servers.
                </p>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
function knuckleballCheckout(stripeKey, plan) {
    return {
        stripe: null,
        card: null,
        clientSecret: null,
        intentType: null,
        loading: false,
        ready: false,
        cardError: '',

        init() {
            this.stripe = Stripe(stripeKey);
            const elements = this.stripe.elements();
            this.card = elements.create('card', {
                style: { base: { fontSize: '14px', color: '#111827', '::placeholder': { color: '#9CA3AF' } } }
            });
            this.$nextTick(() => {
                this.card.mount(this.$refs.cardElement);
                this.card.on('change', (e) => {
                    this.cardError = e.error ? e.error.message : '';
                    this.ready = e.complete;
                });
            });
        },

        onIntent(secret, type) {
            this.clientSecret = secret;
            this.intentType = type;
        },

        async submit() {
            if (!this.clientSecret || this.loading) return;
            this.loading = true;
            this.cardError = '';

            try {
                if (this.intentType === 'payment') {
                    const { paymentIntent, error } = await this.stripe.confirmCardPayment(
                        this.clientSecret, { payment_method: { card: this.card } }
                    );
                    if (error) throw error;
                    await $wire.confirmPayment(paymentIntent.id);
                } else {
                    const { setupIntent, error } = await this.stripe.confirmCardSetup(
                        this.clientSecret, { payment_method: { card: this.card } }
                    );
                    if (error) throw error;
                    await $wire.createPromoterSubscription(setupIntent.payment_method);
                }
            } catch(e) {
                this.cardError = e.message || 'Payment failed. Please try again.';
            }

            this.loading = false;
        }
    };
}
</script>
@endpush

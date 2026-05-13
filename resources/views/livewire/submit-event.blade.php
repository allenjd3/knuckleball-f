<div class="max-w-2xl mx-auto py-12 px-4">

    @if ($submitted)
        {{-- Success header --}}
        <div class="text-center py-10">
            <div class="size-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <x-heroicon-o-check-circle class="size-8 text-green-600" />
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Event Submitted!</h1>
            <p class="text-gray-500">Your event is under review and will go live within 24 hours.</p>
        </div>

        @auth
            @if ($featureSuccess || $promoterCheckoutSuccess)
                {{-- Already purchased --}}
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center mb-4">
                    <p class="text-lg font-bold text-amber-700 mb-1">★ Your listing will be featured!</p>
                    <p class="text-sm text-amber-600">It goes live the moment your event is approved.</p>
                </div>
            @else
                {{-- Feature upsell --}}
                @php $oneTimePrice = in_array($type, ['card_show','comic_con','memorabilia_show']) ? '$19.99' : '$9.99'; @endphp
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-amber-700 mb-1">Want more visibility?</p>
                    <p class="text-sm text-amber-700 font-semibold mb-1">Feature This Event</p>
                    <p class="text-xs text-amber-600 mb-4">Pinned placement, Featured badge, and Watchlist alerts to relevant collectors.</p>

                    <div class="space-y-2">
                        <button wire:click="openFeatureCheckout('one_time')"
                                class="w-full py-2.5 text-sm font-bold text-white rounded-xl"
                                style="background-color:#D93C3F;">
                            One-time — {{ $oneTimePrice }} <span class="font-normal opacity-80">· 30 days</span>
                        </button>
                        <div class="grid grid-cols-2 gap-2">
                            <button wire:click="openFeatureCheckout('monthly')"
                                    class="py-2.5 text-xs font-semibold bg-white border border-amber-300 text-amber-700 rounded-xl hover:bg-amber-50">
                                Promoter Monthly<br><span class="font-bold text-sm">$29.99</span><span class="text-xs font-normal">/mo</span>
                            </button>
                            <button wire:click="openFeatureCheckout('yearly')"
                                    class="relative py-2.5 text-xs font-semibold bg-white border border-amber-300 text-amber-700 rounded-xl hover:bg-amber-50">
                                <span class="absolute -top-2 left-1/2 -translate-x-1/2 text-[9px] font-bold bg-green-500 text-white px-1.5 py-0.5 rounded-full whitespace-nowrap">BEST VALUE</span>
                                Promoter Yearly<br><span class="font-bold text-sm">$249</span><span class="text-xs font-normal">/yr</span>
                            </button>
                        </div>
                        <p class="text-[10px] text-amber-500 text-center">Monthly/Yearly features ALL your events while subscribed</p>
                    </div>
                </div>
            @endif
        @endauth

        <div class="text-center pb-6">
            <a href="{{ route('events.index') }}" class="text-sm text-gray-400 hover:underline">Skip for now · Browse Events</a>
        </div>

        {{-- Feature checkout modal --}}
        @if ($featureCheckoutOpen)
            @php
                $isOneTime   = $featurePlan === 'one_time';
                $oneTimePrice = in_array($type, ['card_show','comic_con','memorabilia_show']) ? '$19.99' : '$9.99';
                $planLabel   = match($featurePlan) {
                    'one_time' => "Feature This Event — {$oneTimePrice}",
                    'monthly'  => 'Promoter Monthly — $29.99/mo',
                    'yearly'   => 'Promoter Yearly — $249/yr',
                };
            @endphp
            <div x-data="knuckleballFeatureCheckout('{{ config('cashier.key') }}')"
                 x-init="init()"
                 @stripe-feature-intent-ready.window="onIntent($event.detail.clientSecret, $event.detail.intentType)"
                 class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center px-4">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.stop>
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-lg font-bold text-gray-900">{{ $planLabel }}</h2>
                        <button wire:click="$set('featureCheckoutOpen', false)" class="text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-x-mark class="size-5" />
                        </button>
                    </div>

                    @if (! $isOneTime)
                        <ul class="space-y-1.5 mb-4">
                            @foreach (['All your events featured while subscribed', 'Activates when your event is approved', 'Cancel any time'] as $perk)
                                <li class="flex items-center gap-2 text-sm text-gray-600">
                                    <x-heroicon-s-check-circle class="size-4 text-green-500 shrink-0" />{{ $perk }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500 mb-4">Your featured slot activates the moment your event is approved. Active for 30 days.</p>
                    @endif

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest mb-2">Card Details</label>
                        <div x-ref="cardElement" class="border border-gray-200 rounded-xl px-3 py-3 bg-white"></div>
                    </div>

                    @if ($featureError)
                        <p class="text-sm text-red-600 mb-3">{{ $featureError }}</p>
                    @endif
                    <p x-show="cardError" x-text="cardError" class="text-sm text-red-600 mb-3"></p>

                    <button @click="submit" :disabled="loading || !clientSecret"
                            class="w-full py-3 rounded-xl text-sm font-bold text-white disabled:opacity-50"
                            style="background-color:#D93C3F;">
                        <span x-show="!loading">{{ $isOneTime ? "Pay {$oneTimePrice}" : 'Subscribe & Feature' }}</span>
                        <span x-show="loading">Processing…</span>
                    </button>
                </div>
            </div>
        @endif

        {{-- Promoter Pass checkout modal (legacy path from old upsell buttons) --}}
        @if ($promoterCheckoutOpen)
            <div x-data="knuckleballPromoterCheckout('{{ config('cashier.key') }}')"
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

                    <ul class="space-y-2 mb-5">
                        @foreach (['All your events featured while subscribed', 'Priority placement in search results', 'Featured badge on your profile', 'Cancel any time from billing settings'] as $perk)
                            <li class="flex items-center gap-2 text-sm text-gray-700">
                                <x-heroicon-s-check-circle class="size-4 text-green-500 shrink-0" />
                                {{ $perk }}
                            </li>
                        @endforeach
                    </ul>

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest mb-2">Card Details</label>
                        <div x-ref="promoterCardElement" class="border border-gray-200 rounded-xl px-3 py-3 bg-white"></div>
                    </div>

                    @if ($promoterError)
                        <p class="text-sm text-red-600 mb-3">{{ $promoterError }}</p>
                    @endif
                    <p x-show="cardError" x-text="cardError" class="text-sm text-red-600 mb-3"></p>

                    <button @click="submit" :disabled="loading || !clientSecret"
                            class="w-full py-3 rounded-xl text-sm font-semibold text-white transition-colors disabled:opacity-50"
                            style="background-color:#D93C3F;">
                        <span x-show="!loading">Activate Promoter Pass</span>
                        <span x-show="loading">Processing…</span>
                    </button>

                </div>
            </div>
        @endif

    @else

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Submit an Event</h1>
        <p class="text-gray-500 text-sm mt-1">Player signings, card shows, and mail-in opportunities for the community.</p>
    </div>

    <form wire:submit="submit" class="space-y-8">

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
                <p class="text-sm font-semibold text-red-700 mb-2">Please fix the following before submitting:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="text-sm text-red-600">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Section: Event Type --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-6 space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">Event Type</h2>

            <div class="grid grid-cols-2 gap-3">
                <button type="button"
                    wire:click="$set('type', 'player_signing')"
                    class="flex flex-col items-center gap-2 p-4 border-2 rounded-xl transition-colors text-sm font-semibold
                        {{ $type === 'player_signing' ? 'border-[#D93C3F] bg-red-50 text-[#D93C3F]' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                    <x-heroicon-o-pencil class="size-5" />
                    Player Signing
                </button>
                <button type="button"
                    wire:click="$set('type', 'card_show')"
                    class="flex flex-col items-center gap-2 p-4 border-2 rounded-xl transition-colors text-sm font-semibold
                        {{ $type === 'card_show' ? 'border-[#D93C3F] bg-red-50 text-[#D93C3F]' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                    <x-heroicon-o-squares-2x2 class="size-5" />
                    Card Show
                </button>
                <button type="button"
                    wire:click="$set('type', 'comic_con')"
                    class="flex flex-col items-center gap-2 p-4 border-2 rounded-xl transition-colors text-sm font-semibold
                        {{ $type === 'comic_con' ? 'border-[#D93C3F] bg-red-50 text-[#D93C3F]' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                    <x-heroicon-o-star class="size-5" />
                    Comic Con
                </button>
                <button type="button"
                    wire:click="$set('type', 'memorabilia_show')"
                    class="flex flex-col items-center gap-2 p-4 border-2 rounded-xl transition-colors text-sm font-semibold
                        {{ $type === 'memorabilia_show' ? 'border-[#D93C3F] bg-red-50 text-[#D93C3F]' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                    <x-heroicon-o-trophy class="size-5" />
                    Memorabilia Show
                </button>
            </div>

            @if ($type === 'player_signing')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Signing Format</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" wire:click="$set('event_subtype', 'in_person')"
                            class="p-3 border-2 rounded-xl text-sm font-semibold transition-colors
                                {{ $event_subtype === 'in_person' ? 'border-[#D93C3F] bg-red-50 text-[#D93C3F]' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                            In Person
                        </button>
                        <button type="button" wire:click="$set('event_subtype', 'mail_in')"
                            class="p-3 border-2 rounded-xl text-sm font-semibold transition-colors
                                {{ $event_subtype === 'mail_in' ? 'border-[#D93C3F] bg-red-50 text-[#D93C3F]' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                            Mail In
                        </button>
                    </div>
                    @error('event_subtype') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div x-data="{ open: false }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Player <span class="text-red-500">*</span></label>

                    @if ($playerName)
                        <div class="flex items-center gap-2 border border-gray-200 rounded-xl px-3 py-2 bg-gray-50">
                            <span class="flex-1 text-sm font-medium text-gray-900">{{ $playerName }}</span>
                            <button type="button" wire:click="clearPlayer" class="text-gray-400 hover:text-red-400">
                                <x-heroicon-o-x-mark class="size-4" />
                            </button>
                        </div>
                    @else
                        <div class="relative" x-data="{ open: false }">
                            <input type="text"
                                   wire:model.live.debounce.250ms="playerQuery"
                                   x-on:focus="open = true"
                                   x-on:blur="setTimeout(() => open = false, 200)"
                                   placeholder="Search by player name…"
                                   autocomplete="off"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />

                            @php $results = strlen($playerQuery) >= 2 ? $this->getPlayerResults() : []; @endphp
                            <div x-show="open && {{ strlen($playerQuery) >= 2 ? 'true' : 'false' }}"
                                 class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                                @forelse ($results as $player)
                                    <button type="button"
                                            wire:click="selectPlayer({{ $player['id'] }}, '{{ addslashes($player['name']) }}')"
                                            class="w-full text-left px-4 py-2.5 text-sm text-gray-800 hover:bg-gray-50 border-b border-gray-100 last:border-0">
                                        {{ $player['name'] }}
                                    </button>
                                @empty
                                    @if (strlen($playerQuery) >= 2)
                                        <div class="px-4 py-3 space-y-2">
                                            <p class="text-xs text-gray-400">No players found for "{{ $playerQuery }}"</p>
                                            <button type="button"
                                                    wire:click="useCustomPlayer('{{ addslashes($playerQuery) }}')"
                                                    class="text-xs font-semibold text-[#D93C3F] hover:underline">
                                                + Use "{{ $playerQuery }}" as the player name
                                            </button>
                                        </div>
                                    @endif
                                @endforelse
                            </div>
                        </div>
                    @endif

                    @error('player_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Event Name <span class="text-red-500">*</span></label>
                <input type="text" wire:model="name" placeholder="e.g. Mike Piazza Private Signing"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Event Image</label>
                <p class="text-xs text-gray-400 mb-2">Flyer, banner, or any image that represents the event. Shown prominently on the listing.</p>
                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-gray-300 hover:bg-gray-50 transition-colors">
                    @if ($heroImage)
                        <img src="{{ $heroImage->temporaryUrl() }}" class="h-full w-full object-cover rounded-xl" />
                    @else
                        <div class="flex flex-col items-center gap-1 text-gray-400">
                            <x-heroicon-o-photo class="size-7" />
                            <span class="text-sm">Click to upload image</span>
                            <span class="text-xs">JPG, PNG, WEBP up to 5MB</span>
                        </div>
                    @endif
                    <input type="file" wire:model="heroImage" accept="image/*" class="hidden" />
                </label>
                @error('heroImage') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <div wire:loading wire:target="heroImage" class="text-xs text-gray-400 mt-1">Uploading…</div>
            </div>
        </div>

        {{-- Section: Date & Time --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-6 space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">Date & Time</h2>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="is_multi_day" class="rounded border-gray-300" />
                <span class="text-sm text-gray-700">Multi-day event</span>
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $is_multi_day ? 'Start Date' : 'Date' }} <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="start_date"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                    @error('start_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                @if ($is_multi_day)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" wire:model="end_date"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                        @error('end_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                    <input type="time" wire:model="start_time"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                    <input type="time" wire:model="end_time"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                </div>
            </div>
        </div>

        {{-- Section: Location --}}
        @if ($event_subtype !== 'mail_in')
            <div class="bg-white border border-gray-100 rounded-2xl p-6 space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">Location</h2>
                <p class="text-xs text-gray-400">We'll automatically geocode this for map display.</p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Venue Name</label>
                    <input type="text" wire:model="venue_name" placeholder="e.g. Convention Center Ballroom"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                    <input type="text" wire:model="address" placeholder="123 Main St"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input type="text" wire:model="city"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">State / Province</label>
                        <input type="text" wire:model="state" placeholder="NY"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                        <input type="text" wire:model="zip_code"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <select wire:model="country"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30">
                        @foreach (\App\Helpers\Countries::list() as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        {{-- Section: Pricing --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">Pricing</h2>
                    <p class="text-xs text-gray-400 mt-0.5">List each item and its price. E.g. Cards $20 · Photos $30 · VIP $100</p>
                </div>
                <button type="button" wire:click="addPricingItem"
                        class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 border border-gray-200 rounded-xl hover:bg-gray-50 text-gray-600 transition-colors">
                    <x-heroicon-o-plus class="size-3.5" /> Add Item
                </button>
            </div>

            @if (empty($pricingItems))
                <p class="text-sm text-gray-400 text-center py-3 border border-dashed border-gray-200 rounded-xl">
                    No pricing added yet — click "Add Item" to list your prices.
                </p>
            @else
                <div class="space-y-2">
                    @foreach ($pricingItems as $i => $item)
                        <div class="flex items-center gap-2">
                            <input type="text"
                                   wire:model="pricingItems.{{ $i }}.label"
                                   placeholder="e.g. Cards, Photos, VIP"
                                   class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                            <div class="relative w-28 shrink-0">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                                <input type="number"
                                       wire:model="pricingItems.{{ $i }}.price"
                                       placeholder="0.00"
                                       min="0" step="0.01"
                                       class="w-full border border-gray-200 rounded-xl pl-6 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                            </div>
                            <button type="button" wire:click="removePricingItem({{ $i }})"
                                    class="text-gray-300 hover:text-red-400 transition-colors shrink-0">
                                <x-heroicon-o-x-mark class="size-5" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Signing-specific options --}}
            @if ($type === 'player_signing')
                <div class="pt-3 border-t border-gray-100 grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Items Per Person</label>
                        <input type="number" wire:model="max_items" min="1"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                    </div>
                    <div class="flex items-center mt-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="personalization_allowed" class="rounded border-gray-300" />
                            <span class="text-sm text-gray-700">Personalization allowed</span>
                        </label>
                    </div>
                </div>
            @endif

            @if ($type === 'player_signing' && $event_subtype === 'mail_in')
                    <div class="pt-2 border-t border-gray-100 space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400">Mail-In Details</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Submission Deadline</label>
                                <input type="date" wire:model="submission_deadline"
                                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Expected Return By</label>
                                <input type="date" wire:model="expected_return_by"
                                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Make Check Payable To</label>
                            <input type="text" wire:model="make_check_payable_to"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Accepted Payment Methods</label>
                            <div class="flex gap-4">
                                @foreach (['check' => 'Check', 'paypal' => 'PayPal', 'venmo' => 'Venmo'] as $val => $label)
                                    <label class="flex items-center gap-1.5 cursor-pointer text-sm text-gray-700">
                                        <input type="checkbox" value="{{ $val }}" wire:model="payment_methods" class="rounded border-gray-300" />
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="return_envelope_required" class="rounded border-gray-300" />
                            <span class="text-sm text-gray-700">Return envelope required (SASE)</span>
                        </label>
                    </div>
                @endif
        </div>

        @if ($type === 'card_show')
            <div class="bg-white border border-gray-100 rounded-2xl p-6 space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">Card Show Details</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea wire:model="description" rows="4" placeholder="Tell collectors what to expect..."
                              class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Promoter Name</label>
                        <input type="text" wire:model="promoter_name"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                        <input type="url" wire:model="website" placeholder="https://"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                    </div>
                </div>
            </div>
        @endif

        {{-- Section: Contact --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-6 space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">Contact Information</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Name</label>
                    <input type="text" wire:model="contact_name"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                    <input type="email" wire:model="contact_email"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Registration / Ticket Link</label>
                <input type="url" wire:model="registration_link" placeholder="https://"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Special Instructions</label>
                <textarea wire:model="special_instructions" rows="3"
                          class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30"></textarea>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <p class="text-xs text-gray-400">All submissions are reviewed before going live.</p>
            <button type="submit"
                    class="px-8 py-2.5 text-sm font-bold text-white rounded-full"
                    style="background-color:#D93C3F;">
                Submit Event
            </button>
        </div>

    </form>

    @endif {{-- @if ($submitted) --}}

</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
function knuckleballFeatureCheckout(stripeKey) {
    return {
        stripe: null, card: null, clientSecret: null, intentType: null, loading: false, cardError: '',
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
        onIntent(secret, type) { this.clientSecret = secret; this.intentType = type; },
        async submit() {
            if (this.loading || !this.clientSecret) return;
            this.loading = true; this.cardError = '';
            if (this.intentType === 'payment') {
                const { paymentIntent, error } = await this.stripe.confirmCardPayment(this.clientSecret, {
                    payment_method: { card: this.card },
                });
                if (error) { this.cardError = error.message; this.loading = false; return; }
                await $wire.confirmFeaturePayment(paymentIntent.id);
            } else {
                const { setupIntent, error } = await this.stripe.confirmCardSetup(this.clientSecret, {
                    payment_method: { card: this.card },
                });
                if (error) { this.cardError = error.message; this.loading = false; return; }
                await $wire.confirmFeatureSubscription(setupIntent.payment_method);
            }
            this.loading = false;
        },
    };
}

function knuckleballPromoterCheckout(stripeKey) {
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
                    hidePostalCode: true,
                });
                this.card.mount(this.$refs.promoterCardElement);
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

            await $wire.confirmPromoterSubscription(setupIntent.payment_method);
            this.loading = false;
        },
    };
}
</script>
@endpush

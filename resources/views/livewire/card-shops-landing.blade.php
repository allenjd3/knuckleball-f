<div class="max-w-5xl mx-auto py-10 px-4">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Card Shop Directory</h1>
            <p class="text-sm text-gray-500 mt-0.5">Find local card shops, dealers, and hobby stores near you</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="$toggle('showMap')"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                @if ($showMap)
                    <x-heroicon-o-list-bullet class="size-4" /> List
                @else
                    <x-heroicon-o-map class="size-4" /> Map
                @endif
            </button>
            @auth
                <a href="{{ route('shops.submit') }}"
                   class="px-4 py-1.5 text-sm font-semibold text-white rounded-xl"
                   style="background-color:#D93C3F;">
                    + Add Shop
                </a>
            @endauth
        </div>
    </div>

    {{-- Search + Filters --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <div class="relative">
            <x-heroicon-o-magnifying-glass class="size-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Search shops or city…"
                   class="pl-9 pr-4 py-1.5 text-sm border border-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30 w-56" />
        </div>

        <button wire:click="$set('filter', 'all')"
                class="px-3.5 py-1.5 text-sm font-semibold rounded-full border transition-colors
                    {{ $filter === 'all' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400' }}">
            All Shops
        </button>
        <button wire:click="$set('filter', 'near_me')"
                class="px-3.5 py-1.5 text-sm font-semibold rounded-full border transition-colors
                    {{ $filter === 'near_me' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400' }}">
            Near Me
        </button>

        @if ($this->categories->isNotEmpty())
            <select wire:model.live="categoryId"
                    class="px-3 py-1.5 text-sm border border-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30">
                <option value="0">All Categories</option>
                @foreach ($this->categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- Map view --}}
    @if ($showMap && count($this->mapLocations))
        <div class="mb-6">
            <x-map-view :locations="$this->mapLocations" height="450px" :zoom="5" />
        </div>
    @endif

    {{-- Shop list --}}
    <div class="space-y-3">
        @forelse ($this->shops as $shop)
            <a href="{{ $shop->path() }}" class="block group">
                <div class="bg-white border {{ $shop->is_featured ? 'border-amber-200 ring-1 ring-amber-200' : 'border-gray-100' }} rounded-2xl p-4 hover:shadow-sm transition-shadow">
                    <div class="flex gap-4">
                        {{-- Logo / Photo --}}
                        <div class="size-16 rounded-xl overflow-hidden bg-gray-50 border border-gray-100 shrink-0 flex items-center justify-center">
                            @if ($shop->logoUrl())
                                <img src="{{ $shop->logoUrl() }}" class="w-full h-full object-contain p-1.5" alt="{{ $shop->name }}" />
                            @elseif ($shop->heroPhoto())
                                <img src="{{ $shop->heroPhoto() }}" class="w-full h-full object-cover" alt="{{ $shop->name }}" />
                            @else
                                <x-heroicon-o-building-storefront class="size-7 text-gray-300" />
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                @if ($shop->is_featured)
                                    <span class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                                        ★ Featured
                                    </span>
                                @endif
                                @foreach ($shop->categories->take(3) as $cat)
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                                        {{ $cat->name }}
                                    </span>
                                @endforeach
                            </div>
                            <p class="font-semibold text-gray-900 group-hover:underline text-sm leading-snug">{{ $shop->name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $shop->city }}, {{ $shop->state }}
                                @if ($shop->phone)
                                    <span class="mx-1">·</span>{{ $shop->phone }}
                                @endif
                                <span class="mx-1">·</span>
                                @if ($shop->isOpenNow())
                                    <span class="text-green-600 font-medium">Open now</span>
                                @else
                                    <span class="text-gray-400">Closed</span>
                                @endif
                                · {{ $shop->todayHours() }}
                            </p>
                        </div>

                        {{-- Arrow --}}
                        <x-heroicon-o-chevron-right class="size-4 text-gray-300 shrink-0 self-center" />
                    </div>
                </div>
            </a>
        @empty
            <p class="text-center text-gray-400 text-sm py-16">No card shops found. Be the first to add one!</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $this->shops->links() }}</div>

</div>

<div class="max-w-5xl mx-auto py-10 px-4">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Events & Signings</h1>
            <p class="text-sm text-gray-500 mt-0.5">Player signings, card shows, and mail-in opportunities</p>
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
                <a href="{{ route('events.submit') }}"
                   class="px-4 py-1.5 text-sm font-semibold text-white rounded-xl"
                   style="background-color:#D93C3F;">
                    + Submit Event
                </a>
            @endauth
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach ([
            'all'              => 'All Events',
            'near_me'          => 'Near Me',
            'player_signing'   => 'Player Signings',
            'card_show'        => 'Card Shows',
            'comic_con'        => 'Comic Cons',
            'memorabilia_show' => 'Memorabilia Shows',
            'in_person'        => 'In Person',
            'mail_in'          => 'Mail In',
        ] as $key => $label)
            <button wire:click="$set('filter', '{{ $key }}')"
                    class="px-3.5 py-1.5 text-sm font-semibold rounded-full border transition-colors
                        {{ $filter === $key
                            ? 'bg-gray-900 text-white border-gray-900'
                            : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Date range --}}
    <div class="flex items-center gap-3 mb-6 text-sm">
        <label class="text-gray-500 shrink-0">From</label>
        <input type="date" wire:model.live="dateFrom"
               class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
        <label class="text-gray-500 shrink-0">to</label>
        <input type="date" wire:model.live="dateTo"
               class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
    </div>

    {{-- Map view --}}
    @if ($showMap && count($this->mapLocations))
        <div class="mb-6">
            <x-map-view :locations="$this->mapLocations" height="450px" :zoom="5" />
        </div>
    @endif

    {{-- Event list --}}
    <div class="space-y-3">
        @forelse ($this->events as $event)
            <a href="{{ $event->path() }}" class="block group">
                <div class="bg-white border {{ $event->is_featured ? 'border-amber-200 ring-1 ring-amber-200' : 'border-gray-100' }} rounded-2xl p-4 hover:shadow-sm transition-shadow">
                    <div class="flex gap-4">
                        {{-- Photo --}}
                        <div class="size-16 rounded-xl overflow-hidden bg-gray-100 shrink-0 flex items-center justify-center">
                            @if ($event->heroPhoto() && (auth()->check() || $event->type !== 'player_signing'))
                                <img src="{{ $event->heroPhoto() }}" class="w-full h-full object-cover" alt="{{ $event->name }}" />
                            @else
                                <x-heroicon-o-calendar class="size-7 text-gray-300" />
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded-full
                                    {{ match($event->type) {
                                        'card_show'        => 'bg-blue-100 text-blue-700',
                                        'comic_con'        => 'bg-purple-100 text-purple-700',
                                        'memorabilia_show' => 'bg-green-100 text-green-700',
                                        default            => 'bg-indigo-100 text-indigo-700',
                                    } }}">
                                    {{ $event->getTypeLabel() }}
                                </span>
                                @if ($event->event_subtype)
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                        {{ $event->getSubtypeLabel() }}
                                    </span>
                                @endif
                                @if ($event->is_featured)
                                    <span class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                                        ★ Featured
                                    </span>
                                @endif
                            </div>
                            <p class="font-semibold text-gray-900 group-hover:underline text-sm leading-snug">{{ $event->name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $event->formattedDate() }}
                                @if ($event->venue_name)
                                    <span class="mx-1">·</span>{{ $event->venue_name }}
                                @endif
                                @if ($event->city)
                                    <span class="mx-1">·</span>{{ $event->city }}{{ $event->state ? ', ' . $event->state : '' }}
                                @endif
                                @if ($event->fees_per_item)
                                    <span class="mx-1">·</span>${{ number_format($event->fees_per_item, 2) }}/item
                                @endif
                            </p>
                        </div>

                        {{-- Arrow --}}
                        <x-heroicon-o-chevron-right class="size-4 text-gray-300 shrink-0 self-center" />
                    </div>
                </div>
            </a>
        @empty
            <p class="text-center text-gray-400 text-sm py-16">No events found for this filter.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $this->events->links() }}</div>

</div>

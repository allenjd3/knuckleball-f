@push('page-title')Most Wanted Addresses — @endpush

<div class="min-h-screen bg-white">
    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <x-heroicon-o-map-pin class="size-6 text-[#CB504B]" />
                Most Wanted Addresses
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Collectors are looking for a way to reach these players. Know an address or a signing email? Add it on the player's page.
                @if ($this->totalOpenRequests > 0)
                    <span class="font-semibold text-gray-700">{{ number_format($this->totalOpenRequests) }} open {{ Str::plural('request', $this->totalOpenRequests) }}.</span>
                @endif
            </p>
        </div>

        <div class="flex flex-col divide-y divide-gray-100 border border-gray-100 rounded-2xl overflow-hidden">
            @forelse ($this->players as $player)
                <a href="{{ $player->path() }}" wire:key="wanted-{{ $player->id }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">
                    <div class="size-10 rounded-full bg-gray-100 overflow-hidden shrink-0 flex items-center justify-center">
                        @auth
                            @if ($player->media?->url)
                                <img src="{{ Storage::url($player->media->url) }}" alt="{{ $player->name }}" class="w-full h-full object-cover" />
                            @else
                                <x-heroicon-o-user class="size-5 text-gray-300" />
                            @endif
                        @else
                            <x-heroicon-o-user class="size-5 text-gray-300" />
                        @endauth
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $player->name }}</p>
                        @if ($player->team)
                            <p class="text-xs text-gray-500 truncate">{{ $player->team->name }}</p>
                        @endif
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-black text-[#CB504B] tabular-nums">{{ $player->open_requests_count }}</p>
                        <p class="text-[11px] text-gray-400">{{ Str::plural('request', $player->open_requests_count) }}</p>
                    </div>
                </a>
            @empty
                <p class="text-gray-400 text-sm text-center py-10">No open address requests right now. Nice work, everyone!</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $this->players->links() }}
        </div>
    </div>
</div>

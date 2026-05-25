<div class="max-w-2xl mx-auto py-12 px-4">

    <h1 class="text-2xl font-bold text-gray-900 mb-1">My Watchlist</h1>
    <p class="text-sm text-gray-500 mb-8">You'll be notified when signing events are posted for these players.</p>

    <div class="space-y-3">
        @forelse ($this->watchlist as $player)
            <div class="bg-white border border-gray-100 rounded-2xl p-4 flex items-center gap-4">
                {{-- Photo --}}
                <div class="size-12 rounded-full overflow-hidden bg-gray-100 shrink-0">
                    @if ($player->media?->url)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($player->media->url) }}" class="w-full h-full object-cover" />
                    @else
                        <x-heroicon-o-user class="size-5 text-gray-300 m-3.5" />
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <a href="{{ $player->path() }}" class="font-semibold text-gray-900 hover:underline text-sm">{{ $player->name }}</a>
                    @if ($player->latest_signing)
                        <p class="text-xs text-gray-400 mt-0.5">
                            <span class="text-indigo-600 font-medium">Upcoming signing:</span>
                            {{ $player->latest_signing->name }} · {{ $player->latest_signing->formattedDate() }}
                        </p>
                    @else
                        <p class="text-xs text-gray-400 mt-0.5">No upcoming signings</p>
                    @endif
                </div>

                <button wire:click="remove({{ $player->id }})"
                        wire:confirm="Remove {{ $player->name }} from your Watchlist?"
                        class="shrink-0 text-xs font-medium text-gray-400 hover:text-red-500 transition-colors">
                    Remove
                </button>
            </div>
        @empty
            <div class="text-center py-16 text-gray-400">
                <x-heroicon-o-eye class="size-10 mx-auto mb-3 text-gray-200" />
                <p class="text-sm">Your Watchlist is empty.</p>
                <p class="text-xs mt-1">Add players from their profile page to get signing alerts.</p>
            </div>
        @endforelse
    </div>

</div>

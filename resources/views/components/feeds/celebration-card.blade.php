<article class="bg-white border border-gray-200 rounded-lg p-4" {{ $attributes }}>
    <div class="flex items-start gap-3">
        <img class="size-10 rounded-full flex-shrink-0" src="{{ $photo }}" alt="{{ $user }}" />
        <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-500">
                <a href="{{ $userPath }}" class="font-semibold text-gray-800 hover:underline">{{ $user }}</a>
                got a return from
            </p>
            <p class="font-bold text-xl leading-tight mt-0.5">
                <a href="{{ $playerPath }}" class="hover:underline">{{ $player }}</a>
            </p>
            <div class="flex items-center gap-3 mt-2 flex-wrap">
                <p class="flex items-center gap-1 text-sm text-gray-400">
                    <x-heroicon-o-envelope-open class="size-4 flex-shrink-0" />
                    Returned {{ $dateReturned }}
                </p>
                @if ($turnaroundDays !== null)
                    <span class="text-sm font-bold">{{ $turnaroundDays }} {{ Str::plural('day', $turnaroundDays) }}</span>
                @endif
            </div>
            @if ($feed->comment)
                <p class="text-sm text-gray-600 mt-1">{{ $feed->comment }}</p>
            @endif
        </div>
    </div>

    @if ($heroPhoto)
        <div class="mt-3">
            <img src="{{ Storage::url($heroPhoto) }}" alt="Returned auto" class="max-h-72 rounded-md w-full object-contain bg-gray-50" />
        </div>
    @endif

    @if (count($cardPhotos) > 1)
        <div class="flex gap-2 mt-3">
            @foreach (array_slice($cardPhotos, 1, 4) as $cardPhoto)
                <img src="{{ Storage::url($cardPhoto) }}" alt="Card" class="size-20 object-cover rounded" />
            @endforeach
        </div>
    @endif

    <div class="mt-4 pt-3 border-t border-gray-100 space-y-2">
        <livewire:feed-reactions :feed="$feed" wire:key="reactions-{{ $feed->id }}" />
        <livewire:feed-comments :feed="$feed" wire:key="comments-{{ $feed->id }}" />
    </div>
</article>

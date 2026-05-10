<article class="bg-white border border-gray-200 rounded-lg p-4" {{ $attributes }}>
    <div class="flex items-start gap-3">
        <img class="size-10 rounded-full flex-shrink-0" src="{{ $photo }}" alt="{{ $user }}" />
        <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-500">
                <a href="{{ $userPath }}" class="font-semibold text-gray-800 hover:underline">{{ $user }}</a>
                added cards for
            </p>
            <p class="font-bold text-xl leading-tight mt-0.5">
                <a href="{{ $playerPath }}" class="hover:underline">{{ $player }}</a>
            </p>
            <p class="flex items-center gap-1 text-sm text-gray-400 mt-2">
                <x-heroicon-o-rectangle-stack class="size-4 flex-shrink-0" />
                {{ $dateSent }}
            </p>
        </div>
    </div>

    @if (count($cardPhotos) === 1)
        <div class="mt-3">
            <img src="{{ Storage::url($cardPhotos[0]) }}" alt="Card" class="max-h-72 rounded-md w-full object-contain bg-gray-50" />
        </div>
    @elseif (count($cardPhotos) > 1)
        <div class="relative mt-3 h-44">
            @foreach (array_slice($cardPhotos, 0, 3) as $i => $cardPhoto)
                <img
                    src="{{ Storage::url($cardPhoto) }}"
                    alt="Card"
                    class="absolute w-28 h-40 object-cover rounded shadow-sm"
                    style="left: {{ $i * 32 }}px; transform: rotate({{ [-4, 0, 4][$i] ?? 0 }}deg); z-index: {{ $i === 1 ? 10 : 0 }};"
                />
            @endforeach
        </div>
    @endif

    <div class="mt-4 pt-3 border-t border-gray-100 space-y-2">
        <livewire:feed-reactions :feed="$feed" wire:key="reactions-{{ $feed->id }}" />
        <livewire:feed-comments :feed="$feed" wire:key="comments-{{ $feed->id }}" />
    </div>
</article>

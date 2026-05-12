<article class="bg-white border border-gray-100 rounded-2xl overflow-hidden" {{ $attributes }}>
    <div class="p-4">
        <div class="flex items-start gap-3">
            <img class="size-9 rounded-full flex-shrink-0 object-cover" src="{{ $photo }}" alt="{{ $user }}" />
            <div class="flex-1 min-w-0">
                <p class="text-sm leading-snug">
                    <a href="{{ $userPath }}" class="font-semibold text-gray-900 hover:underline">{{ $user }}</a>
                    <span class="text-gray-500"> added {{ $photoCount }} {{ Str::plural('photo', $photoCount) }} to </span>
                    <a href="{{ $playerPath }}" class="font-semibold text-gray-900 hover:underline">{{ $player }}</a>
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $createdAt }}</p>
            </div>
            <button class="text-gray-300 hover:text-gray-500 transition-colors shrink-0">
                <x-heroicon-o-ellipsis-horizontal class="size-5" />
            </button>
        </div>

        @if (count($cardPhotos) > 0)
            @php $visible = array_slice($cardPhotos, 0, 3); $overflow = count($cardPhotos) - 3; @endphp
            <div class="mt-3 ml-12 grid grid-cols-3 gap-1.5">
                @foreach ($visible as $i => $cardPhoto)
                    <div class="relative aspect-square rounded-lg overflow-hidden bg-gray-100">
                        <img src="{{ Storage::url($cardPhoto) }}" alt="Card"
                             class="w-full h-full object-cover" />
                        @if ($i === 2 && $overflow > 0)
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                <span class="text-white font-bold text-lg">+{{ $overflow }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($feed->comment)
            <p class="text-sm text-gray-600 mt-2 ml-12">{{ $feed->comment }}</p>
        @endif
    </div>

    <div class="px-4 border-t border-gray-50">
        <div class="flex items-center justify-between py-2.5">
            <livewire:feed-reactions :feed="$feed" wire:key="reactions-{{ $feed->id }}" />
        </div>
        <div class="pb-3">
            <livewire:feed-comments :feed="$feed" wire:key="comments-{{ $feed->id }}" />
        </div>
    </div>
</article>

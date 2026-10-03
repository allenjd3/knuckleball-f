<article class="bg-white border border-gray-100 rounded-2xl overflow-hidden" {{ $attributes }}>
    <div class="p-4">
        <div class="flex items-start gap-3">
            <img class="size-9 rounded-full flex-shrink-0 object-cover" src="{{ $photo }}" alt="{{ $user }}" />
            <div class="flex-1 min-w-0">
                <p class="text-sm leading-snug">
                    <a href="{{ $userPath }}" class="font-semibold text-gray-900 hover:underline">{{ $user }}</a>
                    <span class="text-gray-500"> {{ $reason->headline() }} </span>
                    <a href="{{ $playerPath }}" class="font-semibold text-gray-900 hover:underline">{{ $player }}</a>
                </p>
                <p class="text-xs text-gray-400 mt-0.5 flex flex-wrap items-center gap-x-1">
                    <span class="inline-flex items-center gap-1 font-semibold text-amber-600">
                        <x-heroicon-o-map-pin class="size-3.5" />
                        {{ $reason->label() }}
                    </span>
                    <span class="mx-1">·</span>{{ $feed->created_at?->format('M j, Y') }}
                </p>
            </div>
        </div>

        @if ($feed->comment)
            <p class="text-sm text-gray-600 mt-2 ml-12">{{ $feed->comment }}</p>
        @endif

        <div class="mt-3 ml-12">
            @if ($isFulfilled)
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-green-700 bg-green-50 rounded-full px-3 py-1">
                    <x-heroicon-o-check-circle class="size-4" />
                    Address found
                </span>
            @elseif ($playerPath)
                <a href="{{ $playerPath }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#CB504B] border border-[#CB504B]/30 rounded-full px-3 py-1 hover:bg-red-50 transition-colors">
                    <x-heroicon-o-plus-circle class="size-4" />
                    Know it? Add the address
                </a>
            @endif
        </div>
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

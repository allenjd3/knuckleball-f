<article class="bg-white border border-blue-100 rounded-2xl overflow-hidden {{ $isFeatured ? 'ring-2 ring-blue-400' : '' }}" {{ $attributes }}>
    <div class="p-4">
        <div class="flex items-start gap-3">
            {{-- Hero photo or placeholder --}}
            <div class="size-10 rounded-lg overflow-hidden bg-blue-50 shrink-0 flex items-center justify-center">
                @if ($heroPhoto)
                    <img src="{{ $heroPhoto }}" class="w-full h-full object-cover" alt="{{ $eventName }}" />
                @else
                    <x-heroicon-o-calendar class="size-5 text-blue-400" />
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                    <span class="inline-flex items-center gap-1 text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded-full
                        {{ match($eventType) {
                            'card_show'        => 'bg-blue-100 text-blue-700',
                            'comic_con'        => 'bg-purple-100 text-purple-700',
                            'memorabilia_show' => 'bg-green-100 text-green-700',
                            default            => 'bg-indigo-100 text-indigo-700',
                        } }}">
                        @if ($eventType === 'card_show')
                            <x-heroicon-s-squares-2x2 class="size-3" /> Card Show
                        @elseif ($eventType === 'comic_con')
                            <x-heroicon-s-star class="size-3" /> Comic Con
                        @elseif ($eventType === 'memorabilia_show')
                            <x-heroicon-s-trophy class="size-3" /> Memorabilia Show
                        @else
                            <x-heroicon-s-pencil class="size-3" /> Player Signing
                        @endif
                    </span>
                    @if ($isFeatured)
                        <span class="inline-flex items-center gap-1 text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                            <x-heroicon-s-star class="size-3" />
                            Featured
                        </span>
                    @endif
                </div>

                <a href="{{ $eventPath }}" class="font-semibold text-gray-900 hover:underline text-sm leading-snug block">
                    {{ $eventName }}
                </a>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $date }}
                    @if ($venue)<span class="mx-1">·</span>{{ $venue }}@endif
                    @if ($cityState)<span class="mx-1">·</span>{{ $cityState }}@endif
                    @if ($fees)<span class="mx-1">·</span>{{ $fees }}@endif
                </p>
            </div>
        </div>
    </div>

    <div class="px-4 pb-3 flex items-center justify-between border-t border-blue-50 pt-3">
        <livewire:feed-reactions :feed="$feed" wire:key="reactions-{{ $feed->id }}" />
        <a href="{{ $eventPath }}"
           class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <x-heroicon-o-bookmark class="size-3.5" />
            View Event
        </a>
    </div>
</article>

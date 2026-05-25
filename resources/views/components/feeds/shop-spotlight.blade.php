<article class="bg-white border border-green-100 rounded-2xl overflow-hidden" {{ $attributes }}>
    <div class="p-4">
        <div class="flex items-start gap-3">
            {{-- Hero photo or placeholder --}}
            <div class="size-10 rounded-lg overflow-hidden bg-green-50 shrink-0 flex items-center justify-center">
                @if ($heroPhoto)
                    <img src="{{ $heroPhoto }}" class="w-full h-full object-cover" alt="{{ $shopName }}" />
                @else
                    <x-heroicon-o-building-storefront class="size-5 text-green-500" />
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="inline-flex items-center gap-1 text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                        <x-heroicon-s-building-storefront class="size-3" />
                        Card Shop
                    </span>
                </div>

                <a href="{{ $shopPath }}" class="font-semibold text-gray-900 hover:underline text-sm leading-snug block">
                    {{ $shopName }}
                </a>
                @if ($cityState)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $cityState }}</p>
                @endif

                @if (count($shopIds) > 1)
                    <p class="text-xs text-gray-400 mt-1">+{{ count($shopIds) - 1 }} more new shops added</p>
                @endif
            </div>
        </div>
    </div>

    <div class="px-4 pb-3 flex items-center justify-between border-t border-green-50 pt-3">
        <livewire:feed-reactions :feed="$feed" wire:key="reactions-{{ $feed->id }}" />
        <a href="{{ $shopPath }}"
           class="text-xs font-semibold text-green-700 hover:text-green-900 flex items-center gap-1">
            <x-heroicon-o-building-storefront class="size-3.5" />
            View Shop
        </a>
    </div>
</article>

<div>
    @if ($cards->isEmpty())
        <p class="text-sm text-gray-500 text-center py-8">No cards added yet.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            @foreach ($cards as $card)
                @php $url = $card->media->first()?->url; @endphp

                <div class="flex flex-col rounded-xl border border-gray-100 overflow-hidden bg-gray-50">
                    @if ($url)
                        <button
                            type="button"
                            class="flex items-center justify-center h-40 bg-gray-100 cursor-zoom-in"
                            @click="$dispatch('open-lightbox', { src: '{{ Storage::url($url) }}', alt: '{{ $card->manufacturer }} {{ $card->year }} {{ $card->series }}' })"
                        >
                            <img src="{{ Storage::url($url) }}"
                                 alt="{{ $card->manufacturer }} {{ $card->year }} {{ $card->series }}"
                                 class="max-h-40 max-w-full object-contain" />
                        </button>
                    @else
                        <div class="flex items-center justify-center h-40 bg-gray-100 text-gray-400 text-xs">
                            No image
                        </div>
                    @endif

                    <div class="px-3 py-2 text-xs">
                        <p class="font-semibold text-gray-900 truncate">{{ $card->year }} {{ $card->manufacturer }}</p>
                        <p class="text-gray-500 truncate">
                            {{ $card->series }}
                            @if ($card->number)
                                &middot; #{{ $card->number }}
                            @endif
                        </p>
                        @if ($card->variation)
                            <p class="text-gray-400 truncate">{{ $card->variation }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

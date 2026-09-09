<div>
    @if ($ttmPhotos->isNotEmpty() || $inPersonPhotos->isNotEmpty())
        <p class="text-xs font-bold uppercase tracking-widest text-gray-900 mb-2">Compare Autographs</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Through The Mail</p>
                @if ($ttmPhotos->isEmpty())
                    <p class="text-sm text-gray-400 italic">No TTM photos yet</p>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach ($ttmPhotos as $photo)
                            <button
                                type="button"
                                class="flex items-center justify-center h-28 bg-gray-100 rounded-xl cursor-zoom-in overflow-hidden"
                                @click="$dispatch('open-lightbox', { src: '{{ Storage::url($photo->url) }}', alt: 'Through the mail autograph' })"
                            >
                                <img src="{{ Storage::url($photo->url) }}"
                                     alt="Through the mail autograph"
                                     class="max-h-28 max-w-full object-contain" />
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">In Person</p>
                @if ($inPersonPhotos->isEmpty())
                    <p class="text-sm text-gray-400 italic">No in-person photos yet</p>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach ($inPersonPhotos as $photo)
                            <button
                                type="button"
                                class="flex items-center justify-center h-28 bg-gray-100 rounded-xl cursor-zoom-in overflow-hidden"
                                @click="$dispatch('open-lightbox', { src: '{{ Storage::url($photo->url) }}', alt: 'In person autograph' })"
                            >
                                <img src="{{ Storage::url($photo->url) }}"
                                     alt="In person autograph"
                                     class="max-h-28 max-w-full object-contain" />
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

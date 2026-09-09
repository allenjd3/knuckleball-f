<div>
    @if ($media->isEmpty())
        <p class="text-sm text-gray-500 text-center py-8">No photos added yet.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            @foreach ($media as $photo)
                <button
                    type="button"
                    class="flex items-center justify-center h-40 bg-gray-100 rounded-xl cursor-zoom-in overflow-hidden"
                    @click="$dispatch('open-lightbox', { src: '{{ Storage::url($photo->url) }}', alt: 'In person autograph photo' })"
                >
                    <img src="{{ Storage::url($photo->url) }}"
                         alt="In person autograph photo"
                         class="max-h-40 max-w-full object-contain" />
                </button>
            @endforeach
        </div>
    @endif
</div>

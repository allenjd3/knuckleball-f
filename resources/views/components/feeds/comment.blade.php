<article
    x-data="{showUrl: true}"
    class="py-2 grid grid-cols-[100px_1fr]" {{ $attributes }}
>
    <div class="flex justify-center items-center w-[100px]">
        <img class="size-12 rounded-full" src="{{ $photo }}" alt="{{ $user }}" />
    </div>
    <div class="min-w-0">
        <div class="flex justify-between">
            <span class="font-bold"><a href="{{ $user_path }}">{{ $user }}</a></span>
                <div>
                    <livewire:delete-comment
                        wire:key="{{ str()->random() }}"
                        :canDelete="(bool) auth()->user()?->can('delete', $feed->feedable)"
                        :commentId="$feed->feedable_id"
                    />
                </div>
        </div>
        <p class="mb-2 text-sm">commented on: {{ $date_sent }}</p>
        <div class="prose break-words">
            {!!
                str($feed->comment)->sanitizeHtml()
            !!}
        </div>
        @php
            use App\Dto\OgData;

            $ogData = OgData::make($feed->meta);
        @endphp
    </div>
    <div class="w-[100px]"></div>
    @if ($ogData->hasOgData())
        <a x-show="showUrl" href="{{ $ogData->url }}" class="relative block w-[min(400px,90%)] max-w-full border border-gray-300 mt-4">
            <button @click.prevent="showUrl = false;" class="absolute top-2 right-2 p-2 rounded-full bg-[rgba(255,255,255,0.3)] hover:bg-[rgba(255,255,255,0.8)]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
            @if ($ogData->image)
                <img src="{{ $ogData->image }}" alt="{{ $ogData->title }}"/>
            @endif
            <div class="p-2">
                <p class="font-bold">{{ $ogData->title }}</p>
                <p class="line-clamp-2">{{ $ogData->description }}</p>
            </div>
        </a>
    @endif
</article>

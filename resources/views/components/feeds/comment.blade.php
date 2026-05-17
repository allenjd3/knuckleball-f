<article
    x-data="{showUrl: true}"
    class="bg-white border border-gray-200 rounded-xl p-4 shadow-xs my-3" {{ $attributes }}
>
    <div class="flex gap-4">
        <div class="shrink-0">
            <img class="size-11 rounded-full object-cover ring-2 ring-gray-100" src="{{ $photo }}" alt="{{ $user }}" />
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2">
                <div>
                    <a href="{{ $user_path }}" class="font-semibold text-gray-900 hover:underline">{{ $user }}</a>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $date_sent }}</p>
                </div>
                <livewire:delete-comment
                    wire:key="{{ str()->random() }}"
                    :canDelete="(bool) auth()->user()?->can('delete', $feed->feedable)"
                    :commentId="$feed->feedable_id"
                />
            </div>

            <div class="prose prose-sm max-w-none mt-2 text-gray-700 break-words">
                {!! str($feed->comment)->sanitizeHtml() !!}
            </div>

            @php
                use App\Dto\OgData;
                $ogData = OgData::make($feed->meta);
            @endphp

            @if ($ogData->hasOgData())
                <a
                    x-show="showUrl"
                    href="{{ $ogData->url }}"
                    class="relative mt-3 block w-full max-w-sm border border-gray-200 rounded-lg overflow-hidden hover:border-gray-300 transition-colors"
                >
                    <button
                        @click.prevent="showUrl = false"
                        class="absolute top-2 right-2 p-1 rounded-full bg-white/80 hover:bg-white shadow-sm border border-gray-200"
                        title="Dismiss"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-gray-500" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                    @if ($ogData->image)
                        <img src="{{ $ogData->image }}" alt="{{ $ogData->title }}" class="w-full object-cover max-h-40" />
                    @endif
                    <div class="p-3 bg-gray-50">
                        <p class="text-sm font-semibold text-gray-900 leading-tight">{{ $ogData->title }}</p>
                        @if ($ogData->description)
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $ogData->description }}</p>
                        @endif
                    </div>
                </a>
            @endif
        </div>
    </div>
</article>

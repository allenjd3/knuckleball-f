<article class="py-2 grid grid-cols-[100px_1fr]" {{ $attributes }}>
    <div class="flex items-start justify-center items-center w-[100px]">
        <img class="size-12 rounded-full" src="{{ $photo }}" alt="{{ $user }}" />
    </div>
    <div>
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
        <div>{!! str($feed->comment)->sanitizeHtml() !!}</div>
    </div>
</article>

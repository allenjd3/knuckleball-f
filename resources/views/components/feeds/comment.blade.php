<article class="py-2 grid grid-cols-[100px_1fr]">
    <div class="flex items-start justify-center items-center w-[100px]">
        <img class="size-12 rounded-full" src="{{ $photo }}" alt="{{ $user }}" />
    </div>
    <div>
        <p><span class="font-bold"><a href="{{ $user_path }}">{{ $user }}</a></span></p>
        <p class="mb-2 text-sm">commented on: {{ $date_sent }}</p>
        @can('delete', $feed->feedable)
            <livewire:delete-comment :commentId="$feed->feedable_id" />
        @endcan
        <div>{{ $feed->comment }}</div>
    </div>
</article>

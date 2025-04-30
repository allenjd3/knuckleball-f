<article class="py-2 grid grid-cols-[100px,1fr]">
    <div class="flex items-start justify-center items-center w-[100px]">
        <img class="size-12 border rounded-full" src="{{ $photo }}" alt="{{ $user }}" />
    </div>
    <div>
        <p class="font-bold"><a href="{{ $player_path }}">{{ $player }}</a></p>
        <p class="mb-2 text-sm"><a href="{{ $user_path }}" class="underline font-bold">{{ $user }}</a> sent {{ $type ? $type . " " : "" }}on {{ $dateSent }}</p>
        <div>{{ $feed->comment }}</div>
        @if ($dateReturned)
            <p class="mt-2 font-bold">Returned {{ $dateReturned }}</p>
        @endif
    </div>
</article>

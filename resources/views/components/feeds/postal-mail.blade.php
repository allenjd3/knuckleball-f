<article class="py-2 grid grid-cols-[100px,1fr]">
    <div class="flex items-start justify-center w-[100px]">
        <img class="size-12 border rounded-full" src="{{ $photo }}" alt="{{ $user }}" />
    </div>
    <div>
        <p class="font-bold"><a href="{{ $player_path }}">{{ $player }}</a></p>
        <p class="mb-2"><a href="{{ $user_path }}" class="underline">{{ $user }}</a> sent {{ $type ? $type . " " : "" }}on {{ $dateSent }}</p>
        <div>{{ $feed->comment }}</div>
        <p class="mt-2 font-bold">Returned {{ $dateReturned }}</p>
    </div>
</article>

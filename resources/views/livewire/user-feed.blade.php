<div class="max-w-5xl mx-auto flex mt-8">
    <aside class="hidden sm:block sm:w-64">
        <a href="{{ auth()->check() ? route('users.profile', auth()->user()) : route('login') }}">My Feed</a>
    </aside>
    <main class="divide-y w-full">
    @foreach ($this->players as $player)
        @php
            $feed = $player->latestMail;
        @endphp
        <div class="p-4 cursor-pointer" @click="window.location.href='{{ route("players.show", $player->id ) }}'">
            <div class="flex items-center gap-4">
                <div class="size-12 inline-block rounded-full overflow-hidden">
                    <img src="{{ $feed->user->profile_photo_url }}" alt="{{ $feed->user->name }}" class="object-cover w-full h-full" />
                </div>
                <div>
                    <div class="font-bold">{{ $player->name }}</div>
                    <div class="mb-2">
                        <p class="text-sm">
                            <a href="{{ route('users.profile', $feed->user) }}" @click.stop="" class="font-bold underline">{{ $feed->user->name }}</a> sent {{ $feed->feeMaterials->pluck('name')->join(", ") }} on {{ $feed->date_sent?->format('M d, Y') }}
                        </p>
                    </div>
                    <div>{{ $feed->comment }}</div>
                    <div class="font-bold text-sm">
                        Returned Date: {{ $feed->returned_date ? $feed->returned_date?->format('M d, Y') : "Not yet returned" }}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    </main>
</div>

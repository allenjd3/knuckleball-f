<div class="max-w-5xl mx-auto flex mt-8">
    <aside class="hidden sm:block sm:w-64">
        <a href="{{ auth()->check() ? route('users.profile', auth()->user()) : route('login') }}">My Feed</a>
    </aside>
    <main class="divide-y w-full">
    @foreach ($this->feeds as $feed)
        <div class="p-4 cursor-pointer" @click="window.location.href='{{ route("players.show", $feed->player_id ) }}'">
            <div class="flex items-center gap-4">
                <div class="size-12 inline-block rounded-full overflow-hidden">
                    <img src="{{ $feed->user->profile_photo_url }}" alt="{{ $feed->user->name }}" class="object-cover w-full h-full" />
                </div>
                <div>
                    <a href='{{ route("users.profile", $feed->user) }}' @click.stop="" class="font-bold">
                        {{ $feed->user->name }}
                    </a>
                    <div>
                        <a href='{{ route("players.show", $feed->player_id) }}' class="font-bold">
                            {{ $feed->date_sent->format('M d, Y') }}: {{ $feed->feeMaterials->pluck('name')->join(", ") }}
                        </a>
                    </div>
                    <div>{{ $feed->comment }}</div>
                </div>
            </div>
        </div>
    @endforeach
    </main>
</div>

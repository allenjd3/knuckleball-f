<div class="max-w-5xl mx-auto divide-y flex">
    <aside class="hidden sm:block sm:w-64">
        <a href="">My Feed</a>
    </aside>
    <main>
    @foreach($this->feeds as $feed)
        <div class="p-4 cursor-pointer" @click="window.location.href='{{route("players.show", $feed->player_id )}}'">
            <div class="flex items-center gap-4">
                <div class="size-12 inline-block rounded-full border-4 border-dashed border-gray-400">&nbsp;</div>
                <div>
                    <a href="{{ route("players.show", $feed->player_id) }}" class="font-bold">
                        {{ $feed->user->name }} - {{ $feed->date_sent->format('M d, Y') }}: {{ $feed->feeMaterials->pluck('name')->join(", ")  }}
                    </a>
                    <div>{{ $feed->comment }}</div>
                </div>
            </div>
        </div>
    @endforeach
    </main>
</div>

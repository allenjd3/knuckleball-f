<div class="max-w-5xl mx-auto py-12 px-4">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl">Discover Packs</h1>
            <p class="text-sm text-gray-500 mt-1">Public collections shared by other collectors</p>
        </div>
        @auth
            <a href="{{ route('packs.index') }}" class="text-sm underline hover:text-gray-700">My Packs</a>
        @endauth
    </div>

    @if ($this->categories->isNotEmpty())
        <div class="flex flex-wrap gap-2 mb-8">
            <button
                wire:click="$set('category', null)"
                class="px-3 py-1 rounded-full text-sm border transition-colors {{ is_null($category) ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-300 hover:border-gray-500' }}"
            >All</button>
            @foreach ($this->categories as $cat)
                <button
                    wire:click="$set('category', {{ $cat->id }})"
                    class="px-3 py-1 rounded-full text-sm border transition-colors {{ $category === $cat->id ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-300 hover:border-gray-500' }}"
                >{{ $cat->name }}</button>
            @endforeach
        </div>
    @endif

    @if ($this->packs->isEmpty())
        <div class="text-center py-20 text-gray-400">
            <x-heroicon-o-rectangle-stack class="size-12 mx-auto mb-3 text-gray-300" />
            <p>No public packs yet.</p>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($this->packs as $pack)
                <div>
                    <a href="{{ $pack->path() }}" class="block aspect-[2/3] rounded-lg overflow-hidden bg-gray-100 hover:opacity-90 transition-opacity">
                        @if ($pack->cover_image)
                            <img src="{{ Storage::url($pack->cover_image) }}" alt="{{ $pack->name }}" class="w-full h-full object-cover" />
                        @else
                            <div class="flex flex-col items-center justify-center h-full text-gray-400 p-4 text-center gap-2">
                                <x-heroicon-o-rectangle-stack class="size-10" />
                                <span class="text-sm font-medium leading-tight">{{ $pack->name }}</span>
                            </div>
                        @endif
                    </a>
                    <div class="mt-2">
                        <a href="{{ $pack->path() }}" class="font-medium text-sm block truncate hover:underline">{{ $pack->name }}</a>
                        <p class="text-xs text-gray-500 mt-0.5">
                            by {{ $pack->user->name }}
                            @if ($pack->category) · {{ $pack->category->name }} @endif
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $pack->players_count }} {{ Str::plural('player', $pack->players_count) }}
                            · {{ $pack->followers_count }} {{ Str::plural('follower', $pack->followers_count) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

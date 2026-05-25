<div class="max-w-5xl mx-auto py-12 px-4">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl">Discover Sets</h1>
            <p class="text-sm text-gray-500 mt-1">Signed set completion projects shared by collectors</p>
        </div>
        @auth
            <a href="{{ route('sets.index') }}" class="text-sm underline hover:text-gray-700">My Sets</a>
        @endauth
    </div>

    <div class="flex items-center gap-2 mb-8">
        <button wire:click="$set('sort', 'recent')"
                class="px-3 py-1 rounded-full text-sm border transition-colors {{ $sort === 'recent' ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-300 hover:border-gray-500' }}">
            Recent
        </button>
        <button wire:click="$set('sort', 'complete')"
                class="px-3 py-1 rounded-full text-sm border transition-colors {{ $sort === 'complete' ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-300 hover:border-gray-500' }}">
            Most Complete
        </button>
        <button wire:click="$set('sort', 'followed')"
                class="px-3 py-1 rounded-full text-sm border transition-colors {{ $sort === 'followed' ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-300 hover:border-gray-500' }}">
            Most Followed
        </button>
    </div>

    @if ($this->sets->total() === 0)
        <div class="text-center py-20 text-gray-400">
            <x-heroicon-o-squares-2x2 class="size-12 mx-auto mb-3 text-gray-300" />
            <p>No public sets yet.</p>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($this->sets as $set)
                @php $pct = $set->completionPercentage(); @endphp
                <div>
                    <a href="{{ $set->path() }}" class="block relative aspect-[2/3] rounded-lg overflow-hidden bg-gray-100 hover:opacity-90 transition-opacity">
                        @if ($set->cover_image)
                            <img src="{{ Storage::url($set->cover_image) }}" alt="{{ $set->name }}" class="w-full h-full object-cover" />
                        @else
                            <div class="flex flex-col items-center justify-center h-full text-gray-400 p-4 text-center gap-2">
                                <x-heroicon-o-squares-2x2 class="size-10" />
                                <span class="text-sm font-medium leading-tight">{{ $set->name }}</span>
                            </div>
                        @endif

                        @if ($pct > 0)
                            <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-black/20">
                                <div class="h-full {{ $pct === 100 ? 'bg-amber-400' : 'bg-[#D93C3F]' }}" style="width: {{ $pct }}%"></div>
                            </div>
                        @endif
                    </a>
                    <div class="mt-2">
                        <a href="{{ $set->path() }}" class="font-medium text-sm block truncate hover:underline">{{ $set->name }}</a>
                        <p class="text-xs text-gray-500 mt-0.5">
                            by {{ $set->user->name }}
                            @if ($set->year) · {{ $set->year }} @endif
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $set->entries_count }} {{ Str::plural('card', $set->entries_count) }}
                            · {{ $set->followers_count }} {{ Str::plural('follower', $set->followers_count) }}
                            @if ($pct > 0) · {{ $pct }}% @endif
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-8">{{ $this->sets->links() }}</div>
    @endif
</div>

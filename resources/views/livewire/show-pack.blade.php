<div class="max-w-5xl mx-auto py-12 px-4">
    <div class="flex flex-col sm:flex-row gap-6 mb-8">
        <div class="shrink-0 w-36 sm:w-44">
            <div class="aspect-[2/3] rounded-lg overflow-hidden bg-gray-100">
                @if ($pack->cover_image)
                    <img src="{{ Storage::url($pack->cover_image) }}" alt="{{ $pack->name }}" class="w-full h-full object-cover" />
                @else
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 p-4 text-center gap-2">
                        <x-heroicon-o-rectangle-stack class="size-8" />
                        <span class="text-xs">No cover</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <h1 class="text-3xl">{{ $pack->name }}</h1>
                        <span class="text-xs border border-gray-300 rounded-full px-2 py-0.5 text-gray-500 shrink-0">
                            {{ $pack->is_public ? 'Public' : 'Private' }}
                        </span>
                    </div>

                    @if ($pack->description)
                        <p class="text-gray-600 mt-2">{{ $pack->description }}</p>
                    @endif

                    <p class="text-sm text-gray-400 mt-3">
                        By <a href="{{ route('users.profile', $pack->user) }}" class="underline hover:text-gray-700">{{ $pack->user->name }}</a>
                        @if ($pack->category) · {{ $pack->category->name }} @endif
                        · {{ $pack->players()->count() }} {{ Str::plural('player', $pack->players()->count()) }}
                        · {{ $pack->followers()->count() }} {{ Str::plural('follower', $pack->followers()->count()) }}
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    {{ $this->followAction }}
                    @if ($this->isOwner)
                        <a href="{{ route('packs.index') }}" class="text-sm underline text-gray-500 hover:text-gray-700">My Packs</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{ $this->table }}

    <x-filament-actions::modals />
</div>

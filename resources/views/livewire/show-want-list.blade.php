<div class="max-w-5xl mx-auto py-12 px-4">
    <div class="mb-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h1 class="text-3xl">{{ $wantList->name }}</h1>
                    <span class="text-xs border border-gray-300 rounded-full px-2 py-0.5 text-gray-500">
                        {{ $wantList->is_public ? 'Public' : 'Private' }}
                    </span>
                </div>

                @if ($wantList->description)
                    <p class="text-gray-600 mt-1">{{ $wantList->description }}</p>
                @endif

                <p class="text-sm text-gray-400 mt-2">
                    By <a href="{{ route('users.profile', $wantList->user) }}" class="underline hover:text-gray-700">{{ $wantList->user->name }}</a>
                    @if ($wantList->category)
                        · {{ $wantList->category->name }}
                    @endif
                    · {{ $wantList->followers()->count() }} {{ Str::plural('follower', $wantList->followers()->count()) }}
                </p>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                {{ $this->followAction }}
                @auth
                    @can('update', $wantList)
                        <a href="{{ route('wantLists.index') }}" class="text-sm underline text-gray-500 hover:text-gray-700">My Lists</a>
                    @endcan
                @endauth
            </div>
        </div>
    </div>

    {{ $this->table }}

    <x-filament-actions::modals />
</div>

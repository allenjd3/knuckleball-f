<div class="max-w-5xl mx-auto pb-16">

    {{-- Cover + Avatar --}}
    <div class="relative">
        <div class="h-48 md:h-56 bg-gray-200 overflow-hidden">
            @if ($this->user->cover_photo)
                <img src="{{ Storage::url($this->user->cover_photo) }}" class="w-full h-full object-cover" alt="Cover" />
            @else
                <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300"></div>
            @endif
        </div>
        <div class="absolute bottom-0 left-6 translate-y-1/2">
            <div class="size-24 rounded-full ring-4 ring-white overflow-hidden bg-gray-100">
                <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}" class="w-full h-full object-cover" />
            </div>
        </div>
    </div>

    {{-- Profile info --}}
    <div class="pt-16 pb-4 px-6 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">{{ $this->user->name }}</h1>
            <p class="text-gray-500 text-sm">{{ '@' . $this->user->handle }} · Joined {{ $this->user->created_at?->format('M Y') }}</p>
            @if ($this->user->bio)
                <p class="mt-2 text-gray-700 text-sm max-w-md">{{ $this->user->bio }}</p>
            @endif
        </div>
        <div class="flex items-center gap-2 shrink-0 mt-1">
            @if ($this->isOwner)
                {{ $this->editProfile }}
                {{ $this->changeHandle }}
            @elseif (auth()->check())
                @if ($this->isFollowing)
                    <button wire:click="unfollow" class="px-4 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Unfollow</button>
                @else
                    <button wire:click="follow" class="px-4 py-1.5 text-sm bg-gray-900 text-white rounded-lg hover:bg-gray-700">Follow</button>
                @endif
            @endif
        </div>
    </div>

    <div class="px-4 space-y-8 mt-4">

        {{-- Stats card --}}
        <div class="rounded-2xl p-8 md:p-10 text-white" style="background-color:#D93C3F; font-family:'Inter',sans-serif;">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-10">
                <p class="text-xs font-bold uppercase tracking-widest">All-Time Stats</p>
                <a href="{{ route('users.snapshot', [$this->user, now()->year]) }}"
                   class="bg-white rounded-full px-5 py-2 text-xs font-black uppercase tracking-widest hover:bg-white/90 transition-colors"
                   style="color:#D93C3F;">
                    Share Snapshot ↗
                </a>
            </div>

            {{-- 4 headline numbers --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10">
                <div>
                    <p class="text-8xl font-black leading-none tabular-nums">{{ $this->stats['total_sends'] }}</p>
                    <p class="text-xs font-bold uppercase tracking-widest mt-3 text-white/70">Sends</p>
                </div>
                <div>
                    <p class="text-8xl font-black leading-none tabular-nums">{{ $this->stats['total_returns'] }}</p>
                    <p class="text-xs font-bold uppercase tracking-widest mt-3 text-white/70">Returns</p>
                </div>
                <div>
                    <p class="text-8xl font-black leading-none tabular-nums">{{ $this->stats['success_rate'] }}<span class="text-4xl font-black">%</span></p>
                    <p class="text-xs font-bold uppercase tracking-widest mt-3 text-white/70">Success</p>
                </div>
                <div>
                    <p class="text-8xl font-black leading-none tabular-nums">{{ $this->stats['unique_players'] }}</p>
                    <p class="text-xs font-bold uppercase tracking-widest mt-3 text-white/70">Players</p>
                </div>
            </div>

            {{-- Divider --}}
            <div class="border-t border-white/20 mb-6"></div>

            {{-- Detail rows --}}
            <div class="space-y-4">
                @if ($this->stats['fastest_return'])
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 text-sm font-medium">
                            <x-heroicon-o-bolt class="size-5 text-white/70 shrink-0" />
                            Fastest Return
                        </div>
                        <span class="text-sm font-semibold">{{ $this->stats['fastest_return']['player_name'] }} · {{ $this->stats['fastest_return']['days'] }} days</span>
                    </div>
                @endif
                @if ($this->stats['longest_wait'])
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 text-sm font-medium">
                            <x-heroicon-o-clock class="size-5 text-white/70 shrink-0" />
                            Longest Wait
                        </div>
                        <span class="text-sm font-semibold">{{ $this->stats['longest_wait']['player_name'] }} · {{ $this->stats['longest_wait']['days'] }} days</span>
                    </div>
                @endif
                @if ($this->stats['favorite_team'])
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 text-sm font-medium">
                            <x-heroicon-o-trophy class="size-5 text-white/70 shrink-0" />
                            Favorite Team
                        </div>
                        <span class="text-sm font-semibold">{{ $this->stats['favorite_team'] }}</span>
                    </div>
                @endif
                @if ($this->stats['favorite_category'])
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 text-sm font-medium">
                            <x-heroicon-o-tag class="size-5 text-white/70 shrink-0" />
                            Top Category
                        </div>
                        <span class="text-sm font-semibold">{{ $this->stats['favorite_category'] }}</span>
                    </div>
                @endif
                @if ($this->stats['most_reacted_return'])
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 text-sm font-medium">
                            <x-heroicon-s-heart class="size-5 text-white/70 shrink-0" />
                            Most Reacted
                        </div>
                        <span class="text-sm font-semibold">{{ $this->stats['most_reacted_return']['player_name'] }} · {{ $this->stats['most_reacted_return']['reaction_count'] }} reactions</span>
                    </div>
                @endif
                @if ($this->stats['first_send'])
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 text-sm font-medium">
                            <x-heroicon-o-paper-airplane class="size-5 text-white/70 shrink-0" />
                            First Send
                        </div>
                        <span class="text-sm font-semibold">{{ $this->stats['first_send']->format('M j, Y') }}</span>
                    </div>
                @endif
                @if ($this->stats['most_recent_return'])
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 text-sm font-medium">
                            <x-heroicon-o-inbox-arrow-down class="size-5 text-white/70 shrink-0" />
                            Latest Return
                        </div>
                        <span class="text-sm font-semibold">{{ $this->stats['most_recent_return']->format('M j, Y') }}</span>
                    </div>
                @endif
                @if ($this->stats['total_sends'] === 0)
                    <p class="text-white/50 text-sm text-center py-2">No activity yet — send some mail to see stats here.</p>
                @endif
            </div>
        </div>

        {{-- Past Snapshots --}}
        @if ($this->snapshots->isNotEmpty())
            <div>
                <h2 class="text-sm uppercase tracking-widest text-gray-500 font-medium mb-3">Snapshots</h2>
                <div class="flex gap-3 overflow-x-auto pb-2">
                    @foreach ($this->snapshots as $snapshot)
                        <a href="{{ route('users.snapshot', [$this->user, $snapshot->year]) }}"
                           class="shrink-0 bg-gray-950 text-white rounded-xl p-4 w-36 hover:opacity-80 transition-opacity">
                            <p class="text-3xl font-black">{{ $snapshot->year }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ data_get($snapshot->data, 'total_sends', 0) }} sends</p>
                            <p class="text-xs text-gray-400">{{ data_get($snapshot->data, 'success_rate', 0) }}% success</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    {{-- Tabs --}}
    <div x-data="{ tab: 'activity' }" class="mt-6">

        {{-- Tab nav --}}
        <div class="sticky top-16 z-10 bg-white border-b border-gray-200 px-4">
            <div class="flex gap-6">
                <button
                    @click="tab = 'activity'"
                    :class="tab === 'activity'
                        ? 'border-b-2 border-[#CB504B] text-[#CB504B] font-semibold'
                        : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600'"
                    class="py-3 text-sm transition-colors -mb-px">
                    Activity
                </button>
                <button
                    @click="tab = 'packs'"
                    :class="tab === 'packs'
                        ? 'border-b-2 border-[#CB504B] text-[#CB504B] font-semibold'
                        : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600'"
                    class="py-3 text-sm transition-colors -mb-px">
                    Packs
                </button>
                <button
                    @click="tab = 'sets'"
                    :class="tab === 'sets'
                        ? 'border-b-2 border-[#CB504B] text-[#CB504B] font-semibold'
                        : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600'"
                    class="py-3 text-sm transition-colors -mb-px">
                    Sets
                </button>
                @if ($this->isOwner)
                <button
                    @click="tab = 'watchlist'"
                    :class="tab === 'watchlist'
                        ? 'border-b-2 border-[#CB504B] text-[#CB504B] font-semibold'
                        : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600'"
                    class="py-3 text-sm transition-colors -mb-px">
                    Watchlist
                </button>
                @endif
            </div>
        </div>

        {{-- Activity tab --}}
        <div x-show="tab === 'activity'" x-cloak class="px-4 pt-6 pb-16 flex flex-col gap-3">
            @forelse ($this->feeds as $feed)
                <x-dynamic-component :component="$feed->componentName()" :$feed />
            @empty
                <p class="text-gray-400 text-sm text-center py-8">No activity yet.</p>
            @endforelse
            {{ $this->feeds->links() }}
        </div>

        {{-- Packs tab --}}
        <div x-show="tab === 'packs'" x-cloak class="px-4 pt-6 pb-16">
            @if ($this->packs->isNotEmpty())
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm uppercase tracking-widest text-gray-500 font-medium">{{ $this->packs->count() }} {{ Str::plural('Pack', $this->packs->count()) }}</p>
                    @if ($this->isOwner)
                        <a href="{{ route('packs.index') }}" class="text-sm underline text-gray-500 hover:text-gray-700">Manage</a>
                    @endif
                </div>
                <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                    @foreach ($this->packs as $pack)
                        <a href="{{ $pack->path() }}" class="group block">
                            <div class="aspect-[2/3] rounded-lg overflow-hidden bg-gray-100 group-hover:opacity-90 transition-opacity">
                                @if ($pack->cover_image)
                                    <img src="{{ Storage::url($pack->cover_image) }}" alt="{{ $pack->name }}" class="w-full h-full object-cover" />
                                @else
                                    <div class="flex flex-col items-center justify-center h-full text-gray-400 p-2 text-center gap-1">
                                        <x-heroicon-o-rectangle-stack class="size-6" />
                                        <span class="text-xs leading-tight">{{ $pack->name }}</span>
                                    </div>
                                @endif
                            </div>
                            <p class="text-xs font-medium truncate mt-1.5">{{ $pack->name }}</p>
                            <p class="text-xs text-gray-400">{{ $pack->players_count }} {{ Str::plural('player', $pack->players_count) }}</p>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400 text-sm text-center py-8">No packs yet.</p>
            @endif
        </div>

        {{-- Sets tab --}}
        <div x-show="tab === 'sets'" x-cloak class="px-4 pt-6 pb-16">
            @if ($this->sets->isNotEmpty())
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm uppercase tracking-widest text-gray-500 font-medium">{{ $this->sets->count() }} {{ Str::plural('Set', $this->sets->count()) }}</p>
                    @if ($this->isOwner)
                        <div class="flex items-center gap-3">
                            <a href="{{ route('sets.index') }}"
                               class="flex items-center gap-1.5 text-sm font-medium bg-gray-900 text-white px-3 py-1.5 rounded-lg hover:bg-gray-700 transition-colors">
                                <x-heroicon-o-plus class="size-3.5" /> New Set
                            </a>
                            <a href="{{ route('sets.index') }}" class="text-sm underline text-gray-500 hover:text-gray-700">Manage</a>
                        </div>
                    @endif
                </div>
                <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                    @foreach ($this->sets as $set)
                        @php $pct = $set->completionPercentage(); @endphp
                        <a href="{{ $set->path() }}" class="group block">
                            <div class="aspect-[2/3] rounded-lg overflow-hidden bg-gray-100 group-hover:opacity-90 transition-opacity relative">
                                @if ($set->cover_image)
                                    <img src="{{ Storage::url($set->cover_image) }}" alt="{{ $set->name }}" class="w-full h-full object-cover" />
                                @else
                                    <div class="flex flex-col items-center justify-center h-full text-gray-400 p-2 text-center gap-1">
                                        <x-heroicon-o-squares-2x2 class="size-6" />
                                        <span class="text-xs leading-tight">{{ $set->name }}</span>
                                    </div>
                                @endif
                                @if ($pct > 0)
                                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-black/20">
                                        <div class="h-full {{ $pct === 100 ? 'bg-amber-400' : 'bg-[#D93C3F]' }}" style="width: {{ $pct }}%"></div>
                                    </div>
                                @endif
                            </div>
                            <p class="text-xs font-medium truncate mt-1.5">{{ $set->name }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $set->entries_count }} {{ Str::plural('card', $set->entries_count) }}
                                @if ($pct > 0) · {{ $pct }}% @endif
                            </p>
                        </a>
                    @endforeach
                </div>
            @else
                @if ($this->isOwner)
                    <div class="text-center py-12">
                        <x-heroicon-o-squares-2x2 class="size-10 mx-auto mb-3 text-gray-300" />
                        <p class="text-gray-500 text-sm mb-4">Track your signed set completion progress.</p>
                        <a href="{{ route('sets.index') }}"
                           class="inline-flex items-center gap-2 bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                            <x-heroicon-o-plus class="size-4" /> Create your first set
                        </a>
                    </div>
                @else
                    <p class="text-gray-400 text-sm text-center py-8">No sets yet.</p>
                @endif
            @endif
        </div>

        {{-- Watchlist tab (owner only) --}}
        @if ($this->isOwner)
        <div x-show="tab === 'watchlist'" x-cloak class="px-4 pt-6 pb-16">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm uppercase tracking-widest text-gray-500 font-medium">
                    {{ $this->watchlist->count() }} {{ Str::plural('Player', $this->watchlist->count()) }}
                </p>
                <a href="{{ route('watchlist.index') }}" class="text-sm underline text-gray-500 hover:text-gray-700">Manage</a>
            </div>

            @if ($this->watchlist->isEmpty())
                <div class="text-center py-12">
                    <x-heroicon-o-eye class="size-10 mx-auto mb-3 text-gray-200" />
                    <p class="text-gray-500 text-sm mb-1">No players on your watchlist yet.</p>
                    <p class="text-xs text-gray-400">Add players from their profile page to get signing alerts.</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($this->watchlist as $player)
                        <div class="flex items-center gap-3 bg-white border border-gray-100 rounded-xl p-3">
                            <div class="size-9 rounded-full overflow-hidden bg-gray-100 shrink-0">
                                @if ($player->media?->url)
                                    <img src="{{ Storage::url($player->media->url) }}" class="w-full h-full object-cover" />
                                @else
                                    <x-heroicon-o-user class="size-4 text-gray-300 m-2.5" />
                                @endif
                            </div>
                            <a href="{{ $player->path() }}" class="flex-1 font-medium text-sm text-gray-900 hover:underline">
                                {{ $player->name }}
                            </a>
                            <button wire:click="removeFromWatchlist({{ $player->id }})"
                                    class="text-xs text-gray-400 hover:text-red-500 transition-colors shrink-0">
                                Remove
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Location alert settings callout --}}
            <div class="mt-6 bg-blue-50 border border-blue-100 rounded-2xl p-4">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-map-pin class="size-5 text-blue-500 shrink-0 mt-0.5" />
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-blue-800">Get alerts for events near you</p>
                        @if ($this->user->zip_code)
                            <p class="text-xs text-blue-600 mt-0.5">
                                Alerts set for within <strong>{{ $this->user->radius ?? 50 }} miles</strong> of
                                <strong>{{ $this->user->zip_code }}</strong>.
                                @if ($this->user->card_show_alerts) Card show alerts on. @endif
                            </p>
                        @else
                            <p class="text-xs text-blue-600 mt-0.5">Add your zip code in Edit Profile to get notified about signings and shows near you.</p>
                        @endif
                        <button wire:click="mountAction('editProfile')"
                                class="mt-2 text-xs font-semibold text-blue-700 hover:underline">
                            {{ $this->user->zip_code ? 'Update location →' : 'Set location →' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>

    <x-filament-actions::modals />
</div>

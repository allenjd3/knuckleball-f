<div
    x-data="{ open: false }"
    x-on:open-search.window="open = true; $nextTick(() => $refs.searchInput.focus())"
    x-on:keydown.escape.window="open = false"
>
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[9999] flex items-start justify-center bg-black/50 px-4 pt-24"
        x-trap.inert.noscroll="open"
        @click.self="open = false"
    >
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="relative border-b border-gray-100">
                <x-heroicon-o-magnifying-glass class="absolute left-4 top-1/2 -translate-y-1/2 size-5 text-gray-400 pointer-events-none" />
                <input
                    x-ref="searchInput"
                    type="text"
                    wire:model.live.debounce.300ms="query"
                    placeholder="Search players, teams..."
                    class="w-full pl-12 pr-12 py-4 text-base border-0 focus:outline-hidden focus:ring-0"
                />
                <button
                    type="button"
                    @click="open = false"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    aria-label="Close search"
                >
                    <x-heroicon-o-x-mark class="size-5" />
                </button>
            </div>

            @if (strlen(trim($query)) < 2)
                <div class="px-4 py-10 text-center text-gray-400 text-sm">
                    Keep typing to search.
                </div>
            @elseif ($this->players->isEmpty() && $this->teams->isEmpty())
                <div class="px-4 py-10 text-center text-gray-400 text-sm">
                    No results found.
                </div>
            @else
                <div class="max-h-[28rem] overflow-y-auto">
                    @if ($this->players->isNotEmpty())
                        <p class="px-4 pt-3 pb-1 text-[11px] font-bold uppercase tracking-widest text-gray-400">Players</p>
                        <div class="divide-y divide-gray-50">
                            @foreach ($this->players as $player)
                                <a href="{{ $player->path() }}" @click="open = false" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition-colors">
                                    <div class="size-8 rounded-full overflow-hidden bg-gray-100 shrink-0 flex items-center justify-center">
                                        @auth
                                            @if ($player->media?->url)
                                                <img src="{{ Storage::url($player->media->url) }}" alt="{{ $player->name }}" class="w-full h-full object-cover" />
                                            @else
                                                <x-heroicon-o-user class="size-4 text-gray-300" />
                                            @endif
                                        @else
                                            <x-heroicon-o-user class="size-4 text-gray-300" />
                                        @endauth
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $player->name }}</p>
                                        @if ($player->team)
                                            <p class="text-xs text-gray-500 truncate">{{ $player->team->name }}</p>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ route('players.index', ['search' => $query]) }}"
                           class="block text-center px-4 py-2 text-xs font-semibold text-[#D93C3F] hover:bg-gray-50 border-t border-gray-100">
                            See all players matching "{{ $query }}"
                        </a>
                    @endif

                    @if ($this->teams->isNotEmpty())
                        <p class="px-4 pt-3 pb-1 text-[11px] font-bold uppercase tracking-widest text-gray-400 border-t border-gray-100">Teams</p>
                        <div class="divide-y divide-gray-50">
                            @foreach ($this->teams as $team)
                                <a href="{{ route('teams.show', $team) }}" @click="open = false" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition-colors">
                                    <div class="size-8 rounded-full overflow-hidden bg-gray-100 shrink-0 flex items-center justify-center">
                                        @if ($team->media?->url)
                                            <img src="{{ Storage::url($team->media->url) }}" alt="{{ $team->name }}" class="w-full h-full object-cover" />
                                        @else
                                            <x-heroicon-o-shield-check class="size-4 text-gray-300" />
                                        @endif
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $team->name }}</p>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ route('teams.index', ['search' => $query]) }}"
                           class="block text-center px-4 py-2 text-xs font-semibold text-[#D93C3F] hover:bg-gray-50 border-t border-gray-100">
                            See all teams matching "{{ $query }}"
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

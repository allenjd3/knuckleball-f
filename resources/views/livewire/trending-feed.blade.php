<div class="min-h-screen bg-white">
    <div class="max-w-5xl mx-auto px-4 py-8">
        <div class="flex gap-8 items-start">

            {{-- Sidebar (same as feed) --}}
            <aside class="hidden md:flex flex-col gap-6 w-52 shrink-0 sticky top-20">
                @auth
                    <div class="text-center">
                        <img src="{{ $this->authUser->profile_photo_url }}" alt="{{ $this->authUser->name }}"
                             class="size-20 rounded-full mx-auto mb-3 ring-2 ring-gray-100" />
                        <p class="font-bold text-gray-900">{{ $this->authUser->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Joined {{ $this->authUser->created_at->format('M j, Y') }}</p>
                        <div class="flex justify-center gap-6 mt-3 text-sm">
                            <div>
                                <p class="font-bold text-gray-900">{{ $this->authUser->following_count }}</p>
                                <p class="text-xs text-gray-400">Following</p>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">{{ $this->authUser->followers_count }}</p>
                                <p class="text-xs text-gray-400">Followers</p>
                            </div>
                        </div>
                    </div>
                    <nav class="space-y-0.5">
                        <a href="{{ route('users.feed') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-800 transition-colors">
                            <x-heroicon-o-inbox class="size-5 shrink-0" /> Feed
                        </a>
                        <a href="{{ route('users.trending') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium bg-red-50 text-[#D93C3F] transition-colors">
                            <x-heroicon-o-fire class="size-5 shrink-0" /> Trending
                        </a>
                        <a href="{{ route('players.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-800 transition-colors">
                            <x-heroicon-o-heart class="size-5 shrink-0" /> Players
                        </a>
                        <a href="{{ route('users.profile', auth()->user()) }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-800 transition-colors">
                            <x-heroicon-o-camera class="size-5 shrink-0" /> My Profile
                        </a>
                    </nav>
                @endauth
            </aside>

            {{-- Main --}}
            <main class="flex-1 min-w-0 space-y-6">

                {{-- Trending Returns --}}
                <div>
                    <div class="flex items-center justify-between mb-3 px-1">
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-400">🔥 Trending Returns <span class="normal-case font-normal text-gray-400 tracking-normal">· Last 30 days</span></p>
                        <div class="flex gap-2">
                            @foreach (['reactions' => 'Most Reacted', 'fastest' => 'Fastest', 'comments' => 'Most Comments'] as $key => $label)
                                <button wire:click="setSort('{{ $key }}')"
                                        class="text-xs px-3 py-1.5 rounded-full border transition-colors
                                            {{ $sort === $key
                                                ? 'border-[#D93C3F] text-[#D93C3F] font-semibold'
                                                : 'border-gray-200 text-gray-500 hover:border-gray-300' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-3">
                        @forelse ($this->trendingReturns as $feed)
                            <x-feeds.celebration-card :$feed />
                        @empty
                            <div class="text-center py-12 text-gray-400">
                                <p class="text-sm">No trending returns yet.</p>
                            </div>
                        @endforelse
                    </div>

                    @if ($hasMore)
                        <div
                            x-data
                            x-init="
                                const obs = new IntersectionObserver((entries) => {
                                    if (entries[0].isIntersecting) { obs.disconnect(); $wire.loadMore(); }
                                }, { rootMargin: '200px' });
                                obs.observe($el);
                            "
                            class="flex justify-center py-6"
                        >
                            <div class="size-5 rounded-full border-2 border-[#D93C3F] border-t-transparent animate-spin opacity-60"></div>
                        </div>
                    @endif
                </div>

                {{-- Trending Players --}}
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-3 px-1">📈 Trending Players <span class="normal-case font-normal tracking-normal">· Last 30 days</span></p>
                    <div class="bg-white rounded-2xl border border-gray-100 divide-y divide-gray-50">
                        @forelse ($this->trendingPlayers as $i => $player)
                            <div class="flex items-center gap-4 px-4 py-3">
                                <span class="text-sm font-bold text-gray-300 w-5 text-center">{{ $i + 1 }}</span>
                                @if ($player->media)
                                    <img src="{{ Storage::url($player->media->url) }}" alt="{{ $player->name }}"
                                         class="size-10 rounded-full object-cover flex-shrink-0 bg-gray-100" />
                                @else
                                    <div class="size-10 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-o-user class="size-5 text-gray-300" />
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <a href="{{ $player->path() }}" class="font-semibold text-sm hover:underline">{{ $player->name }}</a>
                                    @if ($player->team?->category)
                                        <p class="text-xs text-gray-400">{{ $player->team->category->name }}</p>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-400 shrink-0">{{ $player->send_count }} sends</span>
                                <a href="{{ $player->path() }}"
                                   class="text-xs px-3 py-1.5 rounded-full border border-gray-900 font-semibold hover:bg-gray-900 hover:text-white transition-colors shrink-0">
                                    Add to Pack
                                </a>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400 text-sm">No trending players yet.</div>
                        @endforelse
                    </div>
                </div>

            </main>
        </div>
    </div>
</div>

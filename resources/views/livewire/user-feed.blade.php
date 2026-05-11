<div class="min-h-screen bg-white">
    <div class="max-w-5xl mx-auto px-4 py-8">
        <div class="flex gap-8 items-start">

            {{-- Left Sidebar --}}
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
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('users.feed') ? 'bg-red-50 text-[#D93C3F]' : 'text-gray-500 hover:text-gray-800' }}">
                            <x-heroicon-o-inbox class="size-5 shrink-0" /> Feed
                        </a>
                        <a href="{{ route('users.trending') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('users.trending') ? 'bg-red-50 text-[#D93C3F]' : 'text-gray-500 hover:text-gray-800' }}">
                            <x-heroicon-o-fire class="size-5 shrink-0" /> Trending
                        </a>
                        <a href="{{ route('players.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('players.*') ? 'bg-red-50 text-[#D93C3F]' : 'text-gray-500 hover:text-gray-800' }}">
                            <x-heroicon-o-heart class="size-5 shrink-0" /> Players
                        </a>
                        @auth
                        <a href="{{ route('users.profile', auth()->user()) }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors text-gray-500 hover:text-gray-800">
                            <x-heroicon-o-camera class="size-5 shrink-0" /> My Profile
                        </a>
                        @endauth
                    </nav>
                @endauth
            </aside>

            {{-- Main content --}}
            <main class="flex-1 min-w-0 space-y-4">

                {{-- Compose --}}
                @auth
                    <livewire:add-comment />
                @endauth

                {{-- Trending Returns Strip --}}
                @if ($this->trendingStrip->isNotEmpty())
                    <div class="bg-white rounded-2xl border border-gray-100 p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span>🔥</span>
                            <span class="text-xs font-bold uppercase tracking-widest text-gray-500">Trending Returns</span>
                        </div>
                        <div class="flex gap-2.5 overflow-x-auto pb-1 -mx-1 px-1">
                            @foreach ($this->trendingStrip as $item)
                                @php
                                    $heroPhoto = data_get($item->meta, 'card_photos.0');
                                    $days      = data_get($item->meta, 'turnaround_days');
                                @endphp
                                <a href="{{ data_get($item->meta, 'player_path', '#') }}"
                                   class="shrink-0 bg-white border border-gray-100 rounded-xl p-3 w-44 hover:border-gray-200 transition-colors block">
                                    @if ($heroPhoto)
                                        <img src="{{ Storage::url($heroPhoto) }}"
                                             class="w-full h-20 object-cover rounded-lg mb-2" />
                                    @else
                                        <div class="w-full h-20 bg-gray-50 rounded-lg mb-2 flex items-center justify-center">
                                            <x-heroicon-o-photo class="size-6 text-gray-200" />
                                        </div>
                                    @endif
                                    <p class="font-semibold text-gray-900 text-xs leading-tight truncate">{{ data_get($item->meta, 'player') }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5 truncate">{{ data_get($item->meta, 'user') }}</p>
                                    @if ($days !== null)
                                        <p class="text-xs font-bold text-[#D93C3F] mt-1">{{ $days }} Days</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Activity header + filter --}}
                <div class="flex items-center justify-between px-1 pt-2">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Activity</p>
                    @auth
                    <div class="flex items-center gap-1 text-sm">
                        <button wire:click="setFilter('following')"
                                class="px-3 py-1 font-medium transition-colors rounded {{ $filter === 'following' ? 'text-gray-900 border-b-2 border-[#D93C3F]' : 'text-gray-400 hover:text-gray-600' }}">
                            Following
                        </button>
                        <button wire:click="setFilter('global')"
                                class="px-3 py-1 font-medium transition-colors rounded {{ $filter === 'global' ? 'text-gray-900 border-b-2 border-[#D93C3F]' : 'text-gray-400 hover:text-gray-600' }}">
                            Global
                        </button>
                    </div>
                    @endauth
                </div>

                {{-- Feed items --}}
                <div class="space-y-2">
                    @forelse ($this->feeds as $feed)
                        <x-dynamic-component :component="$feed->componentName()" :$feed />
                    @empty
                        <div class="text-center py-12 text-gray-400">
                            <x-heroicon-o-inbox class="size-10 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">No activity yet.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Infinite scroll sentinel --}}
                @if ($hasMore)
                    <div
                        x-data
                        x-init="
                            const obs = new IntersectionObserver((entries) => {
                                if (entries[0].isIntersecting) {
                                    obs.disconnect();
                                    $wire.loadMore();
                                }
                            }, { rootMargin: '200px' });
                            obs.observe($el);
                        "
                        class="flex justify-center py-6"
                    >
                        <div class="size-5 rounded-full border-2 border-[#D93C3F] border-t-transparent animate-spin opacity-60"></div>
                    </div>
                @else
                    <p class="text-center text-gray-400 text-sm py-8">You're all caught up.</p>
                @endif

            </main>
        </div>
    </div>
</div>

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
                        <a href="{{ route('leaderboard.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('leaderboard.index') ? 'bg-red-50 text-[#D93C3F]' : 'text-gray-500 hover:text-gray-800' }}">
                            <x-heroicon-o-trophy class="size-5 shrink-0" /> Leaderboard
                        </a>
                        <a href="{{ route('packs.browse') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('packs.*') ? 'bg-red-50 text-[#D93C3F]' : 'text-gray-500 hover:text-gray-800' }}">
                            <x-heroicon-o-bookmark class="size-5 shrink-0" /> Packs
                        </a>
                        <a href="{{ route('sets.browse') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('sets.*') ? 'bg-red-50 text-[#D93C3F]' : 'text-gray-500 hover:text-gray-800' }}">
                            <x-heroicon-o-squares-2x2 class="size-5 shrink-0" /> Sets
                        </a>
                        <a href="{{ route('users.profile', auth()->user()) }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors text-gray-500 hover:text-gray-800">
                            <x-heroicon-o-user-circle class="size-5 shrink-0" /> My Profile
                        </a>
                    </nav>
                @else
                    <div class="text-center">
                        <p class="text-sm font-medium text-gray-700">Sign in to personalize your feed</p>
                        <a href="{{ route('login') }}" class="mt-3 inline-block text-sm font-semibold text-gray-900 underline underline-offset-2">
                            Log in
                        </a>
                    </div>
                @endauth
            </aside>

            {{-- Main content --}}
            <main class="flex-1 min-w-0">

                {{-- Mobile nav --}}
                <div class="md:hidden mb-4">
                    <nav class="flex gap-1 overflow-x-auto pb-1 -mx-1 px-1">
                        <a href="{{ route('users.feed') }}"
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors shrink-0 {{ request()->routeIs('users.feed') ? 'bg-red-50 text-[#D93C3F]' : 'text-gray-500 hover:text-gray-800' }}">
                            <x-heroicon-o-inbox class="size-4 shrink-0" /> Feed
                        </a>
                        <a href="{{ route('users.trending') }}"
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors shrink-0 {{ request()->routeIs('users.trending') ? 'bg-red-50 text-[#D93C3F]' : 'text-gray-500 hover:text-gray-800' }}">
                            <x-heroicon-o-fire class="size-4 shrink-0" /> Trending
                        </a>
                        <a href="{{ route('leaderboard.index') }}"
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors shrink-0 {{ request()->routeIs('leaderboard.index') ? 'bg-red-50 text-[#D93C3F]' : 'text-gray-500 hover:text-gray-800' }}">
                            <x-heroicon-o-trophy class="size-4 shrink-0" /> Leaderboard
                        </a>
                        <a href="{{ route('packs.browse') }}"
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors shrink-0 {{ request()->routeIs('packs.*') ? 'bg-red-50 text-[#D93C3F]' : 'text-gray-500 hover:text-gray-800' }}">
                            <x-heroicon-o-bookmark class="size-4 shrink-0" /> Packs
                        </a>
                        <a href="{{ route('sets.browse') }}"
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors shrink-0 {{ request()->routeIs('sets.*') ? 'bg-red-50 text-[#D93C3F]' : 'text-gray-500 hover:text-gray-800' }}">
                            <x-heroicon-o-squares-2x2 class="size-4 shrink-0" /> Sets
                        </a>
                    </nav>
                </div>

                <x-leaderboard-card :entries="$this->entries" />
            </main>
        </div>
    </div>
</div>

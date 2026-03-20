<div wire:poll.visible.10s class="max-w-5xl mx-auto flex flex-col sm:flex-row gap-6 mt-8 px-4">
    <aside class="w-full sm:w-56 shrink-0 space-y-6">
        @auth
            <div class="text-center">
                <img
                    src="{{ auth()->user()->profile_photo_url }}"
                    alt="{{ auth()->user()->name }}"
                    class="size-20 rounded-full object-cover ring-2 ring-gray-100 mx-auto"
                />
                <p class="font-bold text-gray-900 mt-3">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 mt-1">Joined {{ auth()->user()->created_at?->format('M d, Y') }}</p>
                <div class="flex justify-center gap-4 mt-3 text-sm">
                    <div>
                        <p class="font-bold text-gray-900">{{ auth()->user()->following_count }}</p>
                        <p class="text-xs text-gray-500">Following</p>
                    </div>
                    <div class="w-px bg-gray-200"></div>
                    <div>
                        <p class="font-bold text-gray-900">{{ auth()->user()->followers_count }}</p>
                        <p class="text-xs text-gray-500">Followers</p>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center">
                <p class="text-sm font-medium text-gray-700">Sign in to personalize your feed</p>
                <a href="{{ route('login') }}" class="mt-3 inline-block text-sm font-semibold text-gray-900 underline underline-offset-2">
                    Log in
                </a>
            </div>
        @endauth

        <div class="border-t border-gray-200"></div>

        <nav class="space-y-1">
            <a href="{{ route('users.feed') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('users.feed') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                </svg>
                Feed
            </a>
            <a href="{{ route('players.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('players.index') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                Players
            </a>
            <a href="{{ route('teams.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('teams.index') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
                Teams
            </a>
            <a href="{{ route('categories.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('categories.index') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                </svg>
                Categories
            </a>
        </nav>

        @auth
            <div class="border-t border-gray-200 pt-4">
                <a
                    href="{{ route('users.profile', auth()->user()) }}"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    My Profile
                </a>
            </div>
        @endauth
    </aside>

    <main class="flex-1 min-w-0">
        <livewire:add-comment />

        @forelse ($this->feeds as $feed)
            <x-dynamic-component :component="$feed->componentName()" :$feed />
        @empty
            <div class="text-center py-16 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <p class="font-medium text-gray-500">Nothing in the feed yet</p>
                <p class="text-sm mt-1">Follow some users to see their activity here.</p>
            </div>
        @endforelse

        {{ $this->feeds->links() }}
    </main>
</div>

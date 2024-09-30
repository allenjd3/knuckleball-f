<x-layouts.app>
    <div class="max-w-7xl mx-auto my-12">
        <h1 class="text-3xl">Knuckleball</h1>

        <p>yeah... login</p>

        <div class="flex gap-4 mt-6">
            @guest
                <x-filament::button
                    :href="route('login')"
                    tag="a"
                >Login</x-filament::button>

                <x-filament::button
                    :href="route('register')"
                    tag="a"
                    outlined
                >Register</x-filament::button>
            @endguest
            @auth
                <x-filament::button
                    :href="route('players.index')"
                    tag="a"
                >View Players</x-filament::button>

                @if (auth()->user()->isSuperAdmin())
                    <x-filament::button
                        href="/cp"
                        tag="a"
                        outlined
                    >Admin Panel</x-filament::button>
                @endif
            @endauth
        </div>
    </div>
</x-layouts.app>

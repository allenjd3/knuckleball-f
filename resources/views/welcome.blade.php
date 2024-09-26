<x-layouts.app>
    <div class="max-w-7xl mx-auto my-12">
        <h1 class="text-3xl">Knuckleball</h1>

        <p>yeah... login</p>

        <div class="flex gap-4 mt-6">
            <x-filament::button
                :href="route('login')"
                tag="a"
            >Login</x-filament::button>

            <x-filament::button
                :href="route('register')"
                tag="a"
                outlined
            >Register</x-filament::button>
        </div>
    </div>
</x-layouts.app>

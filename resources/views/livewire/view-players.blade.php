<div>
    <div class="max-w-7xl py-16 mx-auto">
        <h1 class="text-3xl mb-6">Players</h1>
        {{ $this->table }}

        @can('create', \App\Models\Player::class)
            <div class="flex my-4 gap-4">
                {{ $this->createAction }}
                @can('manage', \App\Models\Player::class)
                    <x-filament::button
                        outlined
                        href="/cp/players"
                        tag="a"
                    >Manage Players</x-filament::button>
                @endcan

            </div>
            <x-filament-actions::modals />
        @endcan
    </div>
</div>

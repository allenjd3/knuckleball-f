<div>
    <div class="max-w-7xl py-16 mx-auto">
        <h1 class="text-3xl">Players</h1>
        {{ $this->table }}

        @can('create', \App\Models\Player::class)
            <div class="my-4">
                {{ $this->createAction }}

                <x-filament-actions::modals />
            </div>
        @endcan
    </div>
</div>

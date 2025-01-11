<div>
    <div class="max-w-7xl py-16 mx-auto">
        <h1 class="text-3xl mb-6">{{ __('Players') . (isset($teamName) ? ': ' . $teamName : '') }}</h1>
        {{ $this->table }}

        @can('create', \App\Models\Player::class)
            <div class="flex my-4 gap-4">
                {{ $this->createAction }}
                @if (Route::is('teams.show'))
                    <x-filament::button
                        outlined
                        href="{{ route('players.index') }}"
                        tag="a"
                    >{{ __('All Players') }}</x-filament::button>
                @endif
                @can('manage', \App\Models\Player::class)
                    <x-filament::button
                        outlined
                        href="/cp/players"
                        tag="a"
                    >{{ __('Manage Players') }}</x-filament::button>
                @endcan
            </div>
            <x-filament-actions::modals />
        @endcan
    </div>
</div>

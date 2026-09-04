<div class="px-4">
    <div class="max-w-7xl py-16 mx-auto">
        <div class="flex justify-center">
        @if (isset($mediaUrl))
            <img src="{{ Storage::url($mediaUrl) }}" alt="{{ $teamName }}" class="size-44 rounded-full" />
        @endif
        </div>
        <h1 class="text-3xl mb-6">{{ __('Players') . (isset($teamName) ? ': ' . $teamName : '') }}</h1>

        @if ($this->shouldShowCategoryTabs())
            <x-filament::tabs label="Category" class="mb-6 flex-wrap gap-y-2">
                <x-filament::tabs.item
                    :active="is_null($categoryTab)"
                    wire:click="setCategoryTab(null)"
                >
                    {{ __('All') }}
                </x-filament::tabs.item>

                @foreach ($this->categoryTabs() as $category)
                    <x-filament::tabs.item
                        :active="$categoryTab === $category->id"
                        wire:click="setCategoryTab({{ $category->id }})"
                    >
                        {{ $category->name }}
                    </x-filament::tabs.item>
                @endforeach
            </x-filament::tabs>
        @endif

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

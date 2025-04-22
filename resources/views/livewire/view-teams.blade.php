<div class="max-w-7xl py-16 mx-auto">
    <h1 class="text-3xl mb-6">{{ __('Teams') }}</h1>
    {{ $this->table }}
    @can('create', App\Models\Team::class)
    <div class="flex my-4 gap-4">
        {{ $this->createTeam }}
    </div>
    @endcan
</div>

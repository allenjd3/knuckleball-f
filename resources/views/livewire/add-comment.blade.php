<div>
    @can('create', App\Models\Comment::class)
        <form wire:submit="save" class="space-y-4 my-8">
            {{ $this->form }}

            <x-filament::button
                type="submit"
                size="sm"
            >
                Add Comment
            </x-filament::button>
        </form>
    @endcan
</div>

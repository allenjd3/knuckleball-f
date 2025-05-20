<div>
    <form wire:submit="save">
        {{ $this->form }}

        <x-filament::button
            type="submit"
            size="sm"
        >
            Add Comment
        </x-filament::button>
    </form>
</div>

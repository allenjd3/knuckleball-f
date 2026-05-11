<div class="max-w-5xl mx-auto py-12 px-4">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl">My Want Lists</h1>
            <p class="text-sm text-gray-500 mt-1">
                <a href="{{ route('wantLists.browse') }}" class="underline hover:text-gray-800">Browse public lists</a>
            </p>
        </div>
        {{ $this->createListAction }}
    </div>

    {{ $this->table }}

    <x-filament-actions::modals />
</div>

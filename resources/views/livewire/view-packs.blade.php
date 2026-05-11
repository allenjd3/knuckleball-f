<div class="max-w-5xl mx-auto py-12 px-4">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl">My Packs</h1>
            <p class="text-sm text-gray-500 mt-1">Your curated player collections</p>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('packs.browse') }}" class="text-sm underline hover:text-gray-700">Discover Packs</a>
            {{ $this->createPack }}
        </div>
    </div>

    @if ($this->packs->isEmpty())
        <div class="text-center py-20 border-4 border-dashed border-gray-200 rounded-xl text-gray-400">
            <x-heroicon-o-rectangle-stack class="size-12 mx-auto mb-3 text-gray-300" />
            <p class="text-lg font-medium">No packs yet</p>
            <p class="text-sm mt-1">Create your first pack to start organizing players.</p>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($this->packs as $pack)
                <div>
                    <a href="{{ $pack->path() }}" class="block relative aspect-[2/3] rounded-lg overflow-hidden bg-gray-100 hover:opacity-90 transition-opacity">
                        @if ($pack->cover_image)
                            <img src="{{ Storage::url($pack->cover_image) }}" alt="{{ $pack->name }}" class="w-full h-full object-cover" />
                        @else
                            <div class="flex flex-col items-center justify-center h-full text-gray-400 p-4 text-center gap-2">
                                <x-heroicon-o-rectangle-stack class="size-10" />
                                <span class="text-sm font-medium leading-tight">{{ $pack->name }}</span>
                            </div>
                        @endif
                    </a>
                    <div class="mt-2 flex items-start justify-between gap-1">
                        <div class="min-w-0">
                            <a href="{{ $pack->path() }}" class="font-medium text-sm block truncate hover:underline">{{ $pack->name }}</a>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $pack->players_count }} {{ Str::plural('player', $pack->players_count) }}
                                @if ($pack->category) · {{ $pack->category->name }} @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-0.5 shrink-0 mt-0.5">
                            @if (!$pack->is_public)
                                <x-heroicon-o-lock-closed class="size-3.5 text-gray-400 mr-1" />
                            @endif
                            <button
                                wire:click="mountAction('editPack', { pack: {{ $pack->id }} })"
                                class="p-1 text-gray-400 hover:text-gray-700 rounded"
                            ><x-heroicon-o-pencil-square class="size-3.5" /></button>
                            <button
                                wire:click="mountAction('deletePack', { pack: {{ $pack->id }} })"
                                class="p-1 text-gray-400 hover:text-red-500 rounded"
                            ><x-heroicon-o-trash class="size-3.5" /></button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <x-filament-actions::modals />
</div>

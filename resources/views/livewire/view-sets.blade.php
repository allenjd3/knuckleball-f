<div class="max-w-5xl mx-auto py-12 px-4">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl">My Sets</h1>
            <p class="text-sm text-gray-500 mt-1">Track signed set completion</p>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('sets.browse') }}" class="text-sm underline hover:text-gray-700">Discover Sets</a>
            {{ $this->createSet }}
        </div>
    </div>

    @if ($this->sets->isEmpty())
        <div class="text-center py-20 border-4 border-dashed border-gray-200 rounded-xl text-gray-400">
            <x-heroicon-o-squares-2x2 class="size-12 mx-auto mb-3 text-gray-300" />
            <p class="text-lg font-medium">No sets yet</p>
            <p class="text-sm mt-1">Create your first set to start tracking signed card completion.</p>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($this->sets as $set)
                <div>
                    <a href="{{ $set->path() }}" class="block relative aspect-[2/3] rounded-lg overflow-hidden bg-gray-100 hover:opacity-90 transition-opacity">
                        @if ($set->cover_image)
                            <img src="{{ Storage::url($set->cover_image) }}" alt="{{ $set->name }}" class="w-full h-full object-cover" />
                        @else
                            <div class="flex flex-col items-center justify-center h-full text-gray-400 p-4 text-center gap-2">
                                <x-heroicon-o-squares-2x2 class="size-10" />
                                <span class="text-sm font-medium leading-tight">{{ $set->name }}</span>
                            </div>
                        @endif

                        @php $pct = $set->completionPercentage(); @endphp
                        @if ($pct > 0)
                            <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-black/20">
                                <div class="h-full {{ $pct === 100 ? 'bg-amber-400' : 'bg-[#D93C3F]' }}" style="width: {{ $pct }}%"></div>
                            </div>
                        @endif
                    </a>
                    <div class="mt-2 flex items-start justify-between gap-1">
                        <div class="min-w-0">
                            <a href="{{ $set->path() }}" class="font-medium text-sm block truncate hover:underline">{{ $set->name }}</a>
                            <p class="text-xs text-gray-500 mt-0.5">
                                @if ($set->year) {{ $set->year }} · @endif
                                {{ $set->entries_count }} {{ Str::plural('card', $set->entries_count) }}
                                @if ($pct > 0) · {{ $pct }}% signed @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-0.5 shrink-0 mt-0.5">
                            @if (!$set->is_public)
                                <x-heroicon-o-lock-closed class="size-3.5 text-gray-400 mr-1" />
                            @endif
                            <button
                                wire:click="mountAction('editSet', { set: {{ $set->id }} })"
                                class="p-1 text-gray-400 hover:text-gray-700 rounded"
                            ><x-heroicon-o-pencil-square class="size-3.5" /></button>
                            <button
                                wire:click="mountAction('deleteSet', { set: {{ $set->id }} })"
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

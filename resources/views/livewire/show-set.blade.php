<div class="max-w-5xl mx-auto py-12 px-4" x-data="{ tab: 'need_it' }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-6 mb-8">
        <div class="shrink-0 w-32 sm:w-40">
            <div class="aspect-[2/3] rounded-lg overflow-hidden bg-gray-100 relative">
                @if ($set->cover_image)
                    <img src="{{ Storage::url($set->cover_image) }}" alt="{{ $set->name }}" class="w-full h-full object-cover" />
                @else
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 p-4 text-center gap-2">
                        <x-heroicon-o-squares-2x2 class="size-8" />
                        <span class="text-xs">No cover</span>
                    </div>
                @endif

                @if ($this->completionPct > 0)
                    <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-black/20">
                        <div class="h-full {{ $this->completionPct === 100 ? 'bg-amber-400' : 'bg-[#D93C3F]' }}"
                             style="width: {{ $this->completionPct }}%"></div>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <h1 class="text-3xl">{{ $set->name }}</h1>
                        @if ($set->year)
                            <span class="text-xl text-gray-400">{{ $set->year }}</span>
                        @endif
                        <span class="text-xs border border-gray-300 rounded-full px-2 py-0.5 text-gray-500 shrink-0">
                            {{ $set->is_public ? 'Public' : 'Private' }}
                        </span>
                    </div>

                    @if ($set->manufacturer)
                        <p class="text-sm text-gray-500">{{ $set->manufacturer }}</p>
                    @endif

                    @if ($set->description)
                        <p class="text-gray-600 mt-2 text-sm">{{ $set->description }}</p>
                    @endif

                    <p class="text-sm text-gray-400 mt-3">
                        By <a href="{{ route('users.profile', $set->user) }}" class="underline hover:text-gray-700">{{ $set->user->name }}</a>
                        · {{ $set->entries()->count() }} {{ Str::plural('card', $set->entries()->count()) }}
                        @if ($set->total_card_count)
                            / {{ $set->total_card_count }} total
                        @endif
                        · {{ $set->followers()->count() }} {{ Str::plural('follower', $set->followers()->count()) }}
                    </p>

                    {{-- Progress bar --}}
                    @if ($this->completionPct > 0 || $set->entries()->count() > 0)
                        <div class="mt-3 flex items-center gap-3">
                            <div class="flex-1 bg-gray-100 rounded-full h-2 max-w-xs">
                                <div class="h-2 rounded-full transition-all {{ $this->completionPct === 100 ? 'bg-amber-400' : 'bg-[#D93C3F]' }}"
                                     style="width: {{ $this->completionPct }}%"></div>
                            </div>
                            <span class="text-sm font-bold {{ $this->completionPct === 100 ? 'text-amber-600' : 'text-[#D93C3F]' }}">
                                {{ $this->completionPct }}%
                            </span>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    {{ $this->followAction }}
                    @if ($this->isOwner)
                        <a href="{{ route('sets.index') }}" class="text-sm underline text-gray-500 hover:text-gray-700">My Sets</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-gray-200 mb-6">
        <div class="flex items-center gap-1">
            <button @click="tab = 'need_it'"
                    :class="tab === 'need_it' ? 'border-b-2 border-[#D93C3F] text-gray-900 font-semibold' : 'text-gray-400 hover:text-gray-600'"
                    class="px-4 py-3 text-sm transition-colors -mb-px">
                Need It
                <span class="ml-1 text-xs {{ $this->needItEntries->count() > 0 ? 'text-gray-500' : 'text-gray-300' }}">
                    ({{ $this->needItEntries->count() }})
                </span>
            </button>
            <button @click="tab = 'have_it'"
                    :class="tab === 'have_it' ? 'border-b-2 border-[#D93C3F] text-gray-900 font-semibold' : 'text-gray-400 hover:text-gray-600'"
                    class="px-4 py-3 text-sm transition-colors -mb-px">
                Have It Signed
                <span class="ml-1 text-xs {{ $this->haveItEntries->count() > 0 ? 'text-green-600 font-medium' : 'text-gray-300' }}">
                    ({{ $this->haveItEntries->count() }})
                </span>
            </button>

            @if ($this->isOwner)
                <div class="ml-auto pb-1">
                    {{ $this->addEntry }}
                </div>
            @endif
        </div>
    </div>

    {{-- Need It tab --}}
    <div x-show="tab === 'need_it'" x-cloak>
        @if ($this->needItEntries->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <x-heroicon-o-check-circle class="size-10 mx-auto mb-3 opacity-30" />
                <p class="text-sm">
                    @if ($this->isOwner)
                        No cards in your need-it list. Add some above!
                    @else
                        Nothing needed — they might be done!
                    @endif
                </p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach ($this->needItEntries as $entry)
                    <div class="flex items-center gap-3 py-3">
                        @if ($entry->card_number)
                            <span class="text-xs font-mono text-gray-400 w-10 shrink-0 text-right">#{{ $entry->card_number }}</span>
                        @else
                            <span class="w-10 shrink-0"></span>
                        @endif
                        <div class="flex-1 min-w-0">
                            <a href="{{ $entry->player->path() }}" class="font-medium text-sm text-gray-900 hover:underline">
                                {{ $entry->player->name }}
                            </a>
                            @if ($entry->notes)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $entry->notes }}</p>
                            @endif
                        </div>
                        @if ($this->isOwner)
                            <div class="flex items-center gap-1 shrink-0">
                                <button
                                    wire:click="mountAction('markSigned', { entry: {{ $entry->id }} })"
                                    class="text-xs font-medium text-green-600 hover:text-green-800 px-2 py-1 rounded hover:bg-green-50 transition-colors">
                                    Got it ✓
                                </button>
                                <button
                                    wire:click="mountAction('removeEntry', { entry: {{ $entry->id }} })"
                                    class="p-1 text-gray-300 hover:text-red-400 transition-colors rounded">
                                    <x-heroicon-o-x-mark class="size-4" />
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Have It Signed tab --}}
    <div x-show="tab === 'have_it'" x-cloak>
        @if ($this->haveItEntries->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <x-heroicon-o-pencil class="size-10 mx-auto mb-3 opacity-30" />
                <p class="text-sm">No signed cards recorded yet.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach ($this->haveItEntries as $entry)
                    <div class="flex items-center gap-3 py-3">
                        @if ($entry->card_number)
                            <span class="text-xs font-mono text-gray-400 w-10 shrink-0 text-right">#{{ $entry->card_number }}</span>
                        @else
                            <span class="w-10 shrink-0"></span>
                        @endif
                        <div class="flex-1 min-w-0">
                            <a href="{{ $entry->player->path() }}" class="font-medium text-sm text-gray-900 hover:underline">
                                {{ $entry->player->name }}
                            </a>
                            <p class="text-xs text-gray-400 mt-0.5">
                                @if ($entry->date_signed)
                                    Signed {{ $entry->date_signed->format('M j, Y') }}
                                @endif
                                @if ($entry->notes)
                                    @if ($entry->date_signed) · @endif{{ $entry->notes }}
                                @endif
                            </p>
                        </div>
                        <x-heroicon-o-check-circle class="size-4 text-green-500 shrink-0" />
                        @if ($this->isOwner)
                            <button
                                wire:click="mountAction('removeEntry', { entry: {{ $entry->id }} })"
                                class="p-1 text-gray-300 hover:text-red-400 transition-colors rounded shrink-0">
                                <x-heroicon-o-x-mark class="size-4" />
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <x-filament-actions::modals />
</div>

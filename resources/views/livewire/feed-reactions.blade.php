<div x-data="{ open: false }" class="flex items-center gap-1">
    {{-- Existing reactions with counts --}}
    @foreach ($emojis as $type => $emoji)
        @if ($counts[$type] > 0)
            <button
                wire:click="toggle('{{ $type }}')"
                class="flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-xs transition-colors
                    {{ $userReacted[$type] ? 'bg-gray-100 font-semibold text-gray-700' : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600' }}"
            >
                <span>{{ $emoji }}</span>
                <span>{{ $counts[$type] }}</span>
            </button>
        @endif
    @endforeach

    {{-- Add reaction button --}}
    <div class="relative">
        <button @click="open = !open"
                class="p-1 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-colors">
            <x-heroicon-o-face-smile class="size-4" />
        </button>

        <div x-show="open" x-cloak @click.outside="open = false"
             class="absolute bottom-full left-0 mb-1.5 flex items-center gap-0.5 bg-white border border-gray-200 rounded-xl shadow-lg p-1.5 z-10">
            @foreach ($emojis as $type => $emoji)
                <button
                    @click="$wire.toggle('{{ $type }}'); open = false"
                    class="p-1.5 rounded-lg text-base transition-colors hover:bg-gray-100
                        {{ $userReacted[$type] ? 'bg-gray-100' : '' }}"
                >{{ $emoji }}</button>
            @endforeach
        </div>
    </div>
</div>

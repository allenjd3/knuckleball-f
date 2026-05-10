<div class="flex items-center gap-2 flex-wrap">
    @foreach ($emojis as $type => $emoji)
        <button
            wire:click="toggle('{{ $type }}')"
            class="flex items-center gap-1 text-sm px-2 py-0.5 rounded-full border transition-colors
                {{ $userReacted[$type]
                    ? 'border-gray-400 bg-gray-100 font-semibold'
                    : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}"
        >
            {{ $emoji }}
            @if ($counts[$type] > 0)
                <span class="text-xs text-gray-500">{{ $counts[$type] }}</span>
            @endif
        </button>
    @endforeach
</div>

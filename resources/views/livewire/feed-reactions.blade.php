<div class="flex items-center gap-3">
    @foreach ($emojis as $type => $emoji)
        <button
            wire:click="toggle('{{ $type }}')"
            class="flex items-center gap-1 text-sm transition-colors
                {{ $userReacted[$type] ? 'font-bold' : 'text-gray-400 hover:text-gray-700' }}"
        >
            <span>{{ $emoji }}</span>
            @if ($counts[$type] > 0)
                <span class="text-xs {{ $userReacted[$type] ? 'text-gray-700' : 'text-gray-400' }}">
                    {{ $counts[$type] }}
                </span>
            @endif
        </button>
    @endforeach
</div>

<article class="bg-white border border-gray-200 rounded-lg p-4" {{ $attributes }}>
    <div class="flex items-start gap-3">
        <img class="size-10 rounded-full flex-shrink-0" src="{{ $photo }}" alt="{{ $user }}" />
        <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-500">
                <a href="{{ $userPath }}" class="font-semibold text-gray-800 hover:underline">{{ $user }}</a>
                sent mail to
            </p>
            <p class="font-bold text-xl leading-tight mt-0.5">
                <a href="{{ $playerPath }}" class="hover:underline">{{ $player }}</a>
            </p>
            @if ($feed->comment)
                <p class="text-sm text-gray-600 mt-1">{{ $feed->comment }}</p>
            @endif
            <p class="flex items-center gap-1 text-sm text-gray-400 mt-2">
                <x-heroicon-o-envelope class="size-4 flex-shrink-0" />
                Sent {{ $dateSent }}
            </p>
        </div>
    </div>
    <div class="mt-4 pt-3 border-t border-gray-100 space-y-2">
        <livewire:feed-reactions :feed="$feed" wire:key="reactions-{{ $feed->id }}" />
        <livewire:feed-comments :feed="$feed" wire:key="comments-{{ $feed->id }}" />
    </div>
</article>

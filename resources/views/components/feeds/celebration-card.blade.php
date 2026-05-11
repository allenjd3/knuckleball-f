<article class="bg-white border border-gray-100 rounded-2xl overflow-hidden" {{ $attributes }}>
    <div class="p-4">
        <div class="flex items-start gap-3">
            <img class="size-9 rounded-full flex-shrink-0 object-cover" src="{{ $photo }}" alt="{{ $user }}" />
            <div class="flex-1 min-w-0">
                <p class="text-sm leading-snug">
                    <a href="{{ $userPath }}" class="font-semibold text-gray-900 hover:underline">{{ $user }}</a>
                    <span class="text-gray-500"> got a return from </span>
                    <a href="{{ $playerPath }}" class="font-semibold text-gray-900 hover:underline">{{ $player }}</a>
                </p>
                <p class="text-xs text-gray-400 mt-0.5">
                    @if ($turnaroundDays !== null)
                        Turnaround: <span class="font-semibold text-gray-700">{{ $turnaroundDays }} Days</span>
                        <span class="mx-1">·</span>{{ $dateSent }} – {{ $dateReturned }}
                    @else
                        {{ $dateSent }}
                    @endif
                    @if ($category)
                        <span class="mx-1">·</span>{{ $category }}
                    @endif
                </p>
            </div>
            <button class="text-gray-300 hover:text-gray-500 transition-colors shrink-0">
                <x-heroicon-o-ellipsis-horizontal class="size-5" />
            </button>
        </div>

        @if ($heroPhoto)
            <div class="mt-3 ml-12">
                <img src="{{ Storage::url($heroPhoto) }}"
                     alt="Returned auto"
                     class="w-full rounded-xl object-cover bg-gray-50"
                     style="max-height: 260px;" />
            </div>
        @endif

        @if ($feed->comment)
            <p class="text-sm text-gray-600 mt-2 ml-12">{{ $feed->comment }}</p>
        @endif
    </div>

    <div class="px-4 pb-3 flex items-center justify-between border-t border-gray-50 pt-3">
        <livewire:feed-reactions :feed="$feed" wire:key="reactions-{{ $feed->id }}" />
        <livewire:feed-comments :feed="$feed" wire:key="comments-{{ $feed->id }}" />
    </div>
</article>

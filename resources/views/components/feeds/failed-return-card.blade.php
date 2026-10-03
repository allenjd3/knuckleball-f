<article class="bg-white border border-gray-100 rounded-2xl overflow-hidden" {{ $attributes }}>
    <div class="p-4">
        <div class="flex items-start gap-3">
            <img class="size-9 rounded-full flex-shrink-0 object-cover" src="{{ $photo }}" alt="{{ $user }}" />
            <div class="flex-1 min-w-0">
                <p class="text-sm leading-snug">
                    <a href="{{ $userPath }}" class="font-semibold text-gray-900 hover:underline">{{ $user }}</a>
                    <span class="text-gray-500"> got a failed return from </span>
                    <a href="{{ $playerPath }}" class="font-semibold text-gray-900 hover:underline">{{ $player }}</a>
                </p>
                <p class="text-xs text-gray-400 mt-0.5">
                    <span class="inline-flex items-center gap-1 font-semibold text-red-500">
                        <x-heroicon-o-x-circle class="size-3.5" />
                        {{ $failureReason?->label() ?? 'Failed' }}
                    </span>
                    <span class="mx-1">·</span>{{ $dateSent }}@if ($dateReturned) – {{ $dateReturned }}@endif
                    @if ($category)
                        <span class="mx-1">·</span>{{ $category }}
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-1 shrink-0">
                <button class="text-gray-300 hover:text-gray-500 transition-colors p-1">
                    <x-heroicon-o-ellipsis-horizontal class="size-5" />
                </button>
            </div>
        </div>

        @if ($failureReason === \App\Enums\FailureReason::ReturnToSender && $playerPath)
            <div class="mt-3 ml-12">
                <a href="{{ $playerPath }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#CB504B] border border-[#CB504B]/30 rounded-full px-3 py-1 hover:bg-red-50 transition-colors">
                    <x-heroicon-o-map-pin class="size-4" />
                    Know a new address?
                </a>
            </div>
        @endif

        @if ($feed->comment)
            <p class="text-sm text-gray-600 mt-2 ml-12">{{ $feed->comment }}</p>
        @endif
    </div>

    <div class="px-4 border-t border-gray-50">
        <div class="flex items-center justify-between py-2.5">
            <livewire:feed-reactions :feed="$feed" wire:key="reactions-{{ $feed->id }}" />
        </div>
        <div class="pb-3">
            <livewire:feed-comments :feed="$feed" wire:key="comments-{{ $feed->id }}" />
        </div>
    </div>
</article>

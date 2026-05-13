<article class="bg-white border border-gray-100 rounded-2xl overflow-hidden" {{ $attributes }}>
    <div class="p-4">
        <div class="flex items-start gap-3">
            <div class="size-9 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                <x-heroicon-o-squares-2x2 class="size-4 text-gray-400" />
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm leading-snug">
                    <a href="{{ $userPath }}" class="font-semibold text-gray-900 hover:underline">{{ $user }}</a>
                    <span class="text-gray-500"> started tracking {{ count($sets) }} new {{ Str::plural('Set', count($sets)) }}</span>
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $createdAt }}</p>
            </div>
            <button class="text-gray-300 hover:text-gray-500 transition-colors shrink-0">
                <x-heroicon-o-ellipsis-horizontal class="size-5" />
            </button>
        </div>

        <div class="mt-3 ml-12 divide-y divide-gray-50">
            @foreach ($sets as $set)
                <div class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                    <div class="size-10 rounded-lg overflow-hidden shrink-0 bg-gray-100">
                        @if (!empty($set['cover_image']))
                            <img src="{{ Storage::url($set['cover_image']) }}" alt="{{ $set['name'] }}"
                                 class="w-full h-full object-cover" />
                        @else
                            <div class="w-full h-full bg-gray-100 flex items-center justify-center">
                                <x-heroicon-o-squares-2x2 class="size-4 text-gray-300" />
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="{{ $set['path'] ?? '#' }}" class="font-semibold text-sm text-gray-900 hover:underline truncate block">
                            {{ $set['name'] }}
                            @if (!empty($set['year']))
                                <span class="font-normal text-gray-400">{{ $set['year'] }}</span>
                            @endif
                        </a>
                        <p class="text-xs text-gray-400 mt-0.5">
                            @if (!empty($set['manufacturer'])) {{ $set['manufacturer'] }} · @endif
                            {{ $set['entries_count'] ?? 0 }} {{ Str::plural('card', $set['entries_count'] ?? 0) }}
                        </p>
                    </div>
                    <a href="{{ $set['path'] ?? '#' }}"
                       class="shrink-0 text-xs font-bold text-[#D93C3F] hover:underline">
                        View
                    </a>
                </div>
            @endforeach
        </div>
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

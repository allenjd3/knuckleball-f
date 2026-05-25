<article class="bg-white border border-gray-100 rounded-2xl overflow-hidden" {{ $attributes }}>
    <div class="p-4">
        <div class="flex items-start gap-3">
            <div class="size-9 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                <x-heroicon-o-rectangle-stack class="size-4 text-gray-400" />
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm leading-snug">
                    <a href="{{ $userPath }}" class="font-semibold text-gray-900 hover:underline">{{ $user }}</a>
                    <span class="text-gray-500"> created {{ count($packs) }} new {{ Str::plural('Pack', count($packs)) }}</span>
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $createdAt }}</p>
            </div>
            <button class="text-gray-300 hover:text-gray-500 transition-colors shrink-0">
                <x-heroicon-o-ellipsis-horizontal class="size-5" />
            </button>
        </div>

        <div class="mt-3 ml-12 divide-y divide-gray-50">
            @foreach ($packs as $pack)
                <div class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                    <div class="size-10 rounded-lg overflow-hidden shrink-0 bg-gray-100">
                        @if (!empty($pack['cover_image']))
                            <img src="{{ Storage::url($pack['cover_image']) }}" alt="{{ $pack['name'] }}"
                                 class="w-full h-full object-cover" />
                        @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                <x-heroicon-o-rectangle-stack class="size-4 text-gray-400" />
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="{{ $pack['path'] ?? '#' }}" class="font-semibold text-sm text-gray-900 hover:underline truncate block">
                            {{ $pack['name'] }}
                        </a>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $pack['players_count'] ?? 0 }} {{ Str::plural('Player', $pack['players_count'] ?? 0) }}
                            @if (!empty($pack['category']))
                                <span class="mx-1">·</span>{{ $pack['category'] }}
                            @endif
                        </p>
                    </div>
                    <a href="{{ $pack['path'] ?? '#' }}"
                       class="shrink-0 text-xs font-bold text-[#D93C3F] hover:underline">
                        Follow
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <div class="px-4 pb-3 flex items-center justify-between border-t border-gray-50 pt-3">
        <livewire:feed-reactions :feed="$feed" wire:key="reactions-{{ $feed->id }}" />
        <livewire:feed-comments :feed="$feed" wire:key="comments-{{ $feed->id }}" />
    </div>
</article>

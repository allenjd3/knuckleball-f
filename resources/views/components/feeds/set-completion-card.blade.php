@php
    $setName    = data_get($feed->meta, 'set_name', '');
    $setPath    = data_get($feed->meta, 'set_path', '#');
    $setYear    = data_get($feed->meta, 'set_year');
    $coverImage = data_get($feed->meta, 'cover_image');
    $photo      = data_get($feed->meta, 'photo', '');
    $user       = data_get($feed->meta, 'user', '');
    $userPath   = data_get($feed->meta, 'user_path', '#');
@endphp
<article class="bg-white border border-amber-200 rounded-2xl overflow-hidden" {{ $attributes }}>
    <div class="p-4">
        <div class="flex items-start gap-3">
            <img class="size-9 rounded-full flex-shrink-0 object-cover" src="{{ $photo }}" alt="{{ $user }}" />
            <div class="flex-1 min-w-0">
                <p class="text-sm leading-snug">
                    <a href="{{ $userPath }}" class="font-semibold text-gray-900 hover:underline">{{ $user }}</a>
                    <span class="text-gray-500"> completed the </span>
                    <a href="{{ $setPath }}" class="font-semibold text-gray-900 hover:underline">{{ $setName }}{{ $setYear ? ' ' . $setYear : '' }}</a>
                    <span class="text-gray-500"> set!</span>
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $feed->created_at->diffForHumans() }}</p>
            </div>
            <span class="text-lg shrink-0" title="Set complete!">🏆</span>
        </div>

        @if ($coverImage)
            <div class="mt-3 ml-12">
                <a href="{{ $setPath }}">
                    <img src="{{ Storage::url($coverImage) }}"
                         alt="{{ $setName }}"
                         class="w-full rounded-xl object-cover bg-gray-50"
                         style="max-height: 200px;" />
                </a>
            </div>
        @endif

        <div class="mt-3 ml-12">
            <div class="inline-flex items-center gap-1.5 bg-amber-50 border border-amber-200 rounded-full px-3 py-1">
                <x-heroicon-o-star class="size-3.5 text-amber-500" />
                <span class="text-xs font-bold text-amber-700">100% Complete</span>
            </div>
        </div>
    </div>

    <div class="px-4 border-t border-amber-100">
        <div class="flex items-center justify-between py-2.5">
            <livewire:feed-reactions :feed="$feed" wire:key="reactions-{{ $feed->id }}" />
        </div>
        <div class="pb-3">
            <livewire:feed-comments :feed="$feed" wire:key="comments-{{ $feed->id }}" />
        </div>
    </div>
</article>

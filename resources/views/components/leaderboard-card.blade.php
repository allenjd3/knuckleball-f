@props(['entries'])

@php $entries = collect($entries)->filter(); @endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl p-6 sm:p-8 md:p-10 text-white font-sans']) }} style="background-color:#D93C3F;">

    {{-- Header --}}
    <div class="flex items-center gap-2 mb-7 sm:mb-10">
        <x-heroicon-o-trophy class="size-4 sm:size-5 text-white/70 shrink-0" />
        <span class="text-[10px] sm:text-[11px] font-bold tracking-widest uppercase text-white/70">Leaderboard</span>
    </div>

    {{-- Divider --}}
    <div class="border-t border-white/20 mb-6"></div>

    {{-- Rows --}}
    <div class="flex flex-col gap-4">
        @forelse ($entries as $key => $entry)
            <a href="{{ $entry['user']->path() }}" class="flex justify-between items-center text-sm font-medium gap-2 hover:opacity-80 transition-opacity">
                <div class="flex items-center gap-3 shrink-0">
                    @switch($key)
                        @case('sends')
                            <x-heroicon-o-paper-airplane class="size-4 sm:size-5 text-white/70 shrink-0" />
                            @break
                        @case('returns')
                            <x-heroicon-o-inbox-arrow-down class="size-4 sm:size-5 text-white/70 shrink-0" />
                            @break
                        @case('addresses')
                            <x-heroicon-o-map-pin class="size-4 sm:size-5 text-white/70 shrink-0" />
                            @break
                        @case('cards')
                            <x-heroicon-o-photo class="size-4 sm:size-5 text-white/70 shrink-0" />
                            @break
                        @case('packs')
                            <x-heroicon-o-bookmark class="size-4 sm:size-5 text-white/70 shrink-0" />
                            @break
                        @case('sets')
                            <x-heroicon-o-squares-2x2 class="size-4 sm:size-5 text-white/70 shrink-0" />
                            @break
                    @endswitch
                    {{ $entry['label'] }}
                </div>
                <div class="flex items-center gap-2.5 shrink-0">
                    <img src="{{ $entry['user']->profile_photo_url }}" alt="{{ $entry['user']->name }}"
                         class="size-6 sm:size-7 rounded-full object-cover" />
                    <span class="text-sm font-semibold">{{ $entry['user']->name }}</span>
                    <span class="text-sm font-black tabular-nums w-10 text-right">{{ number_format($entry['count']) }}</span>
                </div>
            </a>
        @empty
            <p class="text-white/50 text-sm text-center py-2">No activity yet — the leaderboard will fill in once people start sending.</p>
        @endforelse
    </div>
</div>

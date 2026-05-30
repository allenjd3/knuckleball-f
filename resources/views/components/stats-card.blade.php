@props(['stats'])

<div {{ $attributes->merge(['class' => 'rounded-2xl p-6 sm:p-8 md:p-10 text-white font-sans']) }} style="background-color:#D93C3F;">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-7 sm:mb-10 gap-2">
        {{ $header }}
    </div>

    {{-- 4 headline numbers --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 sm:gap-6 mb-7 sm:mb-10">
        <div>
            <p class="text-[clamp(22px,7vw,80px)] font-black leading-none tabular-nums">{{ data_get($stats, 'total_sends', 0) }}</p>
            <p class="text-[10px] sm:text-[11px] font-bold tracking-widest uppercase text-white/70 mt-2 sm:mt-3">Sends</p>
        </div>
        <div>
            <p class="text-[clamp(22px,7vw,80px)] font-black leading-none tabular-nums">{{ data_get($stats, 'total_returns', 0) }}</p>
            <p class="text-[10px] sm:text-[11px] font-bold tracking-widest uppercase text-white/70 mt-2 sm:mt-3">Returns</p>
        </div>
        <div>
            <p class="text-[clamp(22px,7vw,80px)] font-black leading-none tabular-nums">{{ data_get($stats, 'success_rate', 0) }}<span class="text-[clamp(12px,3.5vw,40px)] font-black">%</span></p>
            <p class="text-[10px] sm:text-[11px] font-bold tracking-widest uppercase text-white/70 mt-2 sm:mt-3">Success</p>
        </div>
        <div>
            <p class="text-[clamp(22px,7vw,80px)] font-black leading-none tabular-nums">{{ data_get($stats, 'unique_players', 0) }}</p>
            <p class="text-[10px] sm:text-[11px] font-bold tracking-widest uppercase text-white/70 mt-2 sm:mt-3">Players</p>
        </div>
    </div>

    {{-- Divider --}}
    <div class="border-t border-white/20 mb-6"></div>

    {{-- Detail rows --}}
    <div class="flex flex-col gap-4">
        @if (data_get($stats, 'fastest_return'))
            <div class="flex justify-between items-center text-sm font-medium gap-2">
                <div class="flex items-center gap-3 shrink-0">
                    <x-heroicon-o-bolt class="size-4 sm:size-5 text-white/70 shrink-0" />
                    Fastest Return
                </div>
                <span class="text-sm font-semibold text-right">{{ data_get($stats, 'fastest_return.player_name') }} · {{ data_get($stats, 'fastest_return.days') }} days</span>
            </div>
        @endif
        @if (data_get($stats, 'longest_wait'))
            <div class="flex justify-between items-center text-sm font-medium gap-2">
                <div class="flex items-center gap-3 shrink-0">
                    <x-heroicon-o-clock class="size-4 sm:size-5 text-white/70 shrink-0" />
                    Longest Wait
                </div>
                <span class="text-sm font-semibold text-right">{{ data_get($stats, 'longest_wait.player_name') }} · {{ data_get($stats, 'longest_wait.days') }} days</span>
            </div>
        @endif
        @if (data_get($stats, 'favorite_team'))
            <div class="flex justify-between items-center text-sm font-medium gap-2">
                <div class="flex items-center gap-3 shrink-0">
                    <x-heroicon-o-trophy class="size-4 sm:size-5 text-white/70 shrink-0" />
                    Favorite Team
                </div>
                <span class="text-sm font-semibold text-right">{{ data_get($stats, 'favorite_team') }}</span>
            </div>
        @endif
        @if (data_get($stats, 'favorite_category'))
            <div class="flex justify-between items-center text-sm font-medium gap-2">
                <div class="flex items-center gap-3 shrink-0">
                    <x-heroicon-o-tag class="size-4 sm:size-5 text-white/70 shrink-0" />
                    Top Category
                </div>
                <span class="text-sm font-semibold text-right">{{ data_get($stats, 'favorite_category') }}</span>
            </div>
        @endif
        @if (data_get($stats, 'most_reacted_return'))
            <div class="flex justify-between items-center text-sm font-medium gap-2">
                <div class="flex items-center gap-3 shrink-0">
                    <x-heroicon-s-heart class="size-4 sm:size-5 text-white/70 shrink-0" />
                    Most Reacted
                </div>
                <span class="text-sm font-semibold text-right">{{ data_get($stats, 'most_reacted_return.player_name') }} · {{ data_get($stats, 'most_reacted_return.reaction_count') }} reactions</span>
            </div>
        @endif
        @if (data_get($stats, 'first_send'))
            @php $firstSend = data_get($stats, 'first_send'); @endphp
            <div class="flex justify-between items-center text-sm font-medium gap-2">
                <div class="flex items-center gap-3 shrink-0">
                    <x-heroicon-o-paper-airplane class="size-4 sm:size-5 text-white/70 shrink-0" />
                    First Send
                </div>
                <span class="text-sm font-semibold text-right">{{ ($firstSend instanceof \Carbon\Carbon ? $firstSend : \Carbon\Carbon::parse($firstSend))->format('M j, Y') }}</span>
            </div>
        @endif
        @if (data_get($stats, 'most_recent_return'))
            @php $latestReturn = data_get($stats, 'most_recent_return'); @endphp
            <div class="flex justify-between items-center text-sm font-medium gap-2">
                <div class="flex items-center gap-3 shrink-0">
                    <x-heroicon-o-inbox-arrow-down class="size-4 sm:size-5 text-white/70 shrink-0" />
                    Latest Return
                </div>
                <span class="text-sm font-semibold text-right">{{ ($latestReturn instanceof \Carbon\Carbon ? $latestReturn : \Carbon\Carbon::parse($latestReturn))->format('M j, Y') }}</span>
            </div>
        @endif
        @if (data_get($stats, 'total_sends', 0) === 0)
            <p class="text-white/50 text-sm text-center py-2">No activity yet — send some mail to see stats here.</p>
        @endif
    </div>
</div>

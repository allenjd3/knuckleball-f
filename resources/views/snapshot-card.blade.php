<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $user->name }} · {{ $year }} Snapshot</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 flex flex-col items-center justify-start min-h-screen p-4 sm:p-8 font-sans">

<div id="card" class="w-full max-w-[900px] rounded-3xl py-6 px-5 sm:py-12 sm:px-14 text-white" style="background-color:#D93C3F;">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-7 sm:mb-12 gap-2">
        <p class="text-[10px] sm:text-xs font-bold tracking-widest uppercase">{{ $user->name }} · All-Time Stats</p>
        <span class="bg-white rounded-full px-4 sm:px-5 py-1.5 sm:py-2 text-[10px] sm:text-xs font-black tracking-widest uppercase shrink-0" style="color:#D93C3F;">{{ $year }} Snapshot</span>
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
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <svg class="size-4 sm:size-5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                    Fastest Return
                </div>
                <span class="text-sm font-semibold text-right">{{ data_get($stats, 'fastest_return.player_name') }} · {{ data_get($stats, 'fastest_return.days') }} days</span>
            </div>
        @endif
        @if (data_get($stats, 'longest_wait'))
            <div class="flex justify-between items-center text-sm font-medium gap-2">
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <svg class="size-4 sm:size-5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" /></svg>
                    Longest Wait
                </div>
                <span class="text-sm font-semibold text-right">{{ data_get($stats, 'longest_wait.player_name') }} · {{ data_get($stats, 'longest_wait.days') }} days</span>
            </div>
        @endif
        @if (data_get($stats, 'favorite_team'))
            <div class="flex justify-between items-center text-sm font-medium gap-2">
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <svg class="size-4 sm:size-5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" /></svg>
                    Favorite Team
                </div>
                <span class="text-sm font-semibold text-right">{{ data_get($stats, 'favorite_team') }}</span>
            </div>
        @endif
        @if (data_get($stats, 'favorite_category'))
            <div class="flex justify-between items-center text-sm font-medium gap-2">
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <svg class="size-4 sm:size-5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                    Top Category
                </div>
                <span class="text-sm font-semibold text-right">{{ data_get($stats, 'favorite_category') }}</span>
            </div>
        @endif
        @if (data_get($stats, 'most_reacted_return'))
            <div class="flex justify-between items-center text-sm font-medium gap-2">
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <svg class="size-4 sm:size-5 shrink-0 opacity-70" fill="white" viewBox="0 0 24 24"><path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001z" /></svg>
                    Most Reacted
                </div>
                <span class="text-sm font-semibold text-right">{{ data_get($stats, 'most_reacted_return.player_name') }} · {{ data_get($stats, 'most_reacted_return.reaction_count') }} reactions</span>
            </div>
        @endif
        @if (data_get($stats, 'first_send'))
            <div class="flex justify-between items-center text-sm font-medium gap-2">
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <svg class="size-4 sm:size-5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12zm0 0h7.5" /></svg>
                    First Send
                </div>
                <span class="text-sm font-semibold text-right">
                    @php $firstSend = data_get($stats, 'first_send'); @endphp
                    {{ ($firstSend instanceof \Carbon\Carbon ? $firstSend : \Carbon\Carbon::parse($firstSend))->format('M j, Y') }}
                </span>
            </div>
        @endif
        @if (data_get($stats, 'most_recent_return'))
            <div class="flex justify-between items-center text-sm font-medium gap-2">
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <svg class="size-4 sm:size-5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" /></svg>
                    Latest Return
                </div>
                <span class="text-sm font-semibold text-right">
                    @php $latestReturn = data_get($stats, 'most_recent_return'); @endphp
                    {{ ($latestReturn instanceof \Carbon\Carbon ? $latestReturn : \Carbon\Carbon::parse($latestReturn))->format('M j, Y') }}
                </span>
            </div>
        @endif
        @if (data_get($stats, 'total_sends', 0) === 0)
            <p class="text-white/50 text-sm text-center py-2">No activity yet for {{ $year }}.</p>
        @endif
    </div>
</div>

<div class="flex gap-3 mt-5 w-full max-w-[900px] justify-end">
    <a href="{{ route('users.profile', $user) }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center gap-1.5 no-underline bg-gray-200 text-gray-900">← Back</a>
    <button class="px-5 py-2.5 rounded-xl text-sm font-semibold cursor-pointer border-0 inline-flex items-center gap-1.5 text-white" style="background-color:#D93C3F;" onclick="downloadCard()">Download Image</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
function downloadCard() {
    const card = document.getElementById('card');
    html2canvas(card, {
        scale: 2,
        backgroundColor: '#D93C3F',
        useCORS: true,
        width: card.scrollWidth,
        height: card.scrollHeight,
    }).then(canvas => {
        const link = document.createElement('a');
        link.download = '{{ $user->handle ?? $user->slug }}-{{ $year }}-snapshot.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    });
}
</script>
</body>
</html>

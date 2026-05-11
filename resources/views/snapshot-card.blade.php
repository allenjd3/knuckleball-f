<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $user->name }} · {{ $year }} Snapshot</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f3f4f6;
            display: flex; flex-direction: column; align-items: center; justify-content: flex-start;
            min-height: 100vh; padding: 2rem;
            font-family: 'Inter', sans-serif;
        }
        #card {
            width: 100%; max-width: 900px;
            background: #D93C3F;
            border-radius: 24px;
            padding: 48px 56px;
            color: white;
        }
        .header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 48px;
        }
        .header-label {
            font-size: 12px; font-weight: 700;
            letter-spacing: 0.15em; text-transform: uppercase;
        }
        .year-pill {
            background: white; color: #D93C3F;
            border-radius: 9999px; padding: 8px 20px;
            font-size: 12px; font-weight: 900;
            letter-spacing: 0.15em; text-transform: uppercase;
        }
        .big-stats {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px;
            margin-bottom: 40px;
        }
        .stat-number {
            font-size: 80px; font-weight: 900; line-height: 1;
            font-variant-numeric: tabular-nums;
        }
        .stat-pct { font-size: 40px; font-weight: 900; }
        .stat-label {
            font-size: 11px; font-weight: 700;
            letter-spacing: 0.15em; text-transform: uppercase;
            color: rgba(255,255,255,0.7); margin-top: 12px;
        }
        .divider { border-top: 1px solid rgba(255,255,255,0.2); margin-bottom: 24px; }
        .detail-rows { display: flex; flex-direction: column; gap: 16px; }
        .detail-row { display: flex; justify-content: space-between; align-items: center; font-size: 14px; font-weight: 500; }
        .detail-left { display: flex; align-items: center; gap: 12px; }
        .detail-icon { width: 20px; height: 20px; flex-shrink: 0; opacity: 0.7; }
        .detail-value { font-size: 14px; font-weight: 600; }
        .controls {
            display: flex; gap: 12px;
            margin-top: 20px; max-width: 900px; width: 100%;
            justify-content: flex-end;
        }
        .btn {
            padding: 10px 20px; border-radius: 10px;
            font-size: 14px; font-weight: 600; cursor: pointer; border: none;
            font-family: 'Inter', sans-serif; text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-download { background: #D93C3F; color: white; }
        .btn-back { background: #e5e7eb; color: #111827; }
    </style>
</head>
<body>

<div id="card">
    {{-- Header --}}
    <div class="header">
        <p class="header-label">{{ $user->name }} · All-Time Stats</p>
        <span class="year-pill">{{ $year }} Snapshot</span>
    </div>

    {{-- 4 headline numbers --}}
    <div class="big-stats">
        <div>
            <p class="stat-number">{{ data_get($stats, 'total_sends', 0) }}</p>
            <p class="stat-label">Sends</p>
        </div>
        <div>
            <p class="stat-number">{{ data_get($stats, 'total_returns', 0) }}</p>
            <p class="stat-label">Returns</p>
        </div>
        <div>
            <p class="stat-number">{{ data_get($stats, 'success_rate', 0) }}<span class="stat-pct">%</span></p>
            <p class="stat-label">Success</p>
        </div>
        <div>
            <p class="stat-number">{{ data_get($stats, 'unique_players', 0) }}</p>
            <p class="stat-label">Players</p>
        </div>
    </div>

    {{-- Divider --}}
    <div class="divider"></div>

    {{-- Detail rows --}}
    <div class="detail-rows">
        @if (data_get($stats, 'fastest_return'))
            <div class="detail-row">
                <div class="detail-left">
                    <svg class="detail-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                    Fastest Return
                </div>
                <span class="detail-value">{{ data_get($stats, 'fastest_return.player_name') }} · {{ data_get($stats, 'fastest_return.days') }} days</span>
            </div>
        @endif
        @if (data_get($stats, 'longest_wait'))
            <div class="detail-row">
                <div class="detail-left">
                    <svg class="detail-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" /></svg>
                    Longest Wait
                </div>
                <span class="detail-value">{{ data_get($stats, 'longest_wait.player_name') }} · {{ data_get($stats, 'longest_wait.days') }} days</span>
            </div>
        @endif
        @if (data_get($stats, 'favorite_team'))
            <div class="detail-row">
                <div class="detail-left">
                    <svg class="detail-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" /></svg>
                    Favorite Team
                </div>
                <span class="detail-value">{{ data_get($stats, 'favorite_team') }}</span>
            </div>
        @endif
        @if (data_get($stats, 'favorite_category'))
            <div class="detail-row">
                <div class="detail-left">
                    <svg class="detail-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                    Top Category
                </div>
                <span class="detail-value">{{ data_get($stats, 'favorite_category') }}</span>
            </div>
        @endif
        @if (data_get($stats, 'most_reacted_return'))
            <div class="detail-row">
                <div class="detail-left">
                    <svg class="detail-icon" fill="white" viewBox="0 0 24 24"><path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001z" /></svg>
                    Most Reacted
                </div>
                <span class="detail-value">{{ data_get($stats, 'most_reacted_return.player_name') }} · {{ data_get($stats, 'most_reacted_return.reaction_count') }} reactions</span>
            </div>
        @endif
        @if (data_get($stats, 'first_send'))
            <div class="detail-row">
                <div class="detail-left">
                    <svg class="detail-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12zm0 0h7.5" /></svg>
                    First Send
                </div>
                <span class="detail-value">
                    @php $firstSend = data_get($stats, 'first_send'); @endphp
                    {{ ($firstSend instanceof \Carbon\Carbon ? $firstSend : \Carbon\Carbon::parse($firstSend))->format('M j, Y') }}
                </span>
            </div>
        @endif
        @if (data_get($stats, 'most_recent_return'))
            <div class="detail-row">
                <div class="detail-left">
                    <svg class="detail-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" /></svg>
                    Latest Return
                </div>
                <span class="detail-value">
                    @php $latestReturn = data_get($stats, 'most_recent_return'); @endphp
                    {{ ($latestReturn instanceof \Carbon\Carbon ? $latestReturn : \Carbon\Carbon::parse($latestReturn))->format('M j, Y') }}
                </span>
            </div>
        @endif
        @if (data_get($stats, 'total_sends', 0) === 0)
            <p style="color:rgba(255,255,255,0.5); font-size:14px; text-align:center; padding: 8px 0;">No activity yet for {{ $year }}.</p>
        @endif
    </div>
</div>

<div class="controls">
    <a href="{{ route('users.profile', $user) }}" class="btn btn-back">← Back</a>
    <button class="btn btn-download" onclick="downloadCard()">Download Image</button>
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

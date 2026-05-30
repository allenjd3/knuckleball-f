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

<x-stats-card :stats="$stats" id="card" class="w-full max-w-[900px]">
    <x-slot:header>
        <p class="text-[10px] sm:text-xs font-bold tracking-widest uppercase">{{ $user->name }} · All-Time Stats</p>
        <span class="bg-white rounded-full px-4 sm:px-5 py-1.5 sm:py-2 text-[10px] sm:text-xs font-black tracking-widest uppercase shrink-0" style="color:#D93C3F;">{{ $year }} Snapshot</span>
    </x-slot:header>
</x-stats-card>

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

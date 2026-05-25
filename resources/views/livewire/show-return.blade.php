@php
    $ogPlayer  = data_get($meta, 'player', 'TTM Return');
    $ogDays    = data_get($meta, 'turnaround_days');
    $ogUser    = data_get($meta, 'user', '');
    $ogTitle   = $ogPlayer . ($ogDays !== null ? " — {$ogDays} Day Return" : ' — TTM Return');
    $ogDesc    = $ogUser . ' got a TTM return from ' . $ogPlayer . ($ogDays !== null ? " in {$ogDays} days" : '') . '. Track your own returns on Knuckleball.';
    $ogUrl     = route('returns.show', $mail);
    $ogImage   = $this->ogImageUrl();
@endphp

@push('page-title'){{ $ogTitle }} — @endpush

@push('head')
    <meta property="og:type"        content="website" />
    <meta property="og:url"         content="{{ $ogUrl }}" />
    <meta property="og:title"       content="{{ $ogTitle }}" />
    <meta property="og:description" content="{{ $ogDesc }}" />
    @if ($ogImage)
    <meta property="og:image"       content="{{ $ogImage }}" />
    <meta property="og:image:width" content="1080" />
    <meta property="og:image:height" content="1080" />
    <meta property="og:image:type"  content="image/jpeg" />
    @endif
    <meta property="og:site_name"   content="Knuckleball" />

    <meta name="twitter:card"        content="summary_large_image" />
    <meta name="twitter:title"       content="{{ $ogTitle }}" />
    <meta name="twitter:description" content="{{ $ogDesc }}" />
    @if ($ogImage)
    <meta name="twitter:image"       content="{{ $ogImage }}" />
    @endif

    <link rel="canonical" href="{{ $ogUrl }}" />
@endpush

<div class="min-h-screen bg-gray-50">
    <div class="max-w-2xl mx-auto py-10 px-4">

        {{-- Hero card image --}}
        @php
            $heroUrl   = $this->squarePreviewUrl();
            $cardPhoto = data_get($meta, 'card_photos.0');
        @endphp

        @if ($heroUrl)
            <div class="rounded-3xl overflow-hidden shadow-2xl mb-8">
                <img src="{{ $heroUrl }}" alt="{{ data_get($meta, 'player') }}"
                     class="w-full object-cover" />
            </div>
        @elseif ($cardPhoto)
            <div class="rounded-3xl overflow-hidden shadow-2xl mb-8">
                <img src="{{ Storage::url($cardPhoto) }}" alt="{{ data_get($meta, 'player') }}"
                     class="w-full max-h-96 object-cover" />
            </div>
        @endif

        {{-- Info card --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6 space-y-4">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-bold uppercase tracking-widest text-[#D93C3F]">TTM Return</span>
            </div>

            <h1 class="text-3xl font-bold text-gray-900">{{ data_get($meta, 'player') }}</h1>

            <div class="flex items-center flex-wrap gap-3">
                @if (data_get($meta, 'turnaround_days') !== null)
                    <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold text-white"
                          style="background-color:#D93C3F;">
                        {{ data_get($meta, 'turnaround_days') }} Days
                    </span>
                @endif

                @php
                    $feeAmount = null;
                    $feeMaterial = $mail->feeMaterials->first();
                    if ($feeMaterial && $mail->signer) {
                        $fee = $mail->signer->fees()
                            ->where('fee_material_id', $feeMaterial->id)
                            ->whereNotNull('published_at')
                            ->first();
                        $feeAmount = $fee?->amount ?? null;
                    }
                @endphp
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-600">
                    {{ ($feeAmount && $feeAmount > 0) ? 'Fee: $' . $feeAmount : 'No Fee' }}
                </span>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-gray-50">
                <img src="{{ data_get($meta, 'photo') }}" alt="{{ data_get($meta, 'user') }}"
                     class="size-9 rounded-full object-cover" />
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ data_get($meta, 'user') }} <span class="font-normal text-gray-500">Got it back!</span></p>
                    @if (data_get($meta, 'date_returned'))
                        @php
                            $sentDate     = data_get($meta, 'date_sent');
                            $returnedDate = data_get($meta, 'date_returned');
                            $sentFmt      = $sentDate instanceof \Carbon\Carbon ? $sentDate->format('M j, Y') : (is_string($sentDate) ? \Carbon\Carbon::parse($sentDate)->format('M j, Y') : null);
                            $returnFmt    = $returnedDate instanceof \Carbon\Carbon ? $returnedDate->format('M j, Y') : (is_string($returnedDate) ? \Carbon\Carbon::parse($returnedDate)->format('M j, Y') : null);
                        @endphp
                        <p class="text-xs text-gray-400">
                            {{ $sentFmt }} → {{ $returnFmt }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Share section --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
            <p class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-4">Share This Return</p>

            @if ($shareOpen)
                {{-- Card preview + downloads --}}
                @if ($heroUrl)
                    <div class="rounded-xl overflow-hidden border border-gray-100 mb-4">
                        <img src="{{ $heroUrl }}" alt="Share card preview" class="w-full" />
                    </div>
                @endif

                <div class="flex flex-col gap-2">
                    <a href="{{ $this->squareUrl() }}" download
                       class="flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90"
                       style="background-color:#D93C3F;">
                        <x-heroicon-o-arrow-down-tray class="size-4" />
                        Download Square (1080×1080)
                    </a>
                    <a href="{{ $this->storyUrl() }}" download
                       class="flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors">
                        <x-heroicon-o-arrow-down-tray class="size-4" />
                        Download Story (1080×1920)
                    </a>

                    <div x-data="{ copied: false }"
                         class="flex items-center gap-2 border border-gray-200 rounded-xl px-4 py-3">
                        <input type="text" readonly value="{{ $publicUrl }}"
                               class="flex-1 text-xs text-gray-500 bg-transparent border-0 outline-none min-w-0" />
                        <button @click="navigator.clipboard.writeText('{{ $publicUrl }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="shrink-0 text-sm font-semibold text-[#D93C3F] hover:text-red-700 transition-colors">
                            <span x-show="!copied">Copy Link</span>
                            <span x-show="copied" class="text-green-600">Copied!</span>
                        </button>
                    </div>
                </div>

            @else
                <button wire:click="openShare"
                        class="flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90 w-full justify-center"
                        style="background-color:#D93C3F;">
                    <x-heroicon-o-share class="size-4" />
                    Share Your Return
                </button>
            @endif
        </div>

        {{-- Card photos --}}
        @if (! empty(data_get($meta, 'card_photos', [])))
            <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
                <p class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-4">Signed Cards</p>
                <div class="grid grid-cols-2 gap-3">
                    @foreach (data_get($meta, 'card_photos', []) as $photo)
                        <img src="{{ Storage::url($photo) }}" alt="Signed card"
                             class="w-full aspect-[3/4] object-cover rounded-xl bg-gray-50" />
                    @endforeach
                </div>
            </div>
        @endif

        {{-- CTA for non-users --}}
        @guest
            <div class="rounded-2xl p-6 text-center" style="background-color:#0F1117;">
                <p class="text-lg font-bold text-white mb-1">Track your own TTM returns</p>
                <p class="text-sm text-gray-400 mb-5">Join thousands of collectors on Knuckleball.</p>
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white transition-opacity hover:opacity-90"
                   style="background-color:#D93C3F;">
                    Join Knuckleball — It's Free
                </a>
            </div>
        @endguest

    </div>
</div>

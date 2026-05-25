<div>
    <form wire:submit="submit"
          class="flex items-center gap-3 px-3 py-2.5 border border-gray-200 rounded-full bg-white">
        <img src="{{ auth()->user()->profile_photo_url }}"
             class="size-9 rounded-full shrink-0 object-cover" />
        <input
            wire:model="body"
            wire:keydown.enter.prevent="submit"
            type="text"
            placeholder="Log a send, return, or share an update..."
            class="flex-1 min-w-0 bg-transparent border-0 outline-none text-sm text-gray-700 placeholder-gray-400"
        />
        <button type="submit"
                class="shrink-0 px-5 py-2 text-sm font-semibold text-white rounded-full transition-opacity hover:opacity-90"
                style="background-color:#C0544F;">
            Post
        </button>
    </form>

    @error('body')
        <p class="text-xs text-red-500 mt-1 pl-4">{{ $message }}</p>
    @enderror

    <div class="flex items-center gap-5 mt-2 px-4">
        {{ $this->logSendAction }}
        {{ $this->logReturnAction }}
    </div>

    <x-filament-actions::modals />

    {{-- Share return card prompt --}}
    @if ($shareOpen && $shareMailId)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center px-4 py-6 overflow-y-auto"
             wire:key="share-modal-{{ $shareMailId }}">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md" @click.stop>

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Share Your Return!</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Download a card or copy the link to share.</p>
                    </div>
                    <button wire:click="closeShare"
                            class="size-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                        <x-heroicon-o-x-mark class="size-5" />
                    </button>
                </div>

                <div class="p-6 space-y-4">

                    {{-- Card preview --}}
                    @php $preview = $this->sharePreviewUrl(); @endphp
                    @if ($preview)
                        <div class="rounded-2xl overflow-hidden border border-gray-100 shadow-md">
                            <img src="{{ $preview }}" alt="Return card preview" class="w-full" />
                        </div>
                    @else
                        <div class="rounded-2xl bg-gray-900 flex items-center justify-center" style="aspect-ratio:1;">
                            <div class="animate-spin size-8 border-2 border-white/30 border-t-white rounded-full"></div>
                        </div>
                    @endif

                    {{-- Download buttons --}}
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ $this->shareSquareUrl() }}" download
                           class="flex flex-col items-center gap-1 px-4 py-3 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90 text-center"
                           style="background-color:#D93C3F;">
                            <x-heroicon-o-arrow-down-tray class="size-4" />
                            <span>Square</span>
                            <span class="text-xs font-normal opacity-80">1080 × 1080</span>
                        </a>
                        <a href="{{ $this->shareStoryUrl() }}" download
                           class="flex flex-col items-center gap-1 px-4 py-3 rounded-xl text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors text-center">
                            <x-heroicon-o-arrow-down-tray class="size-4" />
                            <span>Story</span>
                            <span class="text-xs font-normal text-gray-400">1080 × 1920</span>
                        </a>
                    </div>

                    {{-- Copy link --}}
                    <div x-data="{ copied: false }"
                         class="flex items-center gap-2 border border-gray-200 rounded-xl px-4 py-3 bg-gray-50">
                        <x-heroicon-o-link class="size-4 text-gray-400 shrink-0" />
                        <input type="text" readonly value="{{ $this->sharePublicUrl() }}"
                               class="flex-1 text-xs text-gray-500 bg-transparent border-0 outline-none min-w-0" />
                        <button @click="navigator.clipboard.writeText('{{ $this->sharePublicUrl() }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                class="shrink-0 text-sm font-bold transition-colors"
                                :class="copied ? 'text-green-600' : 'text-[#D93C3F]'">
                            <span x-show="!copied">Copy Link</span>
                            <span x-show="copied">Copied!</span>
                        </button>
                    </div>

                    {{-- View page link --}}
                    <div class="text-center">
                        <a href="{{ $this->sharePublicUrl() }}" target="_blank"
                           class="text-xs text-gray-400 hover:text-gray-600 hover:underline transition-colors">
                            View public return page →
                        </a>
                    </div>

                </div>
            </div>
        </div>
    @endif

    {{-- Set connection prompt --}}
    @if ($setPromptOpen && count($setPromptEntries) > 0)
        <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="size-10 bg-amber-50 rounded-full flex items-center justify-center shrink-0">
                        <x-heroicon-o-squares-2x2 class="size-5 text-amber-600" />
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">Got it in a set!</p>
                        <p class="text-xs text-gray-500">This player is in {{ count($setPromptEntries) }} of your Need It {{ Str::plural('list', count($setPromptEntries)) }}.</p>
                    </div>
                </div>

                <div class="space-y-2 mb-5">
                    @foreach ($setPromptEntries as $entry)
                        <div class="flex items-center gap-2 text-sm text-gray-700">
                            <x-heroicon-o-check-circle class="size-4 text-amber-500 shrink-0" />
                            <a href="{{ $entry['set_path'] }}" class="hover:underline font-medium">{{ $entry['set_name'] }}</a>
                        </div>
                    @endforeach
                </div>

                <div class="flex gap-2">
                    <button wire:click="markSetEntriesSigned"
                            class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition-colors"
                            style="background-color:#D93C3F;">
                        Mark Signed ✓
                    </button>
                    <button wire:click="dismissSetPrompt"
                            class="px-4 py-2.5 text-sm font-medium text-gray-500 hover:text-gray-700 border border-gray-200 rounded-xl transition-colors">
                        Skip
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

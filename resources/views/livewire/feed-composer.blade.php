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
</div>

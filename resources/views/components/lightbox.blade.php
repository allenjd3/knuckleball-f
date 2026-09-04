<div
    x-data="{ open: false, src: '', alt: '' }"
    x-on:open-lightbox.window="open = true; src = $event.detail.src; alt = $event.detail.alt ?? ''"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    x-trap.inert.noscroll="open"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/90 p-4 sm:p-10"
    style="display: none;"
    x-transition.opacity
    @click.self="open = false"
>
    <button
        type="button"
        @click="open = false"
        class="absolute top-4 right-4 sm:top-6 sm:right-6 text-white/80 hover:text-white transition-colors"
        aria-label="Close"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
    </button>

    <img :src="src" :alt="alt" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl" />
</div>

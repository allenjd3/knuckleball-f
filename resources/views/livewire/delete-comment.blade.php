<div>
    @if ($canDelete)
        <button
            style="--c-600: var(--primary-600); --c-400: var(--primary-400)"
            wire:click="delete"
            class="text-custom-600 dark:text-custom-400 hover:underline font-bold"
        >
            Delete
        </button>
    @endif
</div>

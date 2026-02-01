<div>
    @if ($canDelete)
        <button
            style="--color-600: var(--primary-600); --color-400: var(--primary-400)"
            wire:click="delete"
            class="text-custom-600 dark:text-custom-400 hover:underline font-bold"
        >
            Delete
        </button>
    @endif
</div>

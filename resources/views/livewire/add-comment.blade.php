<div>
    @can('create', App\Models\Comment::class)
        <div class="bg-white rounded-2xl border border-gray-100 p-4">
            <div class="flex items-start gap-3">
                <img src="{{ auth()->user()->profile_photo_url }}"
                     class="size-9 rounded-full shrink-0 object-cover mt-1" />
                <form wire:submit="save" class="flex-1 min-w-0 space-y-3">
                    {{ $this->form }}
                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-4 py-2 text-sm font-semibold text-white rounded-full transition-opacity hover:opacity-90"
                                style="background-color:#D93C3F;">
                            Post
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
</div>

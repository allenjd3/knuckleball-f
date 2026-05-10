<div>
    <button wire:click="toggle" class="flex items-center gap-1 text-sm text-gray-400 hover:text-gray-600 transition-colors">
        <x-heroicon-o-chat-bubble-left class="size-4" />
        @if ($this->commentCount > 0)
            {{ $this->commentCount }} {{ Str::plural('comment', $this->commentCount) }}
        @else
            Add a comment
        @endif
    </button>

    @if ($expanded)
        <div class="mt-4 space-y-4">
            @foreach ($this->comments as $comment)
                <div class="grid grid-cols-[40px_1fr] gap-2">
                    <img src="{{ $comment->user->profile_photo_url }}" alt="{{ $comment->user->name }}" class="size-8 rounded-full" />
                    <div>
                        <p class="text-sm font-bold">{{ $comment->user->name }}</p>
                        <p class="text-sm text-gray-700 mt-0.5">{{ $comment->body }}</p>
                    </div>
                </div>
            @endforeach

            @auth
                <div class="grid grid-cols-[40px_1fr] gap-2 items-center">
                    <img src="{{ auth()->user()->profile_photo_url }}" class="size-8 rounded-full" />
                    <div class="flex items-center gap-2">
                        <input
                            wire:model="body"
                            wire:keydown.enter="submit"
                            type="text"
                            placeholder="Add a comment…"
                            class="flex-1 text-sm border border-gray-200 rounded px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-gray-300"
                        />
                        @if ($body)
                            <button wire:click="submit" class="text-sm font-semibold hover:underline">Post</button>
                        @endif
                    </div>
                </div>
                @error('body')
                    <p class="text-sm text-red-500 pl-10">{{ $message }}</p>
                @enderror
            @endauth
        </div>
    @endif
</div>

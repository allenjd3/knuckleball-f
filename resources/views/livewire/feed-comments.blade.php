<div>
    <button wire:click="toggle" class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-600 transition-colors">
        <x-heroicon-o-chat-bubble-left class="size-4" />
        @if ($this->commentCount > 0)
            <span>{{ $this->commentCount }}</span>
        @else
            <span>Reply</span>
        @endif
    </button>

    @if ($expanded)
        <div class="mt-4 space-y-3 border-t border-gray-100 pt-4">
            @foreach ($this->comments as $comment)
                <div class="flex items-start gap-2.5">
                    <img src="{{ $comment->user->profile_photo_url }}" alt="{{ $comment->user->name }}"
                         class="size-7 rounded-full shrink-0 object-cover" />
                    <div class="flex-1 min-w-0">
                        <div class="bg-gray-50 rounded-xl px-3 py-2">
                            <p class="text-xs font-semibold text-gray-800">{{ $comment->user->name }}</p>
                            <p class="text-sm text-gray-700 mt-0.5">{{ $comment->body }}</p>
                        </div>
                        <p class="text-xs text-gray-400 mt-1 ml-1">{{ $comment->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @endforeach

            @auth
                <div class="flex items-center gap-2.5">
                    <img src="{{ auth()->user()->profile_photo_url }}" class="size-7 rounded-full shrink-0 object-cover" />
                    <div class="flex-1 flex items-center gap-2">
                        <input
                            wire:model="body"
                            wire:keydown.enter="submit"
                            type="text"
                            placeholder="Write a reply…"
                            class="flex-1 text-sm border border-gray-200 rounded-full px-3 py-1.5 bg-gray-50 focus:outline-none focus:ring-1 focus:ring-gray-300 focus:bg-white"
                        />
                        @if ($body)
                            <button wire:click="submit" class="text-sm font-semibold text-[#D93C3F] hover:underline shrink-0">Post</button>
                        @endif
                    </div>
                </div>
                @error('body')
                    <p class="text-xs text-red-500 pl-9">{{ $message }}</p>
                @enderror
            @endauth
        </div>
    @endif
</div>

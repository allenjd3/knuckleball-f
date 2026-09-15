<div class="w-full">
    {{-- Toggle row --}}
    <div class="flex items-center justify-between">
        @if ($this->commentCount > 0 && ! $expanded)
            <button wire:click="toggle" class="text-xs text-gray-400 hover:text-gray-600 transition-colors">{{ $this->commentCount }} {{ Str::plural('comment', $this->commentCount) }}</button>
        @else
            <span></span>
        @endif

        <button wire:click="toggle"
                class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-600 transition-colors">
            <x-heroicon-o-chat-bubble-left class="size-4" />
            <span>{{ $expanded ? 'Hide' : 'Comment' }}</span>
        </button>
    </div>

    @if ($expanded)
        <div class="mt-3 pt-3 border-t border-gray-100 space-y-3">
            @foreach ($this->comments as $comment)
                <div wire:key="comment-{{ $comment->id }}">
                    <div class="flex items-start gap-2.5">
                        <img src="{{ $comment->user->profile_photo_url }}" alt="{{ $comment->user->name }}"
                             class="size-7 rounded-full shrink-0 object-cover mt-0.5" />
                        <div class="flex-1 min-w-0">
                            <div class="bg-gray-50 rounded-2xl px-3 py-2">
                                <p class="text-xs font-semibold text-gray-800">{{ $comment->user->name }}</p>
                                <p class="text-sm text-gray-700 mt-0.5 leading-snug">{{ $comment->body }}</p>
                            </div>
                            <div class="flex items-center gap-3 mt-1 ml-1">
                                <p class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</p>
                                @auth
                                    <button wire:click="startReply({{ $comment->id }})" class="text-xs font-semibold text-gray-400 hover:text-gray-600 transition-colors">
                                        Reply
                                    </button>
                                @endauth
                            </div>
                        </div>
                    </div>

                    {{-- Replies --}}
                    @if ($comment->replies->isNotEmpty())
                        <div class="mt-2 ml-9 space-y-2">
                            @foreach ($comment->replies as $reply)
                                <div wire:key="reply-{{ $reply->id }}" class="flex items-start gap-2.5">
                                    <img src="{{ $reply->user->profile_photo_url }}" alt="{{ $reply->user->name }}"
                                         class="size-6 rounded-full shrink-0 object-cover mt-0.5" />
                                    <div class="flex-1 min-w-0">
                                        <div class="bg-gray-50 rounded-2xl px-3 py-2">
                                            <p class="text-xs font-semibold text-gray-800">{{ $reply->user->name }}</p>
                                            <p class="text-sm text-gray-700 mt-0.5 leading-snug">{{ $reply->body }}</p>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-1 ml-1">{{ $reply->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Reply box --}}
                    @if ($replyingTo === $comment->id)
                        <div class="mt-2 ml-9 flex items-center gap-2.5">
                            <img src="{{ auth()->user()->profile_photo_url }}"
                                 class="size-6 rounded-full shrink-0 object-cover" />
                            <div class="flex-1 flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-full px-3 py-1.5 focus-within:border-gray-300 focus-within:bg-white transition-colors">
                                <input
                                    wire:model="replyBody"
                                    wire:keydown.enter="submitReply"
                                    wire:keydown.escape="cancelReply"
                                    type="text"
                                    placeholder="Reply to {{ $comment->user->name }}…"
                                    autofocus
                                    class="flex-1 text-sm bg-transparent border-0 outline-none min-w-0 text-gray-700 placeholder-gray-400"
                                />
                                @if ($replyBody)
                                    <button wire:click="submitReply"
                                            class="text-sm font-semibold text-[#D93C3F] hover:text-red-700 transition-colors shrink-0">
                                        Post
                                    </button>
                                @endif
                                <button wire:click="cancelReply" class="text-xs text-gray-400 hover:text-gray-600 transition-colors shrink-0">
                                    Cancel
                                </button>
                            </div>
                        </div>
                        @error('replyBody')
                            <p class="text-xs text-red-500 ml-9 mt-1">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
            @endforeach

            @auth
                <div class="flex items-center gap-2.5 pt-1">
                    <img src="{{ auth()->user()->profile_photo_url }}"
                         class="size-7 rounded-full shrink-0 object-cover" />
                    <div class="flex-1 flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-full px-3 py-1.5 focus-within:border-gray-300 focus-within:bg-white transition-colors">
                        <input
                            wire:model="body"
                            wire:keydown.enter="submit"
                            type="text"
                            placeholder="Write a comment…"
                            class="flex-1 text-sm bg-transparent border-0 outline-none min-w-0 text-gray-700 placeholder-gray-400"
                        />
                        @if ($body)
                            <button wire:click="submit"
                                    class="text-sm font-semibold text-[#D93C3F] hover:text-red-700 transition-colors shrink-0">
                                Post
                            </button>
                        @endif
                    </div>
                </div>
                @error('body')
                    <p class="text-xs text-red-500 pl-9">{{ $message }}</p>
                @enderror
            @else
                <p class="text-xs text-gray-400 text-center py-1">
                    <a href="{{ route('login') }}" class="underline hover:text-gray-600">Sign in</a> to leave a comment.
                </p>
            @endauth
        </div>
    @endif
</div>

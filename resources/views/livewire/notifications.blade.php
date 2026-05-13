<div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
    {{-- Bell button --}}
    <button @click="open = !open"
            class="relative flex items-center justify-center size-9 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors">
        <x-heroicon-o-bell class="size-5" />
        @if ($this->unreadCount > 0)
            <span class="absolute top-0.5 right-0.5 flex items-center justify-center min-w-[16px] h-4 px-0.5 bg-[#D93C3F] text-white text-[10px] font-bold rounded-full">
                {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown panel --}}
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 top-11 w-80 bg-white border border-gray-200 rounded-2xl shadow-2xl overflow-hidden z-50">

        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <p class="font-semibold text-sm text-gray-900">Notifications</p>
            @if ($this->unreadCount > 0)
                <button wire:click="markAllRead"
                        class="text-xs text-[#D93C3F] hover:underline font-medium">
                    Mark all read
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto divide-y divide-gray-50">
            @forelse ($this->notifications as $notification)
                @php $data = $notification->data; @endphp
                <div wire:click="markRead('{{ $notification->id }}')"
                     class="flex items-start gap-3 px-4 py-3 cursor-pointer transition-colors {{ $notification->read_at ? 'bg-white hover:bg-gray-50' : 'bg-red-50/40 hover:bg-red-50' }}">

                    {{-- Avatar --}}
                    @php $photo = data_get($data, 'follower_photo') ?? data_get($data, 'reactor_photo') ?? data_get($data, 'commenter_photo') ?? ''; @endphp
                    @if ($photo)
                        <img src="{{ $photo }}" class="size-8 rounded-full shrink-0 object-cover mt-0.5" />
                    @else
                        <div class="size-8 rounded-full bg-gray-100 shrink-0 flex items-center justify-center mt-0.5">
                            <x-heroicon-o-bell class="size-4 text-gray-400" />
                        </div>
                    @endif

                    <div class="flex-1 min-w-0">
                        @if (data_get($data, 'type') === 'new_follower')
                            <p class="text-sm text-gray-800 leading-snug">
                                <a href="{{ route('users.profile', ['user' => data_get($data, 'follower_slug')]) }}"
                                   class="font-semibold hover:underline">{{ data_get($data, 'follower_name') }}</a>
                                started following you.
                            </p>
                        @elseif (data_get($data, 'type') === 'new_reaction')
                            <p class="text-sm text-gray-800 leading-snug">
                                <a href="{{ route('users.profile', ['user' => data_get($data, 'reactor_slug')]) }}"
                                   class="font-semibold hover:underline">{{ data_get($data, 'reactor_name') }}</a>
                                reacted {{ data_get($data, 'emoji') }} to your post.
                            </p>
                        @elseif (data_get($data, 'type') === 'new_comment')
                            <p class="text-sm text-gray-800 leading-snug">
                                <a href="{{ route('users.profile', ['user' => data_get($data, 'commenter_slug')]) }}"
                                   class="font-semibold hover:underline">{{ data_get($data, 'commenter_name') }}</a>
                                commented: <span class="text-gray-500 italic">"{{ data_get($data, 'body') }}"</span>
                            </p>
                        @elseif (data_get($data, 'type') === 'pack_player_added')
                            <p class="text-sm text-gray-800 leading-snug">
                                <span class="font-semibold">{{ data_get($data, 'player_name') }}</span>
                                was added to
                                <a href="{{ route('packs.show', data_get($data, 'pack_slug')) }}"
                                   class="font-semibold hover:underline">{{ data_get($data, 'pack_name') }}</a>.
                            </p>
                        @elseif (isset($data['event_id']) && isset($data['player_name']))
                            <p class="text-sm text-gray-800 leading-snug">
                                <span class="font-semibold">Signing alert:</span>
                                <a href="{{ data_get($data, 'event_path', '#') }}" class="hover:underline">{{ data_get($data, 'player_name') }}</a>
                                — {{ data_get($data, 'event_name') }}
                                @if(data_get($data, 'city')) · {{ data_get($data, 'city') }}@if(data_get($data, 'state')), {{ data_get($data, 'state') }}@endif @endif
                            </p>
                        @elseif (isset($data['event_id']))
                            <p class="text-sm text-gray-800 leading-snug">
                                <span class="font-semibold">Card show near you:</span>
                                <a href="{{ data_get($data, 'event_path', '#') }}" class="hover:underline">{{ data_get($data, 'event_name') }}</a>
                                @if(data_get($data, 'city')) · {{ data_get($data, 'city') }}@if(data_get($data, 'state')), {{ data_get($data, 'state') }}@endif @endif
                            </p>
                        @else
                            <p class="text-sm text-gray-800">New notification</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>

                    @if (! $notification->read_at)
                        <div class="size-2 rounded-full bg-[#D93C3F] shrink-0 mt-2"></div>
                    @endif
                </div>
            @empty
                <div class="px-4 py-10 text-center text-gray-400">
                    <x-heroicon-o-bell class="size-8 mx-auto mb-2 opacity-30" />
                    <p class="text-sm">No notifications yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

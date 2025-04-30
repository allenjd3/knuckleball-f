<div class="max-w-5xl mx-auto flex flex-col md:flex-row gap-8 mt-8">
    <aside class="flex flex-col justify-center items-center w-max">
        <div class="rounded-full size-32 overflow-hidden">
            <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}" class="object-cover w-full h-full"/>
        </div>

        <div class="font-bold text-center mt-4">
        <div>{{ $this->user->name }}</div>
        <div class="text-xs space-y-4">
            <div>Joined: {{ $this->user->created_at?->format('M d, Y') }}</div>
            <div>Following: {{ $this->user->following_count }} Followers: {{ $this->user->followers_count }}</div>
            @if (auth()->check() && $this->user->id !== auth()->user()?->id)
                @if (! $this->isFollowing)
                    <button wire:click="follow">Follow</button>
                @else
                    <button wire:click="unfollow">Unfollow</button>
                @endif
            @endif
        </div>
    </aside>
    <div class="divide-y w-full">
        @foreach ($this->feeds as $feed)
            <x-dynamic-component :component="$feed->componentName()" :$feed />
        @endforeach
        {{ $this->feeds->links() }}
    </div>
</div>

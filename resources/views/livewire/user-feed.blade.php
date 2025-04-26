<div class="max-w-5xl mx-auto flex mt-8">
    <aside class="hidden sm:block sm:w-64">
        <a href="{{ auth()->check() ? route('users.profile', auth()->user()) : route('login') }}">My Feed</a>
    </aside>
    <main class="divide-y w-full">
    @foreach ($feeds as $feed)
        <x-dynamic-component :component="$feed->componentName()" :$feed />
    @endforeach
    @if ($hasMore)
    <x-filament::button
        wire:click="loadMore"
    >
        Next Page
    </x-filament::button>
    @endif

    </main>
</div>

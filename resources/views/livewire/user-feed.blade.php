<div wire:poll.visible.10s class="max-w-5xl mx-auto flex mt-8">
    <aside class="hidden sm:block sm:w-64">
        <a href="{{ auth()->check() ? route('users.profile', auth()->user()) : route('login') }}">My Feed</a>
    </aside>
    <main class="divide-y w-full">
    <livewire:add-comment />
    @foreach ($this->feeds as $feed)
        <x-dynamic-component :component="$feed->componentName()" :$feed />
    @endforeach
    {{ $this->feeds->links() }}
    </main>
</div>

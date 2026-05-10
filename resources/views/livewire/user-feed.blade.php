<div wire:poll.visible.10s class="max-w-5xl mx-auto flex flex-col sm:flex-row mt-8">
    <aside class="w-full sm:w-64">
        <a href="{{ auth()->check() ? route('users.profile', auth()->user()) : route('login') }}">My Feed</a>
    </aside>
    <main class="flex flex-col gap-3 w-full">
    <livewire:add-comment />
    @foreach ($this->feeds as $feed)
        <x-dynamic-component :component="$feed->componentName()" :$feed />
    @endforeach
    {{ $this->feeds->links() }}
    </main>
</div>

<div class="max-w-5xl mx-auto py-12 px-4">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl">Community Want Lists</h1>
            <p class="text-sm text-gray-500 mt-1">Public lists shared by other collectors</p>
        </div>
        @auth
            <a href="{{ route('wantLists.index') }}" class="text-sm underline hover:text-gray-700">My Lists</a>
        @endauth
    </div>

    {{ $this->table }}
</div>

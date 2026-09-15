<div class="divide-y divide-gray-100 -mx-6 -my-4">
    @forelse ($users as $user)
        <a href="{{ $user->path() }}" class="flex items-center gap-3 px-6 py-3 hover:bg-gray-50 transition-colors">
            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="size-10 rounded-full object-cover shrink-0" />
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $user->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ '@' . $user->handle }}</p>
            </div>
        </a>
    @empty
        <p class="text-sm text-gray-500 text-center py-8">Nobody here yet.</p>
    @endforelse
</div>

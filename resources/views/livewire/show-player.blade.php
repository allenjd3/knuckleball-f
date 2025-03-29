<div class="max-w-7xl mx-auto py-12">
    <div class="flex flex-col md:flex-row gap-8 justify-center max-w-3xl mx-auto">
        <div class="mx-auto">
            @if ($this->player->media?->url)
                <img src="{{ Storage::url($this->player->media?->url) }}" alt="{{ $player->name }}" class="size-44 rounded-full" />
            @else
                <div class="flex size-44 border-4 border-dashed border-gray-200 rounded-full text-center text-gray-500 justify-center items-center">
                    <p class="">No photo</p>
                </div>
            @endif
        </div>
        <div class="flex-1">
            <div class="flex gap-2 items-center">
                <h1 class="text-3xl">{{ $player->name }}</h1>
                @php
                $actions = [];
                if (auth()->user()?->can('update', $player)) {
                    array_push($actions, $this->createFee);
                }

                if (auth()->user()?->can('assign', App\Models\Tag::class)) {
                    array_push($actions, $this->associateTag);
                }
                @endphp

                @if (count($actions))
                    <x-filament-actions::group
                        :actions="$actions"
                        label="Player Actions"
                        icon="heroicon-o-plus-circle"
                        tooltip="Player Actions"
                    />
                @endif
            </div>
            <div class="border-b border-black"></div>
            <div class="mb-4 text-lg">
                @if ($player->response_rate)
                    {{ $player->response_rate }}
                @else
                    Too few responses for calculating response rate
                @endif
                @if ($player->fees_required)
                    | Fees Required
                @endif
            </div>
            <div class="mb-4 p-2">
                <h3 class="font-bold">Address:</h3>
                @if ($player->address?->exists)
                    @can('viewAny', App\Models\Address::class)
                        <p>{{ $player->address?->address_1 }}</p>
                        <p>{{ $player->address?->address_2 }}</p>
                        <p>{{ $player->address?->city }}, {{ $player->address?->state }}</p>
                        <p>{{ $player->address?->postal_code }}</p>
                    @else
                        <div class="border-4 border-dashed border-gray-200 mb-2 rounded-xl h-8 w-full">&nbsp;</div>
                        <div class="border-4 border-dashed border-gray-200 rounded-xl h-8 w-full">&nbsp;</div>
                        <p>Only authorized users can view addresses.
                        @guest
                            <a href="{{ route('login') }}" class="font-bold hover:underline">Login</a>
                        @endguest
                        </p>
                    @endcan
                @else
                    <p class="p-8 border-4 rounded-lg border-gray-200 border-dashed text-gray-500">This player doesn't have an address yet.</p>
                    @can('update', $player)
                        <div class="my-4">{{ $this->createAddress }}</div>
                    @endcan
                @endif

                @can('update', $player)
                    <x-filament-actions::modals />
                @endcan
            </div>
            <div>
                @if (auth()->check() && $this->tags->count())
                    <h3 class="font-bold px-2">Tags:</h3>
                    <div class="flex flex-wrap">
                        @foreach ($this->tags as $tag)
                            <x-filament::button
                                class="tag {{ $tag->slug }} {{ is_null($tag->pivot->approved_at) ? 'opacity-50' : '' }}"
                                :x-tooltip.raw="is_null($tag->pivot->approved_at) ? 'Pending Approval' : $tag->description"
                            >
                                {{ $tag->label }}
                            </x-filament::button>
                        @endforeach
                    </div>
                @endif
            </div>
            <div>
                <h3 class="font-bold px-2">Fees:</h3>
                <div class="flex divide-x divide-black">
                    @forelse ($this->fees as $fee)
                        <p class="px-2">${{ $fee->amount }} per {{ str($fee->feeMaterial->name) }}</p>
                    @empty
                        <p class="px-2">No fees yet!</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <h2 class="text-3xl">Recent TTM</h2>
        {{ $this->table }}
    </div>
</div>

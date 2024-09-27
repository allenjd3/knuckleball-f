<div class="max-w-7xl mx-auto py-12">
    <div class="flex flex-col md:flex-row gap-8 justify-center max-w-3xl mx-auto">
        <div class="mx-auto">
            @if ($this->player->media?->url)
                <img src="{{ url($this->player->media?->url ?? '#') }}" alt="{{ $player->name }}" class="size-44 rounded-full" />
            @else
                <div class="flex size-44 border-4 border-dashed border-gray-200 rounded-full text-center text-gray-500 justify-center items-center">
                    <p class="">No photo</p>
                </div>
            @endif
        </div>
        <div class="flex-1">
            <h1 class="text-3xl">{{ $player->name }}</h1>
            <div class="border-b border-black"></div>
            <div class="mb-4 text-lg">98 % Response Rate | Fees Required</div>
            <div class="mb-4 p-2">
                <h3 class="font-bold">Address:</h3>
                @if ($player->address?->exists)
                    <p>{{ $player->address?->address_1 }}</p>
                    <p>{{ $player->address?->address_2 }}</p>
                    <p>{{ $player->address?->city }}, {{ $player->address?->state }}</p>
                    <p>{{ $player->address?->postal_code }}</p>
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
                <h3 class="font-bold px-2">Fees:</h3>
                <div class="flex divide-x divide-black">
                    @forelse ($this->fees as $fee)
                        <p class="px-2">{{ str($fee->feeMaterial->name)->plural() }} {{ $fee->amount }} per {{ str($fee->feeMaterial->name) }}</p>
                    @empty
                        <p class="px-2">No fees yet!</p>
                    @endforelse
                </div>
                @can('update', $player)
                    <div class="flex gap-4">
                        {{ $this->createFee }}
                    </div>
                @endcan
            </div>
        </div>
    </div>
    <div class="mt-4">
        {{ $this->table }}
    </div>
</div>

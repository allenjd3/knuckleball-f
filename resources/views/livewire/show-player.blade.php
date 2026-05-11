<div class="max-w-7xl mx-auto py-12">
    <div class="flex flex-col md:flex-row gap-8 justify-center max-w-3xl mx-auto">
        <div class="mx-auto flex flex-col items-center gap-3 min-w-36">
            @if ($this->player->media?->url)
                <img src="{{ Storage::url($this->player->media?->url) }}" alt="{{ $player->name }}" class="size-44 rounded-full" />
            @else
                <div class="flex size-44 border-4 border-dashed border-gray-200 rounded-full text-center text-gray-500 justify-center items-center">
                    <p class="">No photo</p>
                </div>
            @endif

            @auth
                @php
                    $showEdit = auth()->user()->isSuperAdmin() && auth()->user()->can('update', $player);
                    $showAdmin = auth()->user()->can('update', $player);
                    $showWantList = auth()->user()->isPublished();
                    $hasAny = $showEdit || $showAdmin || $showWantList;
                @endphp
                @if ($hasAny)
                    <div class="w-full bg-white border border-gray-200 rounded-lg overflow-hidden divide-y divide-gray-100">
                        @if ($showEdit)
                            <a href="{{ route('filament.cp.resources.players.edit', $player) }}" class="flex items-center gap-3 px-4 py-3 text-sm font-medium hover:bg-gray-50 w-full">
                                <x-heroicon-o-pencil-square class="size-4 text-gray-500 shrink-0" />
                                Edit player
                            </a>
                        @endif
                        @if ($showAdmin)
                            <button wire:click="mountAction('createFee')" class="flex items-center gap-3 px-4 py-3 text-sm font-medium hover:bg-gray-50 w-full text-left">
                                <x-heroicon-o-currency-dollar class="size-4 text-gray-500 shrink-0" />
                                New fee
                            </button>
                            <button wire:click="mountAction('associateTag')" class="flex items-center gap-3 px-4 py-3 text-sm font-medium hover:bg-gray-50 w-full text-left">
                                <x-heroicon-o-tag class="size-4 text-gray-500 shrink-0" />
                                Associate tag
                            </button>
                        @endif
                        @if ($showWantList)
                            <button wire:click="mountAction('addToPack')" class="flex items-center gap-3 px-4 py-3 text-sm font-medium hover:bg-gray-50 w-full text-left">
                                <x-heroicon-o-bookmark class="size-4 text-gray-500 shrink-0" />
                                Add to Pack
                            </button>
                        @endif
                    </div>
                @endif
            @endauth
        </div>
        <div class="flex-1">
            <div class="flex gap-2 items-center">
                <h1 class="text-3xl">{{ $player->name }}</h1>
            </div>
            <div class="border-b border-black"></div>
            <div class="mb-4 text-lg">
                @if ($player->response_rate)
                    Response Rate: {{ $player->response_rate }}
                @endif
                @if ($player->fees_required)
                    | Fees Required
                @endif
            </div>
            <div class="mb-4 p-2">
                <h3 class="font-bold">Address:</h3>
                @if ($this->address?->exists || $this->address?->exists && $this->hasUnpublishedAddress)
                    @if ($this->player->is_not_deceased)
                        @can('viewAny', App\Models\Address::class)
                            <p>{{ $this->address->address_1 }}</p>
                            <p>{{ $this->address->address_2 }}</p>
                            <p>{{ $this->address->city }}, {{ $this->address->state }}</p>
                            <p>{{ $this->address->postal_code }}</p>
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
                        <div class="border-4 border-dashed border-gray-200 mb-2 rounded-xl h-8 w-full">&nbsp;</div>
                        <div class="border-4 border-dashed border-gray-200 rounded-xl h-8 w-full">&nbsp;</div>
                        <p>This player's address has been archived</p>
                    @endif
                    @if ($this->hasUnpublishedAddress)
                        <p class="font-bold text-green-500">Thanks for your submission. It is in review!</p>
                    @endif
                @elseif ($this->hasUnpublishedAddress)
                    <p class="p-8 border-4 rounded-lg border-green-200 border-dashed text-green-500 font-bold">Thanks for your submission. It is in review!</p>
                @else
                    <div class="text-center p-8 border-4 rounded-lg border-gray-200 border-dashed text-gray-500">
                        <p>This player doesn't have an address yet.</p>
                        <p>Help us find this one! Know where to reach them?</p>
                        @guest
                            <x-filament::button
                                tag="a"
                                class="mt-4"
                                :href="route('login')"
                            >
                                Add the address!
                            </x-filament::button>
                        @else
                            @can('update', $player)
                                <div class="mt-4">{{ $this->createAddress }}</div>
                            @endcan
                        @endguest
                    </div>
                @endif

                @if (isset($unpublishedAddress))
                    <p class="font-bold text-green-500">There is an unpublished address for your review. <a href="{{ route('filament.cp.resources.addresses.edit', ['record' => data_get($unpublishedAddress, 'id')]) }}" class="text-black hover:underline">Review It</a></p>
                @endif

                @if ($this->address?->exists)
                    @can('update', $player)
                        <div class="mt-2">{{ $this->createAddress }}</div>
                    @endcan
                @endif
                <x-filament-actions::modals />
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
                        <div class="px-2">
                            ${{ $fee->amount }} per {{ str($fee->feeMaterial->name) }}
                            @can('update', $this->player)
                                <livewire:edit-fee wire:key="edit-fee-{{ $fee->id }}" :$fee />
                            @endcan
                        </div>
                    @empty
                        <p class="px-2">No fees yet!</p>
                    @endforelse
                </div>
            </div>
            @if ($this->player->note)
                <div>
                    <h3 class="font-bold px-2 mt-8">Note:</h3>
                    <div>{!! nl2br($this->player->note) !!}</div>
                </div>
            @endif
        </div>
    </div>
    <div class="mt-4">
        <h2 class="text-3xl">Recent TTM</h2>
        {{ $this->table }}
    </div>
</div>

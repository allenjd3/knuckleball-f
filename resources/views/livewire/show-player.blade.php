<div class="max-w-7xl mx-auto my-12">
    <h1 class="text-3xl">{{ $player->name }}</h1>
    {{ $player->address?->address_1 }}
    {{ $this->createAddress }}

    <x-filament-actions::modals />
</div>

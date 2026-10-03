<?php

use App\Filament\Resources\AddressRequestResource;
use App\Filament\Resources\AddressRequestResource\Pages\ListAddressRequests;
use App\Livewire\ShowPlayer;
use App\Models\AddressRequest;
use App\Models\Player;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

use function Pest\Laravel\get;

test('admins can see open address requests by default', function () {
    $this->actingAs(User::factory()->isSuperAdmin()->create());

    $open = AddressRequest::factory()->create();
    $fulfilled = AddressRequest::factory()->fulfilled()->create();

    get(AddressRequestResource::getUrl('index'))->assertSuccessful();

    Livewire::test(ListAddressRequests::class)
        ->assertCanSeeTableRecords([$open])
        ->assertCanNotSeeTableRecords([$fulfilled]);
});

test('admins can mark a request fulfilled', function () {
    $this->actingAs(User::factory()->isSuperAdmin()->create());
    $addressRequest = AddressRequest::factory()->create();

    Livewire::test(ListAddressRequests::class)
        ->callAction(TestAction::make('markFulfilled')->table($addressRequest))
        ->assertHasNoActionErrors();

    expect($addressRequest->fresh()->isFulfilled())->toBeTrue();
});

test('regular users cannot open the address request admin page', function () {
    $this->actingAs(User::factory()->create());

    get(AddressRequestResource::getUrl('index'))->assertRedirect();
});

test('a user can cancel their own address request from the player page', function () {
    $user = User::factory()->create();
    $player = Player::factory()->create();

    $component = Livewire::actingAs($user)->test(ShowPlayer::class, ['player' => $player])
        ->callAction('requestAddress')
        ->assertHasNoActionErrors();

    $addressRequest = AddressRequest::sole();
    expect($addressRequest->feeds()->count())->toBe(1);

    $component->callAction('cancelAddressRequest')
        ->assertHasNoActionErrors()
        ->assertActionVisible('requestAddress');

    expect(AddressRequest::count())->toBe(0)
        ->and($addressRequest->feeds()->count())->toBe(0);
});

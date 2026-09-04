<?php

use App\Livewire\UserProfile;
use App\Models\User;
use Livewire\Livewire;

test('They can see a user\'s profile', function () {
    $user = User::factory()->create();
    $this->get(route('users.profile', $user))
        ->assertOk();
});

test('owner can edit their own profile', function () {
    $user = User::factory()->create(['name' => 'Original Name']);

    Livewire::actingAs($user)
        ->test(UserProfile::class, ['user' => $user->slug])
        ->callAction('editProfile', ['name' => 'Updated Name'])
        ->assertHasNoActionErrors();

    expect($user->fresh()->name)->toBe('Updated Name');
});

test('non-owner does not see edit profile action', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    Livewire::actingAs($other)
        ->test(UserProfile::class, ['user' => $owner->slug])
        ->assertActionHidden('editProfile');
});

test('owner sees the import returns action on their own profile', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(UserProfile::class, ['user' => $user->slug])
        ->assertActionVisible('importReturns');
});

test('non-owner does not see the import returns action', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    Livewire::actingAs($other)
        ->test(UserProfile::class, ['user' => $owner->slug])
        ->assertActionHidden('importReturns');
});

test('guests do not see the import returns action', function () {
    $user = User::factory()->create();

    Livewire::test(UserProfile::class, ['user' => $user->slug])
        ->assertActionHidden('importReturns');
});

test('a user can see who follows them', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create(['name' => 'Raul Nino']);
    $follower->follow($user);

    Livewire::test(UserProfile::class, ['user' => $user->slug])
        ->mountAction('followers')
        ->assertMountedActionModalSee('Raul Nino');
});

test('a user can see who they follow', function () {
    $user = User::factory()->create();
    $followed = User::factory()->create(['name' => 'Raul Nino']);
    $user->follow($followed);

    Livewire::test(UserProfile::class, ['user' => $user->slug])
        ->mountAction('following')
        ->assertMountedActionModalSee('Raul Nino');
});

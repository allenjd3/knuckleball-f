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

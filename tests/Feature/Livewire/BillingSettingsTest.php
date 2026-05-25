<?php

use App\Livewire\BillingSettings;
use App\Models\User;
use Livewire\Livewire;

test('guests cannot access billing settings', function () {
    Livewire::test(BillingSettings::class)
        ->assertStatus(401);
});

test('authenticated users can access billing settings', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BillingSettings::class)
        ->assertStatus(200);
});

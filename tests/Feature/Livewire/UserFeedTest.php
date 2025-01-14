<?php

use App\Livewire\UserFeed;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(UserFeed::class)
        ->assertStatus(200);
});

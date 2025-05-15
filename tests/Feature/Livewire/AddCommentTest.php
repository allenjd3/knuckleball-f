<?php

use App\Livewire\AddComment;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(AddComment::class)
        ->assertStatus(200);
});

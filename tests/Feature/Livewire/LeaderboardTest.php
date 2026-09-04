<?php

use App\Livewire\Leaderboard;
use App\Models\PostalMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::forget('leaderboard');
});

it('renders successfully', function () {
    $this->get(route('leaderboard.index'))->assertOk();
});

it('shows the top user for a category with data', function () {
    $leader = User::factory()->create(['name' => 'Raul Nino']);
    PostalMail::factory()->create(['user_id' => $leader->id, 'date_sent' => now()]);

    Livewire::test(Leaderboard::class)
        ->assertSee('Leaderboard')
        ->assertSee('Raul Nino')
        ->assertSee('Most Sends');
});

it('shows an empty state when there is no data at all', function () {
    Livewire::test(Leaderboard::class)
        ->assertSee('No activity yet');
});

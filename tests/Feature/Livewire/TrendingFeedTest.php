<?php

use App\Livewire\TrendingFeed;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\Signer;
use App\Models\User;
use Livewire\Livewire;

it('renders without a GROUP BY error', function () {
    $player = Player::factory()->create();
    $signer = Signer::factory()->create([
        'signable_id' => $player->id,
        'signable_type' => Player::class,
    ]);
    PostalMail::factory()->count(3)->create([
        'signer_id' => $signer->id,
        'date_sent' => now()->subWeek(),
        'created_at' => now()->subDays(10),
    ]);

    Livewire::actingAs(User::factory()->create())
        ->test(TrendingFeed::class)
        ->assertStatus(200);
});

it('shows the trending players ranked by send count', function () {
    $topPlayer = Player::factory()->create();
    $topSigner = Signer::factory()->create([
        'signable_id' => $topPlayer->id,
        'signable_type' => Player::class,
    ]);
    PostalMail::factory()->count(5)->create([
        'signer_id' => $topSigner->id,
        'date_sent' => now()->subWeek(),
        'created_at' => now()->subDays(10),
    ]);

    $otherPlayer = Player::factory()->create();
    $otherSigner = Signer::factory()->create([
        'signable_id' => $otherPlayer->id,
        'signable_type' => Player::class,
    ]);
    PostalMail::factory()->count(2)->create([
        'signer_id' => $otherSigner->id,
        'date_sent' => now()->subWeek(),
        'created_at' => now()->subDays(10),
    ]);

    $component = Livewire::actingAs(User::factory()->create())
        ->test(TrendingFeed::class);

    $players = $component->get('trendingPlayers');
    expect($players->first()->id)->toBe($topPlayer->id)
        ->and((int) $players->first()->send_count)->toBe(5);
});

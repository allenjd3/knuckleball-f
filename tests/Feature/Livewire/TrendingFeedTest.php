<?php

use App\Livewire\TrendingFeed;
use App\Models\Feed;
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

test('loadMore grows the window until maxWindow then slides the offset', function () {
    Feed::factory(80)->returnedPostalMail()->create();

    $component = Livewire::actingAs(User::factory()->create())
        ->test(TrendingFeed::class);

    expect($component->instance()->trendingReturns)->toHaveCount(20);
    expect($component->get('hasPrevious'))->toBeFalse();

    $component->call('loadMore'); // perPage 40
    expect($component->instance()->trendingReturns)->toHaveCount(40);

    $component->call('loadMore'); // perPage 60
    expect($component->instance()->trendingReturns)->toHaveCount(60);

    // Window full — next loadMore slides instead of growing
    $component->call('loadMore'); // offset 20
    expect($component->instance()->trendingReturns)->toHaveCount(60);
    expect($component->get('offset'))->toBe(20);
    expect($component->get('hasPrevious'))->toBeTrue();
});

test('loadPrevious decrements offset and clamps at zero', function () {
    Feed::factory(80)->returnedPostalMail()->create();

    $component = Livewire::actingAs(User::factory()->create())
        ->test(TrendingFeed::class);

    $component->call('loadMore'); // 40
    $component->call('loadMore'); // 60
    $component->call('loadMore'); // offset 20
    $component->call('loadMore'); // offset 40

    expect($component->get('offset'))->toBe(40);

    $component->call('loadPrevious');
    expect($component->get('offset'))->toBe(20);

    $component->call('loadPrevious');
    expect($component->get('offset'))->toBe(0);
    expect($component->get('hasPrevious'))->toBeFalse();

    $component->call('loadPrevious'); // clamps
    expect($component->get('offset'))->toBe(0);
});

test('setSort resets offset and perPage', function () {
    Feed::factory(80)->returnedPostalMail()->create();

    $component = Livewire::actingAs(User::factory()->create())
        ->test(TrendingFeed::class);

    $component->call('loadMore');
    $component->call('loadMore');
    $component->call('loadMore'); // offset now 20

    $component->call('setSort', 'comments');

    expect($component->get('offset'))->toBe(0);
    expect($component->get('perPage'))->toBe(20);
});

it('hides trending player photos from guests but shows them to authenticated users', function () {
    $player = Player::factory()->create();
    $player->media()->create(['url' => 'avatars/test.jpg']);
    $signer = Signer::factory()->create([
        'signable_id' => $player->id,
        'signable_type' => Player::class,
    ]);
    PostalMail::factory()->count(3)->create([
        'signer_id' => $signer->id,
        'date_sent' => now()->subWeek(),
        'created_at' => now()->subDays(10),
    ]);

    Livewire::test(TrendingFeed::class)
        ->assertDontSee('avatars/test.jpg');

    Livewire::actingAs(User::factory()->create())
        ->test(TrendingFeed::class)
        ->assertSee('avatars/test.jpg');
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

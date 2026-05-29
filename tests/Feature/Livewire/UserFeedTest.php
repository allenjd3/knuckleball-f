<?php

use App\Enums\Role;
use App\Livewire\UserFeed;
use App\Models\Feed;
use App\Models\FeeMaterial;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(UserFeed::class)
        ->assertStatus(200);
});

test('it creates a feed when adding postal mail', function () {
    $this->freezeTime(function () {
        $player = Player::factory()->create();
        $user = User::factory()->state(['role' => Role::ADMIN])->create();
        $feeMaterial = FeeMaterial::factory()->create();
        Livewire::actingAs($user)->test('ShowPlayer', ['player' => $player])
            ->callTableAction('createPostalMail', data: [
                'comment' => 'some comment here',
                'user_id' => $user->id,
                'fee_material_id' => $feeMaterial->id,
                'date_sent' => now()->subWeek(),
            ]);

        $this->assertDatabaseHas('feeds', [
            'feedable_id' => $user->postalMails->first()->id,
            'feedable_type' => PostalMail::class,
            'comment' => 'some comment here',
        ]);
    });
});

test('it pulls the feeds', function () {
    Feed::factory(21)->postalMail()->create();

    $this->assertCount(15, Livewire::test(UserFeed::class)
        ->instance()
        ->feeds);
});

test('loadMore grows the window until maxWindow then slides the offset', function () {
    Feed::factory(60)->postalMail()->create();

    $component = Livewire::test(UserFeed::class);
    expect($component->instance()->feeds)->toHaveCount(15);
    expect($component->get('offset'))->toBe(0);
    expect($component->get('hasPrevious'))->toBeFalse();

    $component->call('loadMore');
    expect($component->instance()->feeds)->toHaveCount(30);
    expect($component->get('offset'))->toBe(0);

    $component->call('loadMore');
    expect($component->instance()->feeds)->toHaveCount(45);
    expect($component->get('offset'))->toBe(0);

    // Window is now full — next loadMore slides instead of growing
    $component->call('loadMore');
    expect($component->instance()->feeds)->toHaveCount(45);
    expect($component->get('offset'))->toBe(15);
    expect($component->get('hasPrevious'))->toBeTrue();
});

test('loadPrevious decrements the offset and clamps at zero', function () {
    Feed::factory(60)->postalMail()->create();

    $component = Livewire::test(UserFeed::class);

    // Slide the window forward twice
    $component->call('loadMore'); // perPage 30
    $component->call('loadMore'); // perPage 45
    $component->call('loadMore'); // offset 15
    $component->call('loadMore'); // offset 30

    expect($component->get('offset'))->toBe(30);

    $component->call('loadPrevious');
    expect($component->get('offset'))->toBe(15);
    expect($component->get('hasPrevious'))->toBeTrue();

    $component->call('loadPrevious');
    expect($component->get('offset'))->toBe(0);
    expect($component->get('hasPrevious'))->toBeFalse();

    // Clamped — does not go negative
    $component->call('loadPrevious');
    expect($component->get('offset'))->toBe(0);
});

test('hasMore is false when all feeds are loaded', function () {
    Feed::factory(10)->postalMail()->create();

    $component = Livewire::test(UserFeed::class);

    expect($component->get('hasMore'))->toBeFalse();
});

test('setFilter resets offset and perPage', function () {
    Feed::factory(60)->postalMail()->create();

    $component = Livewire::test(UserFeed::class);
    $component->call('loadMore');
    $component->call('loadMore');
    $component->call('loadMore'); // offset now 15

    $component->call('setFilter', 'following');

    expect($component->get('offset'))->toBe(0);
    expect($component->get('perPage'))->toBe(15);
});

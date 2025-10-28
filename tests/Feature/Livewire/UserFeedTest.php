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

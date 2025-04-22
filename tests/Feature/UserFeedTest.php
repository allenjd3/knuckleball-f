<?php

use App\Livewire\UserFeed;
use App\Models\Card;
use App\Models\Feed;
use App\Models\FeeMaterial;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\User;
use App\Presenters\FeedPresenter;
use Database\Factories\FeedFactory;
use Illuminate\Support\Facades\DB;

test('it creates a feed when adding postal mail', function () {
    $this->freezeTime(function () {
        $player = Player::factory()->create();
        $user = User::factory()->state(['super_admin' => true])->create();
        $feeMaterial = FeeMaterial::factory()->create();
        Livewire::actingAs($user)->test('ShowPlayer', ['player' => $player])
            ->callTableAction('createPostalMail', data: [
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

test('a feed can have a presenter', function () {
    $feed = Feed::factory()->for(
        PostalMail::factory()
            ->has(
                Card::factory()
                    ->for(User::factory())
                    ->state(
                        ['manufacturer' => 'Merx', 'series' => 'first', 'year' => 1986],
                    ),
            )
            ->has(
                FeeMaterial::factory()->state(['name' => 'Ball'])
            ),
        'feedable',
    )->create();
    $feedPresenter = FeedPresenter::make($feed);

    $this->assertEquals($feed->comment, $feedPresenter->comment);
    $this->assertEquals("Sent: {$feed->feedable->date_sent->toDayDateTimeString()}", $feedPresenter->title());
    $this->assertEquals("Material: Ball. \n<br />Manufacturer: Merx, Series: first, Year: 1986", $feedPresenter->body());
    $this->assertEquals("", $feedPresenter->footer());
});

test('it pulls the feeds', function () {
    $feed = Feed::factory(21)->create();

    $this->assertEquals(21, Livewire::test(UserFeed::class)
        ->instance()
        ->feeds->total());
});

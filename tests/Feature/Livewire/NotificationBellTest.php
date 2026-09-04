<?php

use App\Livewire\NotificationBell;
use App\Models\Event;
use App\Models\Player;
use App\Models\User;
use App\Notifications\CardShowAlert;
use App\Notifications\EventApprovedNotification;
use App\Notifications\WatchlistSigningAlert;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->rawNotification = fn (User $user, array $data) => $user->notifications()->create([
        'id' => Str::uuid(),
        'type' => 'raw',
        'data' => $data,
    ]);
});

it('describes a plain comment differently from a reply', function () {
    $user = User::factory()->create();

    ($this->rawNotification)($user, [
        'type' => 'new_comment',
        'is_reply' => false,
        'commenter_name' => 'Raul Nino',
        'commenter_slug' => 'raul-nino',
        'body' => 'Great pickup!',
        'feed_id' => 1,
    ]);

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->assertSee('Raul Nino')
        ->assertSee('commented')
        ->assertDontSee('replied to your comment');
});

it('describes a reply notification distinctly from a plain comment', function () {
    $user = User::factory()->create();

    ($this->rawNotification)($user, [
        'type' => 'new_comment',
        'is_reply' => true,
        'commenter_name' => 'Raul Nino',
        'commenter_slug' => 'raul-nino',
        'body' => 'Thanks!',
        'feed_id' => 1,
    ]);

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->assertSee('Raul Nino')
        ->assertSee('replied to your comment');
});

it('describes a pack_player_added notification instead of a generic message', function () {
    $user = User::factory()->create();

    ($this->rawNotification)($user, [
        'type' => 'pack_player_added',
        'pack_name' => 'Vintage Stars',
        'pack_slug' => 'vintage-stars',
        'player_name' => 'Babe Ruth',
    ]);

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->assertSee('Babe Ruth')
        ->assertSee('Vintage Stars')
        ->assertDontSee('New notification');
});

it('describes a want_list_player_added notification instead of a generic message', function () {
    $user = User::factory()->create();

    ($this->rawNotification)($user, [
        'type' => 'want_list_player_added',
        'want_list_name' => 'HOF Autos',
        'want_list_path' => '/want-lists/hof-autos',
        'player_name' => 'Mickey Mantle',
        'player_path' => '/players/mickey-mantle',
    ]);

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->assertSee('Mickey Mantle')
        ->assertSee('HOF Autos')
        ->assertDontSee('New notification');
});

it('describes a watchlist signing alert instead of a generic message', function () {
    $user = User::factory()->create();
    $player = Player::factory()->create(['name' => 'Ken Griffey Jr.']);
    $event = Event::factory()->mailIn($player)->create(['name' => 'Spring TTM Tour']);

    $user->notify(new WatchlistSigningAlert($event));

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->assertSee('Signing alert')
        ->assertSee('Ken Griffey Jr.')
        ->assertSee('Spring TTM Tour')
        ->assertDontSee('New notification');
});

it('describes a card show alert instead of a generic message', function () {
    $user = User::factory()->create();
    $event = Event::factory()->cardShow()->create(['name' => 'Midwest Card Expo']);

    $user->notify(new CardShowAlert($event));

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->assertSee('Card show near you')
        ->assertSee('Midwest Card Expo')
        ->assertDontSee('New notification');
});

it('describes an event approved notification instead of a generic message', function () {
    $user = User::factory()->create();
    $event = Event::factory()->playerSigning()->create(['name' => 'Fall Fan Day']);

    $user->notify(new EventApprovedNotification($event));

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->assertSee('was approved and is now live')
        ->assertSee('Fall Fan Day')
        ->assertDontSee('New notification');
});

it('falls back to a notification\'s own message instead of the generic placeholder', function () {
    $user = User::factory()->create();

    ($this->rawNotification)($user, [
        'message' => 'Your card ending in 4242 expires 01/2027.',
    ]);

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->assertSee('Your card ending in 4242 expires 01/2027.')
        ->assertDontSee('New notification');
});

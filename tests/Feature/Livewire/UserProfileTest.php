<?php

use App\Actions\CreateFeedItem;
use App\Enums\ActivityFilter;
use App\Livewire\UserProfile;
use App\Models\Feed;
use App\Models\PostalMail;
use App\Models\User;
use Livewire\Livewire;

test('They can see a user\'s profile', function () {
    $user = User::factory()->create();
    $this->get(route('users.profile', $user))
        ->assertOk();
});

test('owner can edit their own profile', function () {
    $user = User::factory()->create(['name' => 'Original Name']);

    Livewire::actingAs($user)
        ->test(UserProfile::class, ['user' => $user->slug])
        ->callAction('editProfile', ['name' => 'Updated Name'])
        ->assertHasNoActionErrors();

    expect($user->fresh()->name)->toBe('Updated Name');
});

test('non-owner does not see edit profile action', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    Livewire::actingAs($other)
        ->test(UserProfile::class, ['user' => $owner->slug])
        ->assertActionHidden('editProfile');
});

test('owner sees the import returns action on their own profile', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(UserProfile::class, ['user' => $user->slug])
        ->assertActionVisible('importReturns');
});

test('non-owner does not see the import returns action', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    Livewire::actingAs($other)
        ->test(UserProfile::class, ['user' => $owner->slug])
        ->assertActionHidden('importReturns');
});

test('guests do not see the import returns action', function () {
    $user = User::factory()->create();

    Livewire::test(UserProfile::class, ['user' => $user->slug])
        ->assertActionHidden('importReturns');
});

test('a user can see who follows them', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create(['name' => 'Raul Nino']);
    $follower->follow($user);

    Livewire::test(UserProfile::class, ['user' => $user->slug])
        ->mountAction('followers')
        ->assertMountedActionModalSee('Raul Nino');
});

test('a user can see who they follow', function () {
    $user = User::factory()->create();
    $followed = User::factory()->create(['name' => 'Raul Nino']);
    $user->follow($followed);

    Livewire::test(UserProfile::class, ['user' => $user->slug])
        ->mountAction('following')
        ->assertMountedActionModalSee('Raul Nino');
});

/**
 * @return array{pending: Feed, returned: Feed, failed: Feed}
 */
function profileSendFeeds(User $user): array
{
    $feeds = [];

    foreach (['pending' => 'unReturned', 'returned' => 'returned', 'failed' => 'failed'] as $key => $state) {
        $postalMail = PostalMail::factory()->{$state}()->for($user)->create();
        CreateFeedItem::execute($postalMail, $postalMail->comment);
        $feeds[$key] = Feed::query()
            ->where('feedable_type', PostalMail::class)
            ->where('feedable_id', $postalMail->id)
            ->firstOrFail();
    }

    return $feeds;
}

it('shows only the matching sends for each activity filter', function (string $filter, string $expected) {
    $user = User::factory()->create();
    $feeds = profileSendFeeds($user);

    $shown = Livewire::test(UserProfile::class, ['user' => $user->slug])
        ->set('activityFilter', $filter)
        ->instance()
        ->feeds
        ->pluck('id')
        ->all();

    expect($shown)->toBe([$feeds[$expected]->id]);
})->with([
    'pending' => ['pending', 'pending'],
    'returns' => ['returns', 'returned'],
    'failures' => ['failures', 'failed'],
]);

it('shows every send and mentions under the all filter', function () {
    $user = User::factory()->create();
    $feeds = profileSendFeeds($user);
    $mentioning = Feed::factory()->postalMail()->create();
    $mentioning->mention($user);

    $shown = Livewire::test(UserProfile::class, ['user' => $user->slug])
        ->instance()
        ->feeds
        ->pluck('id')
        ->all();

    expect($shown)->toEqualCanonicalizing([...collect($feeds)->pluck('id')->all(), $mentioning->id]);
});

it('counts the profile owner\'s sends for each filter', function () {
    $user = User::factory()->create();
    profileSendFeeds($user);
    profileSendFeeds(User::factory()->create());

    Livewire::test(UserProfile::class, ['user' => $user->slug])
        ->assertSet('activityFilterCounts', ['pending' => 1, 'returns' => 1, 'failures' => 1])
        ->assertSeeHtml('wire:click="$set(\'activityFilter\', \'failures\')"');
});

it('reads the activity filter from the url', function () {
    $user = User::factory()->create();

    Livewire::withQueryParams(['activity' => 'failures'])
        ->test(UserProfile::class, ['user' => $user->slug])
        ->assertSet('activityFilter', 'failures')
        ->assertSee('No failures.');
});

it('falls back to all for an unknown activity filter', function () {
    $user = User::factory()->create();

    $component = Livewire::withQueryParams(['activity' => 'nonsense'])
        ->test(UserProfile::class, ['user' => $user->slug]);

    expect($component->instance()->selectedActivityFilter)->toBe(ActivityFilter::All);
    $component->assertSee('No activity yet.');
});

it('shows an empty message for each filter', function (string $filter, string $message) {
    $user = User::factory()->create();

    Livewire::test(UserProfile::class, ['user' => $user->slug])
        ->set('activityFilter', $filter)
        ->assertSee($message);
})->with([
    ['pending', 'No pending sends.'],
    ['returns', 'No returns yet.'],
    ['failures', 'No failures.'],
]);

it('goes back to the first page when the filter changes', function () {
    $user = User::factory()->create();

    Livewire::test(UserProfile::class, ['user' => $user->slug])
        ->call('setPage', 2)
        ->assertSet('paginators.page', 2)
        ->set('activityFilter', 'pending')
        ->assertSet('paginators.page', 1);
});

<?php

use App\Enums\ActivityFilter;
use App\Enums\FailureReason;
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

test('activity can be filtered into pending sends, returns, and failures', function () {
    $user = User::factory()->create();

    $pending = Feed::factory()->create([
        'feedable_id' => PostalMail::factory()->for($user)->unReturned()->create()->id,
        'feedable_type' => PostalMail::class,
        'followable_id' => $user->id,
    ]);
    $returned = Feed::factory()->create([
        'feedable_id' => PostalMail::factory()->for($user)->returned()->create()->id,
        'feedable_type' => PostalMail::class,
        'followable_id' => $user->id,
    ]);
    $failed = Feed::factory()->create([
        'feedable_id' => PostalMail::factory()->for($user)->failed()->create()->id,
        'feedable_type' => PostalMail::class,
        'followable_id' => $user->id,
    ]);
    $comment = Feed::factory()->comment($user)->create();

    $component = Livewire::test(UserProfile::class, ['user' => $user->slug]);

    expect($component->instance()->feeds->pluck('id')->all())
        ->toEqualCanonicalizing([$pending->id, $returned->id, $failed->id, $comment->id]);

    $component->set('activityFilter', ActivityFilter::Pending->value);
    expect($component->instance()->feeds->pluck('id')->all())->toBe([$pending->id]);

    $component->set('activityFilter', ActivityFilter::Returns->value);
    expect($component->instance()->feeds->pluck('id')->all())->toBe([$returned->id]);

    $component->set('activityFilter', ActivityFilter::Failures->value);
    expect($component->instance()->feeds->pluck('id')->all())->toBe([$failed->id]);
});

test('activity filters exclude postal mail from other users the profile is mentioned in', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $mentioned = Feed::factory()->create([
        'feedable_id' => PostalMail::factory()->for($other)->unReturned()->create()->id,
        'feedable_type' => PostalMail::class,
        'followable_id' => $other->id,
    ]);
    $mentioned->mention($user);

    $component = Livewire::test(UserProfile::class, ['user' => $user->slug]);

    expect($component->instance()->feeds->pluck('id')->all())->toBe([$mentioned->id]);

    $component->set('activityFilter', ActivityFilter::Pending->value);
    expect($component->instance()->feeds)->toBeEmpty();
});

test('activity filter shows a filter-specific empty state', function () {
    $user = User::factory()->create();

    Livewire::withQueryParams(['activity' => 'failures'])
        ->test(UserProfile::class, ['user' => $user->slug])
        ->assertSet('activityFilter', ActivityFilter::Failures->value)
        ->assertSee('No failures.');
});

test('an unknown activity filter falls back to showing all activity', function () {
    $user = User::factory()->create();
    $comment = Feed::factory()->comment($user)->create();

    $component = Livewire::withQueryParams(['activity' => 'bogus'])
        ->test(UserProfile::class, ['user' => $user->slug]);

    expect($component->instance()->selectedActivityFilter)->toBe(ActivityFilter::All)
        ->and($component->instance()->feeds->pluck('id')->all())->toBe([$comment->id]);
});

test('activity filters show how many sends are in each status', function () {
    $user = User::factory()->create();

    foreach (['unReturned', 'unReturned', 'returned', 'failed'] as $state) {
        Feed::factory()->create([
            'feedable_id' => PostalMail::factory()->for($user)->{$state}()->create()->id,
            'feedable_type' => PostalMail::class,
            'followable_id' => $user->id,
        ]);
    }

    $component = Livewire::test(UserProfile::class, ['user' => $user->slug]);

    expect($component->instance()->activityFilterCounts)->toBe([
        'pending' => 2,
        'returns' => 1,
        'failures' => 1,
    ]);
});

test('owners are prompted to label failed sends that have no reason', function () {
    $user = User::factory()->create();
    $unlabeled = PostalMail::factory()->for($user)->failed()->create();
    $labeled = PostalMail::factory()->for($user)->failed()->create(['failure_reason' => FailureReason::Declined]);

    Livewire::actingAs($user)
        ->test(UserProfile::class, ['user' => $user->slug])
        ->assertSee("1 of your failed sends doesn't say what happened.")
        ->assertActionVisible('labelFailures')
        ->callAction('labelFailures', data: [
            'reasons' => [$unlabeled->id => FailureReason::ReturnToSender->value],
        ])
        ->assertHasNoActionErrors()
        ->assertActionHidden('labelFailures');

    expect($unlabeled->fresh()->failure_reason)->toBe(FailureReason::ReturnToSender)
        ->and($labeled->fresh()->failure_reason)->toBe(FailureReason::Declined);
});

test('other users do not see the label failures prompt', function () {
    $owner = User::factory()->create();
    PostalMail::factory()->for($owner)->failed()->create();

    Livewire::actingAs(User::factory()->create())
        ->test(UserProfile::class, ['user' => $owner->slug])
        ->assertActionHidden('labelFailures')
        ->assertDontSee('failed sends', false);
});

<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('it generates a slug for new users', function () {
    $user = User::factory()->create();

    expect($user->slug)->toStartWith(str()->slug($user->name, '-'));
});

test('it generates a slug for old users', function () {
    $user = User::factory()->make();

    DB::table('users')
        ->insert(
            $user->only([
                'name',
                'email',
                'email_verified_at',
                'current_team_id',
                'published_at',
                'password',
            ])
        );

    $user = User::first();
    expect($user->slug)->toBeNull();

    $this->artisan('users:slug-generate')
        ->expectsConfirmation('This will reset all User Slugs. Only run this once! Do you wish to continue?', 'yes')
        ->assertExitCode(0);

    expect($user->fresh()->slug)->toStartWith(str()->slug($user->name, '-'));
});

test('a user can follow another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1->follow($user2);

    expect($user2->followers->pluck('id'))->toContain($user1->id);
});

test('a user can only follow another user once', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1->follow($user2);
    $user1->follow($user2);

    $this->assertCount(1, $user1->following);
});

test('a user can unfollow another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1->follow($user2);
    expect($user2->followers->pluck('id'))->toContain($user1->id);

    $user1->unfollow($user2);
    expect($user2->fresh()->followers->pluck('id'))->toBeEmpty();
});

test('a user can get a list of all of the people following them', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    User::factory(2)->create();

    $user1->follow($user2);
    $user3->follow($user2);

    expect($user2->followers->pluck('id')->toArray())->toEqual([$user1->id, $user3->id]);
});

test('a user can get a list of everyone they are following', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    User::factory(2)->create();

    $user1->follow($user2);
    $user1->follow($user3);

    expect($user1->following->pluck('id')->toArray())->toEqual([$user2->id, $user3->id]);
});

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

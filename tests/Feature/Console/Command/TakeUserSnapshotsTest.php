<?php

use App\Models\User;
use App\Models\UserSnapshot;

test('it creates a snapshot for every user', function () {
    $users = User::factory()->count(3)->create();

    $this->artisan('snapshots:take')->assertSuccessful();

    foreach ($users as $user) {
        $this->assertDatabaseHas('user_snapshots', [
            'user_id' => $user->id,
            'year'    => now()->year,
        ]);
    }
});

test('it updates an existing snapshot rather than creating a duplicate', function () {
    $user = User::factory()->create();

    $this->artisan('snapshots:take')->assertSuccessful();
    $this->artisan('snapshots:take')->assertSuccessful();

    expect(UserSnapshot::where('user_id', $user->id)->count())->toBe(1);
});

test('it accepts a custom year option', function () {
    $user = User::factory()->create();

    $this->artisan('snapshots:take', ['--year' => 2025])->assertSuccessful();

    $this->assertDatabaseHas('user_snapshots', [
        'user_id' => $user->id,
        'year'    => 2025,
    ]);
});

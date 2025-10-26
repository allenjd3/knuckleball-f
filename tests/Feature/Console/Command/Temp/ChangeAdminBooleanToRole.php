<?php

use App\Enums\Role;
use App\Models\User;

test('it updates user super_admin to admin role', function () {
    $admin = User::factory()->isSuperAdmin()->create();
    $users = User::factory(5)->create();
    $this->artisan('operation:change-admin-boolean-to-role');
    $userRoles = $users->fresh()->pluck('role')->unique();

    expect($admin->fresh()->role)->toBe(Role::ADMIN);

    $this->assertCount(1, $userRoles);
    expect($userRoles[0])->toBe(Role::USER);
});
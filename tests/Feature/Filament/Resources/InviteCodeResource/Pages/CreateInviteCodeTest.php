<?php

use App\Filament\Resources\InviteCodeResource\Pages\CreateInviteCode;
use App\Models\InviteCode;
use App\Models\User;

test('it can save an invite code', function () {
    InviteCode::factory()->make();
    $user = User::factory()->state(['super_admin' => true])->create();

    Livewire::actingAs($user)
        ->test(CreateInviteCode::class)
        ->fillForm([
            'code' => 'INVITE',
            'remaining' => 5,
            'is_unlimited' => false,
        ])
        ->call('create')
        ->assertValid();
});

test('it requires the code', function () {
    InviteCode::factory()->make();
    $user = User::factory()->state(['super_admin' => true])->create();

    Livewire::actingAs($user)
        ->test(CreateInviteCode::class)
        ->fillForm([
            'remaining' => 5,
            'is_unlimited' => false,
        ])
        ->call('create')
        ->assertHasFormErrors(['code' => 'required']);
});

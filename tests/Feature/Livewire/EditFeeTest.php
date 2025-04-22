<?php

use App\Livewire\EditFee;
use App\Models\Fee;
use App\Models\User;
use Livewire\Livewire;

it('allows you to edit Fees', function () {
    $user = User::factory()->create();
    $fee = Fee::factory()->state(['amount' => 12])->create();

    Livewire::actingAs($user)
        ->test(EditFee::class, ['fee' => $fee])
        ->callAction('editFee', ['amount' => 23, 'fee_material_id' => $fee->fee_material_id])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('fees', ['amount' => 23, 'fee_material_id' => $fee->fee_material_id]);
});

it('doesn\'t allow non users to edit fees', function () {
    $fee = Fee::factory()->create();
    Livewire::test(EditFee::class, ['fee' => $fee])
        ->assertActionHidden('editFee');
});

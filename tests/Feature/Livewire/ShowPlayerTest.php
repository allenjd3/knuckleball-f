<?php

use App\Actions\CreateFeedItem;
use App\Livewire\ShowPlayer;
use App\Models\FeeMaterial;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\User;
use Filament\Tables\Actions\EditAction;

test('it can create addresses', function () {
    $user = User::factory()->isSuperAdmin()->create();
    $player = Player::factory()->create();

    Livewire::actingAs($user)->test(ShowPlayer::class, ['player' => $player])
        ->callAction('createAddress', data: [
            'address_1' => '2671 Rochester Ave',
            'city' => 'Hamilton',
            'state' => 'Ohio',
            'postal_code' => '45011',
        ])
        ->assertHasNoActionErrors();
});

test('it can create a fee', function () {
    $user = User::factory()->isSuperAdmin()->create();
    $player = Player::factory()->create();
    $feeMaterial = FeeMaterial::factory()->create();

    Livewire::actingAs($user)->test(ShowPlayer::class, ['player' => $player])
        ->callAction('createFee', data: [
            'amount' => 23,
            'fee_material_id' => $feeMaterial->id,
        ])
        ->assertHasNoActionErrors();

    $this->assertTrue($player->fresh()->fees->pluck('amount')->contains(23));
});

it('debug postal mail edit', function () {
    $player = Player::factory()->create();
    $postalMail = PostalMail::factory()->state(['signer_id' => $player->signer->id])->create();
    CreateFeedItem::execute($postalMail, $postalMail->comment);

    $returnedDate = now()->subDay();
    $component = Livewire::actingAs($postalMail->user)->test(ShowPlayer::class, ['player' => $player]);

    // Check if the record is visible in the table
    $component->assertCanSeeTableRecords([$postalMail]);

    // Check if the action is visible
    $component->assertTableActionVisible(EditAction::class, $postalMail);

    // Then try the action
    $component->callTableAction(EditAction::class, $postalMail, [
        'returned_date' => $returnedDate->format('Y-m-d'),
    ])
        ->assertHasNoTableActionErrors();
});

it('can edit a postal mail', function () {
    $player = Player::factory()->create();
    $postalMail = PostalMail::factory()->state([
        'signer_id' => $player->signer->id,
        'returned_date' => null,
    ])->create();

    CreateFeedItem::execute($postalMail, $postalMail->comment);

    $returnedDate = now()->subDay();

    Livewire::actingAs($postalMail->user)->test(ShowPlayer::class, ['player' => $postalMail->player])
        ->mountTableAction(EditAction::class, $postalMail)
        ->setTableActionData([
            'returned_date' => $returnedDate->format('Y-m-d'),
        ])
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    $this->assertEquals($returnedDate?->format('Y-m-d'), $postalMail->fresh()->returned_date?->format('Y-m-d'));
});

it('can mark a postal mail as failed', function () {
    $player = Player::factory()->create();
    $postalMail = PostalMail::factory()->state([
        'signer_id' => $player->signer->id,
        'returned_date' => null,
    ])->create();

    CreateFeedItem::execute($postalMail, $postalMail->comment);

    Livewire::actingAs($postalMail->user)->test(ShowPlayer::class, ['player' => $postalMail->player])
        ->callTableAction(EditAction::class, $postalMail, [
            'is_failed' => true,
        ])
        ->assertHasNoActionErrors();

    $this->assertTrue($postalMail->fresh()->is_failed);
});

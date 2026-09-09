<?php

use App\Actions\CreateFeedItem;
use App\Livewire\InPersonAutographsTable;
use App\Models\Feed;
use App\Models\FeeMaterial;
use App\Models\InPersonAutograph;
use App\Models\Player;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('a published user can log an in-person autograph', function () {
    $user = User::factory()->create();
    $player = Player::factory()->create();
    $feeMaterial = FeeMaterial::factory()->create();

    Livewire::actingAs($user)->test(InPersonAutographsTable::class, ['player' => $player])
        ->callAction(TestAction::make('createInPersonAutograph')->table(), [
            'obtained_date' => now()->subDay()->toDateString(),
            'fee_material_id' => $feeMaterial->id,
            'location' => 'National Card Show',
            'comment' => 'So excited!',
        ])
        ->assertHasNoActionErrors();

    $autograph = InPersonAutograph::firstWhere('signer_id', $player->signer->id);

    expect($autograph)->not->toBeNull()
        ->and($autograph->user_id)->toBe($user->id)
        ->and($autograph->location)->toBe('National Card Show')
        ->and($autograph->feeds()->exists())->toBeTrue()
        ->and($autograph->feeds->first()->componentName())->toBe('feeds.in-person-card');
});

test('the owner can edit their own in-person autograph', function () {
    $player = Player::factory()->create();
    $autograph = InPersonAutograph::factory()->create(['signer_id' => $player->signer->id]);

    Livewire::actingAs($autograph->user)->test(InPersonAutographsTable::class, ['player' => $player])
        ->callAction(TestAction::make('edit')->table($autograph), [
            'location' => 'Updated Location',
        ])
        ->assertHasNoActionErrors();

    expect($autograph->fresh()->location)->toBe('Updated Location');
});

test('another user cannot edit or delete someone else in-person autograph', function () {
    $player = Player::factory()->create();
    $autograph = InPersonAutograph::factory()->create(['signer_id' => $player->signer->id]);
    $otherUser = User::factory()->create();

    Livewire::actingAs($otherUser)->test(InPersonAutographsTable::class, ['player' => $player])
        ->assertTableActionHidden('edit', $autograph)
        ->assertTableActionHidden('delete', $autograph);
});

test('the owner can delete their own in-person autograph', function () {
    $player = Player::factory()->create();
    $autograph = InPersonAutograph::factory()->create(['signer_id' => $player->signer->id]);

    Livewire::actingAs($autograph->user)->test(InPersonAutographsTable::class, ['player' => $player])
        ->callAction(TestAction::make('delete')->table($autograph));

    expect(InPersonAutograph::find($autograph->id))->toBeNull();
});

test('deleting an in-person autograph also removes its feed post', function () {
    $player = Player::factory()->create();
    $autograph = InPersonAutograph::factory()->create(['signer_id' => $player->signer->id]);
    CreateFeedItem::execute($autograph, $autograph->comment);

    expect($autograph->feeds()->exists())->toBeTrue();

    Livewire::actingAs($autograph->user)->test(InPersonAutographsTable::class, ['player' => $player])
        ->callAction(TestAction::make('delete')->table($autograph));

    expect(Feed::where('feedable_type', InPersonAutograph::class)->where('feedable_id', $autograph->id)->exists())->toBeFalse();
});

test('editing an in-person autograph to declined removes its feed post', function () {
    $player = Player::factory()->create();
    $autograph = InPersonAutograph::factory()->create(['signer_id' => $player->signer->id, 'is_declined' => false]);
    CreateFeedItem::execute($autograph, $autograph->comment);

    expect($autograph->feeds()->exists())->toBeTrue();

    Livewire::actingAs($autograph->user)->test(InPersonAutographsTable::class, ['player' => $player])
        ->callAction(TestAction::make('edit')->table($autograph), [
            'obtained_date' => $autograph->obtained_date->toDateString(),
            'location' => $autograph->location,
            'is_declined' => true,
            'comment' => $autograph->comment,
        ])
        ->assertHasNoActionErrors();

    expect($autograph->fresh()->feeds()->exists())->toBeFalse();
});

test('a photo can be attached when logging an in-person autograph', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $player = Player::factory()->create();
    $feeMaterial = FeeMaterial::factory()->create();

    Livewire::actingAs($user)->test(InPersonAutographsTable::class, ['player' => $player])
        ->callAction(TestAction::make('createInPersonAutograph')->table(), [
            'obtained_date' => now()->subDay()->toDateString(),
            'fee_material_id' => $feeMaterial->id,
            'photos' => [UploadedFile::fake()->image('signed-8x10.jpg')],
        ])
        ->assertHasNoActionErrors();

    $autograph = InPersonAutograph::firstWhere('signer_id', $player->signer->id);

    expect($autograph->media)->toHaveCount(1)
        ->and($autograph->generateMeta()['photos'])->toHaveCount(1);
});

test('a photo can be added to an existing in-person autograph afterward', function () {
    Storage::fake('public');

    $player = Player::factory()->create();
    $autograph = InPersonAutograph::factory()->create(['signer_id' => $player->signer->id]);

    Livewire::actingAs($autograph->user)->test(InPersonAutographsTable::class, ['player' => $player])
        ->callAction(TestAction::make('addPhoto')->table($autograph), [
            'photos' => [UploadedFile::fake()->image('signed-ball.jpg')],
        ])
        ->assertHasNoActionErrors();

    expect($autograph->fresh()->media)->toHaveCount(1);
});

test('logging a declined in-person attempt does not post to the feed', function () {
    $user = User::factory()->create();
    $player = Player::factory()->create();

    Livewire::actingAs($user)->test(InPersonAutographsTable::class, ['player' => $player])
        ->callAction(TestAction::make('createInPersonAutograph')->table(), [
            'obtained_date' => now()->subDay()->toDateString(),
            'is_declined' => true,
        ])
        ->assertHasNoActionErrors();

    $autograph = InPersonAutograph::firstWhere('signer_id', $player->signer->id);

    expect($autograph->is_declined)->toBeTrue()
        ->and($autograph->feeds()->exists())->toBeFalse();
});

test('editing an autograph to mark it declined counts against the response rate', function () {
    $player = Player::factory()->create();
    $autograph = InPersonAutograph::factory()->create(['signer_id' => $player->signer->id, 'is_declined' => false]);

    Livewire::actingAs($autograph->user)->test(InPersonAutographsTable::class, ['player' => $player])
        ->callAction(TestAction::make('edit')->table($autograph), [
            'is_declined' => true,
        ])
        ->assertHasNoActionErrors();

    expect($autograph->fresh()->is_declined)->toBeTrue()
        ->and($player->fresh()->in_person_response_rate)->toBe('0% obtained in person (0 of 1).');
});

test('guests cannot see the create action', function () {
    $player = Player::factory()->create();

    Livewire::test(InPersonAutographsTable::class, ['player' => $player])
        ->assertTableActionHidden('createInPersonAutograph');
});

<?php

use App\Actions\CreateFeedItem;
use App\Livewire\EditComment;
use App\Models\Comment;
use App\Models\User;

test('the user can delete their own comment', function () {
    $user = User::factory()->create();

    $comment = Comment::factory()
        ->state([
            'user_id' => $user->id,
            'body' => 'This is a comment',
        ])
        ->create();

    Livewire::actingAs($user)
        ->test(EditComment::class, ['commentId' => $comment->id])
        ->call('delete');

    $this->assertTrue($comment->fresh()->trashed());
});

test("the user cannot delete another user's comment", function () {
    $user = User::factory()->create();

    $comment = Comment::factory()
        ->state([
            'body' => 'This is a comment',
        ])
        ->create();

    Livewire::actingAs($user)
        ->test(EditComment::class, ['commentId' => $comment->id])
        ->call('delete')
        ->assertForbidden();

    $this->assertFalse($comment->fresh()->trashed());
});

test('the user can edit their own comment', function () {
    $user = User::factory()->create();

    $comment = Comment::factory()
        ->state([
            'user_id' => $user->id,
            'body' => 'This is a comment',
        ])
        ->create();
    CreateFeedItem::execute($comment, $comment->body);

    Livewire::actingAs($user)
        ->test(EditComment::class, ['commentId' => $comment->id])
        ->set('body', 'Updated Comment')
        ->call('edit');

    $this->assertDatabaseHas('comments', [
        'body' => 'Updated Comment',
    ]);
});

test("the user cannot edit another user's comment", function () {
    $user = User::factory()->create();

    $comment = Comment::factory()
        ->state([
            'body' => 'This is a comment',
        ])
        ->create();

    Livewire::actingAs($user)
        ->test(EditComment::class, ['commentId' => $comment->id])
        ->set('body', 'Updated Comment')
        ->call('edit')
        ->assertForbidden();

    $this->assertDatabaseHas('comments', [
        'body' => 'This is a comment',
    ]);
});

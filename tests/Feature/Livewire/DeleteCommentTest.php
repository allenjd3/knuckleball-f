<?php

use App\Actions\CreateFeedItem;
use App\Livewire\DeleteComment;
use App\Models\Comment;
use App\Models\Feed;
use App\Models\User;

test('a user can delete a comment', function () {
    $comment = Comment::factory()->for(User::factory())->create();

    Livewire::actingAs($comment->user)
        ->test(DeleteComment::class, ['commentId' => $comment->id])
        ->call('delete');

    $this->assertTrue($comment->fresh()->trashed());
});

test('an admin can delete any comment', function () {
    $comment = Comment::factory()->create();
    $admin = User::factory()->isSuperAdmin()->create();

    Livewire::actingAs($admin)
        ->test(DeleteComment::class, ['commentId' => $comment->id])
        ->call('delete');

    $this->assertTrue($comment->fresh()->trashed());
});

test('soft deleted comments are hidden from the UI', function () {
    $comment = Comment::factory()->for(User::factory())->create();

    CreateFeedItem::execute($comment, $comment->body);
    $comment->delete();

    $this->assertCount(0, Feed::all());
});

// test('soft deleted comments can be restored by an admin', function () {
//     $comment = Comment::factory()->create();
//     $admin = User::factory()->isSuperAdmin()->create();
//
//     Livewire::actingAs($admin)
//         ->test(DeleteComment::class, ['commentId' => $comment->id])
//         ->call('restore');
//
//     $this->assertFalse($comment->fresh()->trashed());
// });
//
// test('soft deleted comments can be destroyed by an admin', function () {
//     $comment = Comment::factory()->for(User::factory())->create();
//
//     $admin = User::factory()->isSuperAdmin()->create();
//
//     $comment->delete();
//
//     Livewire::actingAs($admin)
//         ->test(DeleteComment::class, ['commentId' => $comment->id])
//         ->call('destroy');
//
//     $this->assertCount(0, Comment::all());
//     $this->assertNull($comment->fresh()?->trashed());
// });

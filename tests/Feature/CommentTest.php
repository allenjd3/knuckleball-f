<?php

use App\Livewire\AddComment;
use App\Models\Comment;
use App\Models\User;

test('the user can make a comment', function () {
    $user = User::factory()->create();
    Livewire::actingAs($user)->test(AddComment::class)
        ->set('body', 'This is a comment')
        ->call('save');

    $this->assertDatabaseHas('comments', [
        'body' => 'This is a comment',
    ]);

    $this->assertTrue(Comment::firstWhere('body', 'This is a comment')->user->is($user));
});

test("the user can reply to a comment", function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create();
    Livewire::actingAs($user)->test(AddComment::class, ['commentId' => $comment->id])
        ->set('body', 'This is a comment')
        ->call('save');

    $this->assertDatabaseHas('comments', [
        'comment_id' => $comment->id,
        'body' => 'This is a comment',
    ]);
});

test("the user can delete their own comment", function () {})->toDo();
test("the user cannot delete another user's comment", function () {})->toDo();
test("the user can edit their own comment", function () {})->toDo();
test("the user cannot edit another user's comment", function () {})->toDo();
test("guests cannot make a comment", function () {})->toDo();
test("comments are shown in the correct order", function () {})->toDo();
test("replies are shown as threaded under the parent comment", function () {})->toDo();
test("the user can like a comment", function () {})->toDo();
test("the user can unlike a comment", function () {})->toDo();
test("the user cannot like the same comment twice", function () {})->toDo();
test("the comment is not saved without content", function () {})->toDo();
test("the comment content has a maximum length", function () {})->toDo();
test("comments are paginated", function () {})->toDo();
test("the comment content is sanitized for HTML", function () {})->toDo();
test("mentions in comments link to user profiles", function () {})->toDo();
test("hashtags in comments link to tag search", function () {})->toDo();
test("a user can report a comment", function () {})->toDo();
test("an admin can see reported comments", function () {})->toDo();
test("an admin can delete any comment", function () {})->toDo();
test("the user is notified when someone replies to their comment", function () {})->toDo();
test("soft deleted comments are hidden from the UI", function () {})->toDo();
test("soft deleted comments can be restored by an admin", function () {})->toDo();
test("the user can load more replies under a comment", function () {})->toDo();
test("long comments are truncated with a 'read more' link", function () {})->toDo();
test("the user can expand a truncated comment", function () {})->toDo();

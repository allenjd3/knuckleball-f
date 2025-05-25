<?php

use App\Livewire\AddComment;
use App\Models\Comment;
use App\Models\User;
use Livewire\Livewire;

test('the user can make a comment', function () {
    $user = User::factory()->create();
    Livewire::actingAs($user)->test(AddComment::class)
        ->fillForm(['body' => 'This is a comment'])
        ->call('save');

    $this->assertDatabaseHas('comments', [
        'body' => 'This is a comment',
    ]);

    $this->assertTrue(Comment::firstWhere('body', 'This is a comment')->user->is($user));
});

test('the user can reply to a comment', function () {
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

test('guests cannot make a comment', function () {
    Livewire::test(AddComment::class)
        ->set('body', 'This is a comment')
        ->call('save');

    $this->assertDatabaseMissing('comments', [
        'body' => 'This is a comment',
    ]);
});

test('the comment is not saved without content', function () {
    $user = User::factory()->create();
    Livewire::actingAs($user)->test(AddComment::class)
        ->fillForm(['body' => ''])
        ->call('save')
        ->assertHasErrors('body');
});


test('the comment content has a maximum length', function () {
    $user = User::factory()->create();
    Livewire::actingAs($user)->test(AddComment::class)
        ->fillForm(['body' => str('l')->repeat(501)->__toString()])
        ->call('save')
        ->assertHasErrors('body');
});

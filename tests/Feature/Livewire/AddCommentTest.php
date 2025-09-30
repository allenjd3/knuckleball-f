<?php

use App\Livewire\AddComment;
use App\Models\Comment;
use App\Models\User;
use Livewire\Livewire;

test('the user can make a comment', function () {
    $user = User::factory()->create();
    Livewire::actingAs($user)->test(AddComment::class)
        ->fillForm(['body' => commentContent('This is a comment')])
        ->call('save');

    $this->assertDatabaseHas('comments', [
        'body' => '<p>This is a comment</p>',
    ]);

    $this->assertTrue(Comment::firstWhere('body', '<p>This is a comment</p>')->user->is($user));
});

test('the user can reply to a comment', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create();
    Livewire::actingAs($user)->test(AddComment::class, ['commentId' => $comment->id])
        ->set('body', commentContent('This is a comment'))
        ->call('save');

    $this->assertDatabaseHas('comments', [
        'comment_id' => $comment->id,
        'body' => '<p>This is a comment</p>',
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
        ->fillForm(['body' => commentContent('')])
        ->call('save')
        ->assertHasErrors('body');
});

test('the comment content has a maximum length', function () {
    $user = User::factory()->create();
    Livewire::actingAs($user)->test(AddComment::class)
        ->fillForm(['body' => commentContent(str('l')->repeat(501)->__toString())])
        ->call('save')
        ->assertHasErrors('body');
});

function commentContent(string $body, ?User $userMention = null)
{
    $mention = $userMention
        ? [
                'type' => 'mention',
                'attrs' => [
                    'id' => $userMention?->id,
                    'label' => "{$userMention?->id} (@{$userMention?->handle})",
                    'href' => $userMention?->path(),
                    'type' => null,
                    'target' => '_blank',
                    'data' => [],
                ]
            ]
        : [];

    $content = [
            [
                'type' => 'text',
                'text' => $body,
            ],
            ...$mention,
        ];

    return [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'attrs' => [
                        'class' => null,
                        'style' => null,
                    ],
                    'content' => $content,
                ]
            ]
        ];
}

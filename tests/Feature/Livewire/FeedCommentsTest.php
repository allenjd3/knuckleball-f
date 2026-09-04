<?php

use App\Livewire\FeedComments;
use App\Models\Comment;
use App\Models\Feed;
use App\Models\User;
use App\Notifications\NewComment;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('a user can post a top-level comment', function () {
    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    $feed = Feed::factory()->forUser($owner)->create();

    Livewire::actingAs($commenter)
        ->test(FeedComments::class, ['feed' => $feed])
        ->call('toggle')
        ->set('body', 'Nice return!')
        ->call('submit');

    $this->assertDatabaseHas('comments', [
        'feed_id' => $feed->id,
        'user_id' => $commenter->id,
        'comment_id' => null,
        'body' => 'Nice return!',
    ]);
});

test('the feed owner is notified of a new top-level comment', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    $feed = Feed::factory()->forUser($owner)->create();

    Livewire::actingAs($commenter)
        ->test(FeedComments::class, ['feed' => $feed])
        ->set('body', 'Nice return!')
        ->call('submit');

    Notification::assertSentTo($owner, NewComment::class, function (NewComment $notification) {
        return $notification->isReply === false;
    });
});

test('starting a reply prefills the input with an @mention of the comment author', function () {
    $commenter = User::factory()->create(['name' => 'Jane Doe']);
    $feed = Feed::factory()->create();
    $comment = Comment::factory()->create(['feed_id' => $feed->id, 'user_id' => $commenter->id]);

    Livewire::actingAs(User::factory()->create())
        ->test(FeedComments::class, ['feed' => $feed])
        ->call('startReply', $comment->id)
        ->assertSet('replyBody', '@Jane Doe ');
});

test('a user can reply to a comment', function () {
    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    $replier = User::factory()->create();
    $feed = Feed::factory()->forUser($owner)->create();
    $comment = Comment::factory()->create(['feed_id' => $feed->id, 'user_id' => $commenter->id]);

    Livewire::actingAs($replier)
        ->test(FeedComments::class, ['feed' => $feed])
        ->call('startReply', $comment->id)
        ->set('replyBody', 'Thanks!')
        ->call('submitReply');

    $this->assertDatabaseHas('comments', [
        'feed_id' => $feed->id,
        'user_id' => $replier->id,
        'comment_id' => $comment->id,
        'body' => 'Thanks!',
    ]);
});

test('replying to a comment notifies the comment author, not the feed owner', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    $replier = User::factory()->create();
    $feed = Feed::factory()->forUser($owner)->create();
    $comment = Comment::factory()->create(['feed_id' => $feed->id, 'user_id' => $commenter->id]);

    Livewire::actingAs($replier)
        ->test(FeedComments::class, ['feed' => $feed])
        ->call('startReply', $comment->id)
        ->set('replyBody', 'Thanks!')
        ->call('submitReply');

    Notification::assertSentTo($commenter, NewComment::class, function (NewComment $notification) {
        return $notification->isReply === true;
    });
    Notification::assertNotSentTo($owner, NewComment::class);
});

test('replying to your own comment does not notify yourself', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    $feed = Feed::factory()->forUser($owner)->create();
    $comment = Comment::factory()->create(['feed_id' => $feed->id, 'user_id' => $commenter->id]);

    Livewire::actingAs($commenter)
        ->test(FeedComments::class, ['feed' => $feed])
        ->call('startReply', $comment->id)
        ->set('replyBody', 'Adding more context.')
        ->call('submitReply');

    Notification::assertNothingSent();
});

test('top-level comments only include replies nested under their parent', function () {
    $feed = Feed::factory()->create();
    $topLevel = Comment::factory()->create(['feed_id' => $feed->id]);
    $reply = Comment::factory()->create(['feed_id' => $feed->id, 'comment_id' => $topLevel->id]);

    $component = Livewire::test(FeedComments::class, ['feed' => $feed])
        ->call('toggle');

    $comments = $component->get('comments');

    expect($comments->pluck('id')->all())->toBe([$topLevel->id])
        ->and($comments->first()->replies->pluck('id')->all())->toBe([$reply->id]);
});

test('the comment count includes replies', function () {
    $feed = Feed::factory()->forUser(User::factory()->create())->create();

    Livewire::actingAs(User::factory()->create())
        ->test(FeedComments::class, ['feed' => $feed])
        ->set('body', 'Top level')
        ->call('submit')
        ->assertSet('commentCount', 1);

    $topLevel = Comment::where('feed_id', $feed->id)->first();
    $feed = Feed::withCount('feedComments')->find($feed->id);

    Livewire::actingAs(User::factory()->create())
        ->test(FeedComments::class, ['feed' => $feed])
        ->call('startReply', $topLevel->id)
        ->set('replyBody', 'A reply')
        ->call('submitReply')
        ->assertSet('commentCount', 2);
});

test('guests cannot reply', function () {
    $feed = Feed::factory()->create();
    $comment = Comment::factory()->create(['feed_id' => $feed->id]);

    Livewire::test(FeedComments::class, ['feed' => $feed])
        ->call('startReply', $comment->id)
        ->set('replyBody', 'Should not save')
        ->call('submitReply');

    $this->assertDatabaseMissing('comments', [
        'body' => 'Should not save',
    ]);
});

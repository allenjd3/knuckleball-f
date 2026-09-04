<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Feed;
use App\Models\User;
use App\Notifications\NewComment;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class FeedComments extends Component
{
    #[Locked]
    public int $feedId;

    public bool $expanded = false;

    public int $commentCount = 0;

    #[Validate('required|max:500')]
    public string $body = '';

    #[Locked]
    public ?int $replyingTo = null;

    #[Validate('required|max:500')]
    public string $replyBody = '';

    public function mount(Feed $feed): void
    {
        $this->feedId = $feed->id;
        $this->commentCount = (int) ($feed->feed_comments_count ?? 0);
    }

    #[Computed]
    public function comments()
    {
        return Comment::where('feed_id', $this->feedId)
            ->whereNull('comment_id')
            ->with(['user', 'replies.user'])
            ->oldest()
            ->get();
    }

    public function toggle(): void
    {
        $this->expanded = ! $this->expanded;
    }

    public function startReply(int $commentId): void
    {
        $comment = Comment::find($commentId);

        $this->replyingTo = $commentId;
        $this->replyBody = $comment ? '@' . $comment->user->name . ' ' : '';
        $this->resetErrorBag('replyBody');
    }

    public function cancelReply(): void
    {
        $this->replyingTo = null;
        $this->replyBody = '';
    }

    public function submit(): void
    {
        if (! auth()->check()) {
            return;
        }

        $this->validate(['body' => 'required|max:500']);

        $comment = Comment::create([
            'feed_id' => $this->feedId,
            'user_id' => auth()->id(),
            'body' => $this->body,
        ]);

        $this->notifyOwnerOfNewComment($comment);

        $this->body = '';
        $this->commentCount++;
        unset($this->comments);
    }

    public function submitReply(): void
    {
        if (! auth()->check() || ! $this->replyingTo) {
            return;
        }

        $this->validate(['replyBody' => 'required|max:500']);

        $parent = Comment::find($this->replyingTo);

        $reply = Comment::create([
            'feed_id' => $this->feedId,
            'user_id' => auth()->id(),
            'comment_id' => $this->replyingTo,
            'body' => $this->replyBody,
        ]);

        if ($parent && $parent->user_id !== auth()->id()) {
            $parent->user?->notify(new NewComment(auth()->user(), Feed::find($this->feedId), $this->replyBody, isReply: true));
        }

        $this->cancelReply();
        $this->commentCount++;
        unset($this->comments);
    }

    public function render()
    {
        return view('livewire.feed-comments');
    }

    private function notifyOwnerOfNewComment(Comment $comment): void
    {
        $feed = Feed::find($this->feedId);
        $owner = $feed ? User::find($feed->followable_id) : null;

        if ($owner && $owner->id !== auth()->id()) {
            $owner->notify(new NewComment(auth()->user(), $feed, $comment->body));
        }
    }
}

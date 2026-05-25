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

    public function mount(Feed $feed): void
    {
        $this->feedId = $feed->id;
        $this->commentCount = (int) ($feed->feed_comments_count ?? 0);
    }

    #[Computed]
    public function comments()
    {
        return Comment::where('feed_id', $this->feedId)
            ->with('user')
            ->oldest()
            ->get();
    }

    public function toggle(): void
    {
        $this->expanded = ! $this->expanded;
    }

    public function submit(): void
    {
        if (! auth()->check()) {
            return;
        }

        $this->validate();

        $feed = Feed::find($this->feedId);

        Comment::create([
            'feed_id' => $this->feedId,
            'user_id' => auth()->id(),
            'body' => $this->body,
        ]);

        $owner = $feed ? User::find($feed->followable_id) : null;
        if ($owner && $owner->id !== auth()->id()) {
            $owner->notify(new NewComment(auth()->user(), $feed, $this->body));
        }

        $this->body = '';
        $this->commentCount++;
        unset($this->comments);
    }

    public function render()
    {
        return view('livewire.feed-comments');
    }
}

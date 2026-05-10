<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Feed;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class FeedComments extends Component
{
    #[Locked]
    public int $feedId;

    public bool $expanded = false;

    #[Validate('required|max:500')]
    public string $body = '';

    public function mount(Feed $feed): void
    {
        $this->feedId = $feed->id;
    }

    #[Computed]
    public function comments()
    {
        return Comment::where('feed_id', $this->feedId)
            ->with('user')
            ->oldest()
            ->get();
    }

    #[Computed]
    public function commentCount(): int
    {
        return Comment::where('feed_id', $this->feedId)->count();
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

        Comment::create([
            'feed_id' => $this->feedId,
            'user_id' => auth()->id(),
            'body' => $this->body,
        ]);

        $this->body = '';
        unset($this->comments, $this->commentCount);
    }

    public function render()
    {
        return view('livewire.feed-comments');
    }
}

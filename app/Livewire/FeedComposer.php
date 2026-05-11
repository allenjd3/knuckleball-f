<?php

namespace App\Livewire;

use App\Actions\CreateFeedItem;
use App\Models\Comment;
use Livewire\Attributes\Validate;
use Livewire\Component;

class FeedComposer extends Component
{
    #[Validate('required|string|min:1|max:500')]
    public string $body = '';

    public function submit(): void
    {
        if (! auth()->check()) {
            return;
        }

        $this->validate();

        $comment = auth()->user()->comments()->create([
            'body' => $this->body,
        ]);

        CreateFeedItem::execute($comment, $comment->body);

        $this->body = '';
        $this->dispatch('feed-updated');
    }

    public function render()
    {
        return view('livewire.feed-composer');
    }
}

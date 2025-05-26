<?php

namespace App\Livewire;

use App\Models\Comment;
use Livewire\Component;

class DeleteComment extends Component
{
    public int $commentId;

    public function mount($commentId = 0)
    {
        $this->commentId = $commentId;
    }

    public function render()
    {
        return view('livewire.delete-comment');
    }

    public function delete()
    {
        $comment = Comment::find($this->commentId);

        $this->authorize('delete', $comment);

        $comment->delete();

        $this->dispatch('feed-updated');
    }
}

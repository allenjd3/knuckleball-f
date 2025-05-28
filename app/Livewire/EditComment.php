<?php

namespace App\Livewire;

use App\Models\Comment;
use Livewire\Attributes\Locked;
use Livewire\Component;

class EditComment extends Component
{
    public string $body;

    #[Locked]
    public ?int $commentId = null;

    public function mount(?int $commentId = null)
    {
        $this->commentId = $commentId;
    }

    public function render()
    {
        return view('livewire.edit-comment');
    }

    public function delete()
    {
        $comment = Comment::find($this->commentId);
        $this->authorize('delete', $comment);

        $comment->delete();
    }

    public function edit()
    {
        $comment = Comment::find($this->commentId);
        $this->authorize('update', $comment);

        $comment->update([
            'body' => $this->body,
        ]);
    }
}

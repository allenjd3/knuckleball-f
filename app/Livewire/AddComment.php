<?php

namespace App\Livewire;

use App\Models\Comment;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AddComment extends Component
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
        return view('livewire.add-comment');
    }

    public function save()
    {
        if ($this->commentId) {
            auth()->user()
                ?->comments()
                ->create(['body' => $this->body, 'comment_id' => $this->commentId]);
        } else {
            auth()->user()
                ?->comments()
                ->create(['body' => $this->body]);
        }
    }
}

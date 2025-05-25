<?php

namespace App\Livewire;

use App\Actions\CreateFeedItem;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AddComment extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public string $body;

    #[Locked]
    public ?int $commentId = null;

    public function mount(?int $commentId = null)
    {
        $this->form->fill(['body' => '']);
        $this->commentId = $commentId;
    }

    public function render()
    {
        return view('livewire.add-comment');
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('')
            ->schema([
                Textarea::make('body')
                    ->label('Comment')
                    ->minLength(0)
                    ->maxLength(500)
            ]);
    }

    public function save()
    {
        $comment = auth()->user()
            ?->comments()
            ->create(['body' => $this->body, 'comment_id' => $this->commentId]);

        CreateFeedItem::execute($comment, $comment->body);
        $this->dispatch('feed-updated');
    }
}

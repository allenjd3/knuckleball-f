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
                Grid::make(1)->schema([
                    Textarea::make('body')
                        ->label('Comment')
                        ->minLength(0)
                        ->maxLength(500)
                        ->extraFieldWrapperAttributes([
                            'style' => 'grid-column: 1 / -1 !important;',
                        ])
                        ->columnSpan('full'),
                ]),
            ]);
    }

    public function save()
    {
        if ($this->commentId) {
            $comment = auth()->user()
                ?->comments()
                ->create(['body' => $this->body, 'comment_id' => $this->commentId]);
        } else {
            $comment = auth()->user()
                ?->comments()
                ->create(['body' => $this->body]);
        }

        CreateFeedItem::execute($comment, $comment->body);
        $this->dispatch('comment-created');
    }
}

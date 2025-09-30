<?php

namespace App\Livewire;

use App\Actions\CreateFeedItem;
use App\Actions\ReplacePastedLinks;
use App\Models\Comment;
use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use FilamentTiptapEditor\Concerns\HasFormMentions;
use FilamentTiptapEditor\Data\MentionItem;
use FilamentTiptapEditor\Enums\TiptapOutput;
use FilamentTiptapEditor\Facades\TiptapConverter;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AddComment extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use HasFormMentions;

    public $body;

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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TiptapEditor::make('body')
                    ->label('Comment')
                    ->getMentionItemsUsing(
                        fn ($query) => User::where('handle', 'like', $query . '%')
                            ->limit(10)
                            ->get()
                            ->map(fn ($user) => new MentionItem(
                                id: $user->id,
                                label: "{$user->name} (@{$user->handle})",
                                href: $user->path(),
                            ))
                            ->toArray()
                    )
                    ->profile('none')
                    ->output(TiptapOutput::Html)
                    ->maxContentWidth('5xl'),
            ]);
    }

    public function save()
    {
        $this->authorize('create', Comment::class);
        $mentionIds = $this->extractMentionIds($this->body);

        $validator = Validator::make([
            'body' => TiptapConverter::asHTML($this->body),
        ], [
            'body' => ['min:1', 'max:500', 'required'],
        ]);

        $body = data_get($validator->validated(), 'body');

        $bodyWithReplacedLinks = ReplacePastedLinks::handle($body);

        $comment = auth()->user()
            ?->comments()
            ->create([
                'body' => $bodyWithReplacedLinks,
                'comment_id' => $this->commentId,
            ]);

        $feed = CreateFeedItem::execute($comment, $comment->body);
        $mentionIds->each(fn ($id) => $feed->mention(User::find($id)));

        $this->dispatch('feed-updated');
        unset($this->body);
    }

    private function extractMentionIds(array $comment)
    {
        return collect(data_get($comment, 'content.*.content'))
            ->flatten(1)
            ->filter(fn ($text) => data_get($text, 'type') == 'mention')
            ->map(fn ($mentions) => data_get($mentions, 'attrs'))
            ->pluck('id');
    }
}

<?php

namespace App\Livewire;

use Filament\Schemas\Schema;
use App\Actions\CreateFeedItem;
use App\Actions\ReplacePastedLinks;
use App\Models\Comment;
use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
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
    use HasFormMentions;
    use InteractsWithActions;
    use InteractsWithForms;

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

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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

        $validator = Validator::make([
            'body' => TiptapConverter::asText($this->body),
        ], [
            'body' => ['min:1', 'max:500', 'required'],
        ]);

        $validator->validated();

        $mentionIds = $this->extractMentionIds($this->body);

        $body = TiptapConverter::asHTML($this->body);

        $bodyWithReplacedLinks = ReplacePastedLinks::handle($body);

        $comment = auth()->user()
            ?->comments()
            ->create([
                'body' => $bodyWithReplacedLinks,
                'comment_id' => $this->commentId,
            ]);

        $feed = CreateFeedItem::execute($comment, $comment->body);
        $mentionIds->each(function ($id) use ($feed) {
            if ($user = User::find($id)) {
                $feed->mention($user);
            }
        });

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

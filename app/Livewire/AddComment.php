<?php

namespace App\Livewire;

use App\Actions\CreateFeedItem;
use App\Actions\ReplacePastedLinks;
use App\Models\Comment;
use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\MentionProvider;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AddComment extends Component implements HasActions, HasForms
{
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
                RichEditor::make('body')
                    ->label('Comment')
                    ->toolbarButtons([])
                    ->mentions([
                        MentionProvider::make('@')
                            ->getSearchResultsUsing(fn (string $search) => User::where('handle', 'like', $search . '%')
                                ->limit(10)
                                ->get()
                                ->pluck('name', 'id')
                                ->all()
                            )
                            ->getLabelsUsing(fn (array $ids) => User::query()
                                ->whereIn('id', $ids)
                                ->pluck('name', 'id')
                                ->all()
                            ),
                    ]),
            ]);
    }

    public function save()
    {
        $this->authorize('create', Comment::class);

        $data = $this->form->getState();
        $validator = Validator::make(['body' => RichContentRenderer::make($this->body)->toText()], [
            'body' => ['required', 'string', 'min:1', 'max:500'],
        ]);
        $validator->validated();

        $htmlBody = RichContentRenderer::make($data['body'])
            ->mentions([
                MentionProvider::make('@')
                    ->url(fn (string $id, string $label): string => route('users.profile', [
                        'user' => User::find($id),
                    ])
                    ),
            ])
            ->toHtml();

        $mentionIds = $this->extractMentionIds($this->body);

        $bodyWithReplacedLinks = ReplacePastedLinks::handle($htmlBody);

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

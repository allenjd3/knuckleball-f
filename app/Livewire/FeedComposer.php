<?php

namespace App\Livewire;

use App\Actions\CreateFeedItem;
use App\Models\Comment;
use App\Models\FeeMaterial;
use App\Models\Player;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Validate;
use Livewire\Component;

class FeedComposer extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

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

    public function logSendAction(): Action
    {
        return Action::make('logSend')
            ->label('Log a Send')
            ->icon('heroicon-o-paper-airplane')
            ->modalHeading('Log a Send')
            ->modalSubmitActionLabel('Log Send')
            ->form([
                Select::make('player_id')
                    ->label('Player')
                    ->required()
                    ->searchable()
                    ->getSearchResultsUsing(
                        fn (string $search) => Player::where('name', 'like', "%{$search}%")
                            ->limit(20)
                            ->pluck('name', 'id')
                    )
                    ->getOptionLabelUsing(fn ($value) => Player::find($value)?->name),
                DatePicker::make('date_sent')
                    ->label('Date Sent')
                    ->required()
                    ->default(now()),
                Select::make('fee_material_id')
                    ->label('Material')
                    ->required()
                    ->options(fn () => FeeMaterial::pluck('name', 'id')->toArray())
                    ->searchable(),
                Textarea::make('comment')
                    ->label('Note')
                    ->placeholder('Anything worth noting about the send…')
                    ->nullable()
                    ->maxLength(255),
            ])
            ->action(function (array $data) {
                $player = Player::with('signer')->findOrFail($data['player_id']);

                $postalMail = auth()->user()->postalMails()->create([
                    'signer_id'       => $player->signer->id,
                    'date_sent'       => $data['date_sent'],
                    'fee_material_id' => $data['fee_material_id'],
                    'comment'         => $data['comment'] ?? null,
                ]);

                $postalMail->feeMaterials()->attach($data['fee_material_id']);

                CreateFeedItem::execute($postalMail, $postalMail->comment);
                $this->dispatch('feed-updated');
            });
    }

    public function logReturnAction(): Action
    {
        return Action::make('logReturn')
            ->label('Log a Return')
            ->icon('heroicon-o-star')
            ->modalHeading('Log a Return')
            ->modalSubmitActionLabel('Log Return')
            ->form([
                Select::make('postal_mail_id')
                    ->label('Pending Send')
                    ->required()
                    ->searchable()
                    ->options(function () {
                        return auth()->user()
                            ->postalMails()
                            ->whereNull('returned_date')
                            ->where('is_failed', false)
                            ->with(['signer.signable'])
                            ->orderByDesc('date_sent')
                            ->get()
                            ->mapWithKeys(function ($pm) {
                                $name = optional($pm->player)->name ?? 'Unknown';
                                $date = $pm->date_sent?->format('M j, Y') ?? '—';
                                return [$pm->id => "{$name} · sent {$date}"];
                            });
                    }),
                DatePicker::make('returned_date')
                    ->label('Date Returned')
                    ->required()
                    ->default(now()),
                Textarea::make('comment')
                    ->label('Note')
                    ->placeholder('Anything worth sharing about the return…')
                    ->nullable()
                    ->maxLength(255),
            ])
            ->action(function (array $data) {
                $postalMail = auth()->user()
                    ->postalMails()
                    ->findOrFail($data['postal_mail_id']);

                $postalMail->update([
                    'returned_date' => $data['returned_date'],
                    'comment'       => $data['comment'] ?? $postalMail->comment,
                ]);

                // PostalMail::booted() updated hook calls UpdateFeedItem automatically,
                // which upgrades the feed card from send-card to celebration-card.
                $this->dispatch('feed-updated');
            });
    }

    public function render()
    {
        return view('livewire.feed-composer');
    }
}

<?php

namespace App\Livewire;

use App\Actions\CreateFeedItem;
use App\Enums\SetEntryStatus;
use App\Http\Controllers\ReturnCardController;
use App\Jobs\GenerateReturnCard;
use App\Models\FeeMaterial;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\SetEntry;
use App\Services\ReturnCardService;
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

    // Share return prompt
    public ?int $shareMailId = null;
    public bool $shareOpen = false;
    public bool $shareGenerating = false;

    // Set connection prompt (shown after logging a return if player is in Need It sets)
    public bool $setPromptOpen = false;
    public array $setPromptEntries = [];

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
                    'signer_id' => $player->signer->id,
                    'date_sent' => $data['date_sent'],
                    'fee_material_id' => $data['fee_material_id'],
                    'comment' => $data['comment'] ?? null,
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
                    'comment' => $data['comment'] ?? $postalMail->comment,
                ]);

                // PostalMail::booted() updated hook calls UpdateFeedItem automatically,
                // which upgrades the feed card from send-card to celebration-card.
                $this->dispatch('feed-updated');

                // Trigger the share prompt
                $this->shareMailId = $postalMail->id;
                $this->shareOpen = true;
                GenerateReturnCard::dispatch($postalMail->id);

                // Check if this player is in any Need It set entries
                $postalMail->load('signer.signable');
                $player = $postalMail->signer?->signable;
                if ($player instanceof Player) {
                    $entries = SetEntry::where('status', SetEntryStatus::NeedIt->value)
                        ->where('player_id', $player->id)
                        ->whereHas('cardSet', fn ($q) => $q->where('user_id', auth()->id()))
                        ->with('cardSet')
                        ->get();

                    if ($entries->isNotEmpty()) {
                        $this->setPromptEntries = $entries->map(fn ($e) => [
                            'id' => $e->id,
                            'set_name' => $e->cardSet->name,
                            'set_path' => $e->cardSet->path(),
                        ])->toArray();
                        $this->setPromptOpen = true;
                    }
                }
            });
    }

    public function closeShare(): void
    {
        $this->shareOpen = false;
        $this->shareMailId = null;
    }

    public function markSetEntriesSigned(): void
    {
        $ids = collect($this->setPromptEntries)->pluck('id');
        SetEntry::whereIn('id', $ids)
            ->whereHas('cardSet', fn ($q) => $q->where('user_id', auth()->id()))
            ->update([
                'status' => SetEntryStatus::HaveItSigned->value,
                'date_signed' => now()->toDateString(),
            ]);
        $this->setPromptOpen = false;
        $this->setPromptEntries = [];
    }

    public function dismissSetPrompt(): void
    {
        $this->setPromptOpen = false;
        $this->setPromptEntries = [];
    }

    public function shareSquareUrl(): string
    {
        if (! $this->shareMailId) {
            return '#';
        }
        $mail = PostalMail::find($this->shareMailId);

        return $mail ? ReturnCardController::signedUrl($mail, 'square') : '#';
    }

    public function shareStoryUrl(): string
    {
        if (! $this->shareMailId) {
            return '#';
        }
        $mail = PostalMail::find($this->shareMailId);

        return $mail ? ReturnCardController::signedUrl($mail, 'story') : '#';
    }

    public function sharePreviewUrl(): ?string
    {
        if (! $this->shareMailId) {
            return null;
        }
        $mail = PostalMail::find($this->shareMailId);

        return $mail ? app(ReturnCardService::class)->previewUrl($mail, 'square') : null;
    }

    public function sharePublicUrl(): string
    {
        return $this->shareMailId ? route('returns.show', $this->shareMailId) : '#';
    }

    public function render()
    {
        return view('livewire.feed-composer');
    }
}

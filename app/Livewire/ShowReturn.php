<?php

namespace App\Livewire;

use App\Http\Controllers\ReturnCardController;
use App\Models\PostalMail;
use App\Services\ReturnCardService;
use Livewire\Component;

class ShowReturn extends Component
{
    public PostalMail $mail;

    public bool $shareOpen = false;
    public string $copySuccess = '';

    public function mount(PostalMail $mail): void
    {
        abort_unless($mail->returned_date !== null, 404);

        $this->mail = $mail;

        // Pre-generate cards so the OG image is ready immediately for crawlers
        app(ReturnCardService::class)->getOrGenerate($mail, 'square');
        app(ReturnCardService::class)->getOrGenerate($mail, 'story');
    }

    public function openShare(): void
    {
        // Ensure cards exist before showing the prompt
        app(ReturnCardService::class)->getOrGenerate($this->mail, 'square');
        app(ReturnCardService::class)->getOrGenerate($this->mail, 'story');
        $this->shareOpen = true;
    }

    public function squareUrl(): string
    {
        return ReturnCardController::signedUrl($this->mail, 'square');
    }

    public function storyUrl(): string
    {
        return ReturnCardController::signedUrl($this->mail, 'story');
    }

    public function squarePreviewUrl(): ?string
    {
        return app(ReturnCardService::class)->previewUrl($this->mail, 'square');
    }

    public function ogImageUrl(): ?string
    {
        $relative = app(ReturnCardService::class)->previewUrl($this->mail, 'square');

        return $relative ? url($relative) : null;
    }

    public function render()
    {
        $this->mail->loadMissing([
            'user',
            'signer.signable.media',
            'signer.signable.team.category',
            'cards.media',
            'feeMaterials',
            'signer.fees',
        ]);

        $player = $this->mail->player;
        $meta = $this->mail->generateMeta();

        return view('livewire.show-return', [
            'player' => $player,
            'meta' => $meta,
            'publicUrl' => route('returns.show', $this->mail),
        ])->layout('layouts.app');
    }
}

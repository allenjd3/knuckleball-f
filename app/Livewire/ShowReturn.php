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

        $service = app(ReturnCardService::class);

        if (request()->boolean('refresh')) {
            $service->generate($mail);
        } else {
            $service->getOrGenerate($mail, 'square');
            $service->getOrGenerate($mail, 'story');
        }
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
            'headline' => $this->headline(),
            'ogTitle' => $this->ogTitle($meta),
            'ogDescription' => $this->ogDescription($meta),
            'publicUrl' => route('returns.show', $this->mail),
        ])->layout('layouts.app');
    }

    private function headline(): string
    {
        return $this->mail->is_failed ? 'Got a failed return' : 'Got it back!';
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function ogTitle(array $meta): string
    {
        $player = data_get($meta, 'player', 'TTM Return');
        $days = data_get($meta, 'turnaround_days');

        return match (true) {
            $this->mail->is_failed => "{$player} — Failed Return",
            $days !== null => "{$player} — {$days} Day Return",
            default => "{$player} — TTM Return",
        };
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function ogDescription(array $meta): string
    {
        $player = data_get($meta, 'player', 'TTM Return');
        $user = data_get($meta, 'user', '');
        $days = data_get($meta, 'turnaround_days');

        $summary = match (true) {
            $this->mail->is_failed => "{$user} got a failed return from {$player}",
            $days !== null => "{$user} got a TTM return from {$player} in {$days} days",
            default => "{$user} got a TTM return from {$player}",
        };

        return "{$summary}. Track your own returns on Knuckleball.";
    }
}

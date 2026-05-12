<?php

namespace App\Services;

use App\Models\Player;
use App\Models\PostalMail;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;

class ReturnCardService
{
    private ImageManager $manager;
    private string $boldFont;
    private string $regularFont;
    private string $mediumFont;

    // Brand colours
    private const BG     = '#0F1117';
    private const RED    = '#D93C3F';
    private const MUTED  = '#9CA3AF';
    private const BADGE  = '#1E2230';
    private const BORDER = '#3A4055';

    public function __construct()
    {
        $this->manager     = new ImageManager(new Driver());
        $this->boldFont    = resource_path('fonts/Inter-Bold.ttf');
        $this->mediumFont  = resource_path('fonts/Inter-Medium.ttf');
        $this->regularFont = resource_path('fonts/Inter-Regular.ttf');
    }

    // ── Public API ────────────────────────────────────────────────

    /**
     * Return storage path for a format, generating if not already cached.
     * Files are stored on the `public` disk so they can be served directly.
     */
    public function getOrGenerate(PostalMail $mail, string $format): string
    {
        $path = $this->storagePath($mail->id, $format);

        if (! Storage::disk('public')->exists($path)) {
            $this->generate($mail);
        }

        return $path;
    }

    /**
     * Force-regenerate both formats and return their public-disk paths.
     */
    public function generate(PostalMail $mail): array
    {
        $data = $this->collectData($mail);

        return [
            'square' => $this->buildSquare($mail->id, $data),
            'story'  => $this->buildStory($mail->id, $data),
        ];
    }

    /**
     * Publicly accessible URL for a generated card (for browser preview).
     */
    public function previewUrl(PostalMail $mail, string $format): ?string
    {
        $path = $this->storagePath($mail->id, $format);

        return Storage::disk('public')->exists($path)
            ? Storage::disk('public')->url($path)
            : null;
    }

    // ── Data collection ───────────────────────────────────────────

    private function collectData(PostalMail $mail): array
    {
        $mail->loadMissing([
            'user',
            'signer.signable.media',
            'signer.signable.team.category',
            'cards.media',
            'feeMaterials',
            'signer.fees',
        ]);

        $player     = $mail->player;
        $playerName = $player?->name ?? 'Unknown Player';

        $playerPhotoPath = $this->resolveStoragePath($player instanceof Player ? $player->media?->url : null);
        $cardPhotoPath   = $this->resolveStoragePath($mail->cards->first()?->media->first()?->url);

        $turnaroundDays = ($mail->returned_date && $mail->date_sent)
            ? (int) $mail->date_sent->diffInDays($mail->returned_date)
            : null;

        $feeText = 'NO FEE';
        if ($feeMaterial = $mail->feeMaterials->first()) {
            $fee = $mail->signer->fees()
                ->where('fee_material_id', $feeMaterial->id)
                ->whereNotNull('published_at')
                ->first();
            if ($fee && $fee->amount > 0) {
                $feeText = 'FEE: $' . $fee->amount;
            }
        }

        return [
            'playerName'      => $playerName,
            'playerPhotoPath' => $playerPhotoPath,
            'cardPhotoPath'   => $cardPhotoPath,
            'turnaroundDays'  => $turnaroundDays,
            'feeText'         => $feeText,
            'userName'        => $mail->user->name ?? 'Unknown',
        ];
    }

    private function resolveStoragePath(?string $url): ?string
    {
        if (! $url) return null;

        if (Storage::disk('public')->exists($url)) {
            return Storage::disk('public')->path($url);
        }

        return null;
    }

    // ── Square 1080×1080 ─────────────────────────────────────────

    private function buildSquare(int $mailId, array $d): string
    {
        $canvas = $this->manager->create(1080, 1080);
        $canvas->fill(self::BG);

        // Hero image
        $heroPath = $d['cardPhotoPath'] ?? $d['playerPhotoPath'];
        if ($heroPath) {
            try {
                $hero = $this->manager->read($heroPath)->cover(1080, 540);
                $canvas->place($hero, 'top-left', 0, 0);
                // Bottom fade overlay
                $fade = $this->manager->create(1080, 300)->fill(self::BG);
                $canvas->place($fade, 'top-left', 0, 240, 75);
            } catch (\Throwable) { /* no hero – dark bg is fine */ }
        }

        // Top bar
        $canvas->place($this->manager->create(1080, 70)->fill(self::BG), 'top-left', 0, 0, 88);
        $canvas->text('KNUCKLEBALL', 56, 42, fn (FontFactory $f) => $f
            ->filename($this->boldFont)->size(22)->color(self::RED)->align('left')->valign('middle'));

        // Player name
        $canvas->text(strtoupper($d['playerName']), 540, 620, fn (FontFactory $f) => $f
            ->filename($this->boldFont)->size(76)->color('#FFFFFF')
            ->align('center')->valign('middle')->lineHeight(1.15)->wrap(970));

        // Badges
        $this->drawBadges($canvas, $d, 718, 200, 160, 16, 22, 17);

        // "got it back" line
        $canvas->text($d['userName'] . ' Got it back!', 540, 843, fn (FontFactory $f) => $f
            ->filename($this->regularFont)->size(24)->color(self::MUTED)->align('center')->valign('middle'));

        // Accent line
        $canvas->drawRectangle(480, 936, fn (RectangleFactory $r) => $r->size(120, 3)->background(self::RED));

        // Domain
        $canvas->text('knuckleball.app', 540, 1002, fn (FontFactory $f) => $f
            ->filename($this->regularFont)->size(18)->color('#4B5563')->align('center')->valign('middle'));

        $path = $this->storagePath($mailId, 'square');
        Storage::disk('public')->put($path, $canvas->toJpeg(92)->toString());

        return $path;
    }

    // ── Story 1080×1920 ──────────────────────────────────────────

    private function buildStory(int $mailId, array $d): string
    {
        $canvas = $this->manager->create(1080, 1920);
        $canvas->fill(self::BG);

        // Hero image (taller)
        $heroPath = $d['cardPhotoPath'] ?? $d['playerPhotoPath'];
        if ($heroPath) {
            try {
                $hero = $this->manager->read($heroPath)->cover(1080, 1000);
                $canvas->place($hero, 'top-left', 0, 0);
                $fade = $this->manager->create(1080, 500)->fill(self::BG);
                $canvas->place($fade, 'top-left', 0, 500, 75);
            } catch (\Throwable) { /* dark bg */ }
        }

        // Top bar
        $canvas->place($this->manager->create(1080, 88)->fill(self::BG), 'top-left', 0, 0, 88);
        $canvas->text('KNUCKLEBALL', 56, 54, fn (FontFactory $f) => $f
            ->filename($this->boldFont)->size(26)->color(self::RED)->align('left')->valign('middle'));

        // Player name
        $canvas->text(strtoupper($d['playerName']), 540, 1090, fn (FontFactory $f) => $f
            ->filename($this->boldFont)->size(88)->color('#FFFFFF')
            ->align('center')->valign('middle')->lineHeight(1.15)->wrap(980));

        // Badges (larger, side by side)
        $this->drawBadges($canvas, $d, 1270, 240, 196, 20, 26, 20, 72);

        // "got it back" line
        $canvas->text($d['userName'] . ' Got it back!', 540, 1450, fn (FontFactory $f) => $f
            ->filename($this->regularFont)->size(30)->color(self::MUTED)->align('center')->valign('middle'));

        // Accent line
        $canvas->drawRectangle(480, 1620, fn (RectangleFactory $r) => $r->size(120, 3)->background(self::RED));

        // Domain
        $canvas->text('knuckleball.app', 540, 1872, fn (FontFactory $f) => $f
            ->filename($this->regularFont)->size(22)->color('#4B5563')->align('center')->valign('middle'));

        $path = $this->storagePath($mailId, 'story');
        Storage::disk('public')->put($path, $canvas->toJpeg(92)->toString());

        return $path;
    }

    // ── Shared helpers ────────────────────────────────────────────

    /**
     * Draw the turnaround + fee badges centred on the canvas at the given $y position.
     */
    private function drawBadges(
        $canvas,
        array $d,
        int $y,
        int $daysWidth,
        int $feeWidth,
        int $gap,
        int $daysFontSize,
        int $feeFontSize,
        int $badgeH = 56,
    ): void {
        $totalW = $daysWidth + $gap + $feeWidth;
        $startX = (int) ((1080 - $totalW) / 2);

        $daysText = $d['turnaroundDays'] !== null ? "{$d['turnaroundDays']} DAYS" : '— DAYS';

        // Turnaround badge (red)
        $canvas->drawRectangle($startX, $y, fn (RectangleFactory $r) =>
            $r->size($daysWidth, $badgeH)->background(self::RED));
        $canvas->text($daysText, $startX + intdiv($daysWidth, 2), $y + intdiv($badgeH, 2), fn (FontFactory $f) => $f
            ->filename($this->boldFont)->size($daysFontSize)->color('#FFFFFF')->align('center')->valign('middle'));

        // Fee badge (dark)
        $feeX = $startX + $daysWidth + $gap;
        $canvas->drawRectangle($feeX, $y, fn (RectangleFactory $r) =>
            $r->size($feeWidth, $badgeH)->background(self::BADGE)->border(self::BORDER));
        $canvas->text($d['feeText'], $feeX + intdiv($feeWidth, 2), $y + intdiv($badgeH, 2), fn (FontFactory $f) => $f
            ->filename($this->mediumFont)->size($feeFontSize)->color(self::MUTED)->align('center')->valign('middle'));
    }

    private function storagePath(int $mailId, string $format): string
    {
        return "return-cards/{$mailId}/{$format}.jpg";
    }
}

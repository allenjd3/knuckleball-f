<?php

namespace App\Http\Controllers;

use App\Models\PostalMail;
use App\Services\ReturnCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReturnCardController extends Controller
{
    /**
     * Generate (or retrieve) a signed download URL for a card format.
     * Called from the Livewire share prompt.
     */
    public static function signedUrl(PostalMail $mail, string $format): string
    {
        return URL::temporarySignedRoute(
            'returns.card.download',
            now()->addHours(24),
            ['mail' => $mail->id, 'format' => $format],
        );
    }

    /**
     * Serve the generated image as a download.
     * Route is protected by a signed URL (no auth required, expires 24h).
     */
    public function download(Request $request, PostalMail $mail, string $format): StreamedResponse
    {
        abort_unless(in_array($format, ['square', 'story']), 404);
        abort_unless($request->hasValidSignature(), 403);

        $service = app(ReturnCardService::class);
        $path = $service->getOrGenerate($mail, $format);

        abort_unless(Storage::exists($path), 404);

        $player = $mail->player;
        $slug = str($player?->name ?? 'return')->slug('-');
        $filename = "knuckleball-{$slug}-{$format}.jpg";

        return Storage::download($path, $filename, ['Content-Type' => 'image/jpeg']);
    }
}

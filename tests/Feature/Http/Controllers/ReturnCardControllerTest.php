<?php

use App\Http\Controllers\ReturnCardController;
use App\Models\PostalMail;
use App\Services\ReturnCardService;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;

beforeEach(function () {
    Storage::fake();
});

it('serves a jpeg download via a valid signed url', function (string $format) {
    $mail = PostalMail::factory()->returned()->create();

    $service = $this->mock(ReturnCardService::class);
    $service->shouldReceive('getOrGenerate')
        ->once()
        ->with(Mockery::on(fn ($m) => $m->id === $mail->id), $format)
        ->andReturnUsing(function () use ($mail, $format) {
            $path = "return-cards/{$mail->id}/{$format}.jpg";
            Storage::put($path, 'fake-image-bytes');

            return $path;
        });

    $url = ReturnCardController::signedUrl($mail, $format);

    get($url)
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'image/jpeg')
        ->assertDownload();
})->with(['square', 'story']);

it('returns 403 for an invalid signature', function () {
    $mail = PostalMail::factory()->returned()->create();

    get(route('returns.card.download', ['mail' => $mail->id, 'format' => 'square']))
        ->assertForbidden();
});

it('returns 404 for an unknown format', function () {
    $mail = PostalMail::factory()->returned()->create();

    $url = ReturnCardController::signedUrl($mail, 'square');
    $url = str_replace('/square?', '/invalid?', $url);

    get($url)->assertNotFound();
});

it('returns 404 when the generated file does not exist on storage', function () {
    $mail = PostalMail::factory()->returned()->create();

    $service = $this->mock(ReturnCardService::class);
    $service->shouldReceive('getOrGenerate')->once()->andReturn('return-cards/1/square.jpg');

    $url = ReturnCardController::signedUrl($mail, 'square');

    get($url)->assertNotFound();
});

<?php

use App\Models\PostalMail;
use App\Models\Signer;
use App\Services\ReturnCardService;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

beforeEach(function () {
    Storage::fake();
});

function pixelIsCloseTo(array $pixel, array $rgb, int $tolerance = 12): bool
{
    return abs($pixel[0] - $rgb[0]) <= $tolerance
        && abs($pixel[1] - $rgb[1]) <= $tolerance
        && abs($pixel[2] - $rgb[2]) <= $tolerance;
}

it('letterboxes an extreme-aspect hero image instead of cropping it', function () {
    $signer = Signer::factory()->create();
    $mail = PostalMail::factory()->returned()->create(['signer_id' => $signer->id]);

    $player = $signer->signable;
    $heroPath = 'players/hero.png';

    // A very tall, narrow, solid-red source: contain() must shrink it to fit
    // fully inside the 1080x540 hero band, leaving the BG colour visible in
    // the letterboxed margins either side. cover() would instead crop it to
    // fill the whole band with red, with no BG margin visible.
    $source = (new ImageManager(new Driver))->create(50, 1000)->fill('#FF0000');
    Storage::put($heroPath, $source->toPng()->toString());
    $player->media()->create(['url' => $heroPath]);

    $paths = app(ReturnCardService::class)->generate($mail->fresh());

    $manager = new ImageManager(new Driver);
    $result = $manager->read(Storage::get($paths['square']));

    // y=150 sits below the top bar overlay and above the gradient fade, so it
    // reflects only the hero placement itself.
    $margin = $result->pickColor(5, 150)->toArray();
    $center = $result->pickColor(540, 150)->toArray();

    expect(pixelIsCloseTo($margin, [15, 17, 23]))->toBeTrue()
        ->and(pixelIsCloseTo($center, [255, 0, 0]))->toBeTrue();
});

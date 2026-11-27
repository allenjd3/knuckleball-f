<?php

use App\Models\PostalMail;
use App\Services\ReturnCardService;

use function Pest\Laravel\get;

beforeEach(function () {
    $service = $this->mock(ReturnCardService::class);
    $service->shouldReceive('getOrGenerate')->andReturn('return-cards/fake.jpg');
    $service->shouldReceive('previewUrl')->andReturn(null);
});

it('describes a successful return as a return', function () {
    $mail = PostalMail::factory()->returned()->create();

    get(route('returns.show', $mail))
        ->assertSuccessful()
        ->assertSee('Got it back!')
        ->assertSee('Day Return')
        ->assertDontSee('Failed Return');
});

it('describes a failed return as failed', function () {
    $mail = PostalMail::factory()->failed()->create(['returned_date' => now()->subDay()]);

    get(route('returns.show', $mail))
        ->assertSuccessful()
        ->assertSee('Got a failed return')
        ->assertSee('Failed Return')
        ->assertSee('got a failed return from')
        ->assertDontSee('Got it back!')
        ->assertDontSee('Day Return')
        ->assertDontSee('TTM Return')
        ->assertDontSee($mail->date_sent->diffInDays($mail->returned_date) . ' Days');
});

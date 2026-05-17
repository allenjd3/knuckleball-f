<?php

use App\Jobs\GenerateReturnCard;
use App\Models\PostalMail;
use App\Services\ReturnCardService;

test('it calls ReturnCardService::generate with the postal mail', function () {
    $mail = PostalMail::factory()->returned()->create();

    $service = $this->mock(ReturnCardService::class);
    $service->shouldReceive('generate')->once()->with(
        Mockery::on(fn ($m) => $m->id === $mail->id)
    );

    GenerateReturnCard::dispatch($mail->id);
});

test('it does nothing when the postal mail does not exist', function () {
    $service = $this->mock(ReturnCardService::class);
    $service->shouldReceive('generate')->never();

    GenerateReturnCard::dispatch(99999);
});

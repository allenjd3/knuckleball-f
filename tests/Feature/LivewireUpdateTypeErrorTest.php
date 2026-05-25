<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;

it('redirects instead of 500 when a TypeError is thrown during a livewire update', function () {
    $handler = app(ExceptionHandler::class);
    $request = Request::create('/livewire/update', 'POST');
    $e = new TypeError('Argument #1 ($notification) must be of type array, int given');

    $response = $handler->render($request, $e);

    expect($response->getStatusCode())->toBe(302);
});

it('redirects instead of 500 when an array-to-string Error is thrown during a livewire update', function () {
    $handler = app(ExceptionHandler::class);
    $request = Request::create('/livewire/update', 'POST');
    $e = new Error('Array to string conversion');

    $response = $handler->render($request, $e);

    expect($response->getStatusCode())->toBe(302);
});

it('does not redirect Errors on non-livewire paths', function () {
    $handler = app(ExceptionHandler::class);
    $request = Request::create('/players', 'GET');
    $e = new Error('Array to string conversion');

    $response = $handler->render($request, $e);

    expect($response->getStatusCode())->not->toBe(302);
});

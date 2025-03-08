<?php

use App\Support\Dtos\AddressDto;

it('can sanitize an address string', function ($addressString) {
    $address = AddressDto::make(explode("\n", $addressString));

    expect($address->address1)->toBe('2671 Rochester Ave');
    expect($address->address2)->toBeNull();
    expect($address->city)->toBe('Hamilton');
    expect($address->state)->toBe('OH');
    expect($address->zip)->toBe('45011');
})->with([
        "2671 Rochester Ave,\n Hamilton OH 45011",
    ]);

<?php

namespace App\Support\Dtos;

class AddressDto {
    public string $address1;
    public string $city;
    public string $state;
    public string $zip;
    public ?string $name = null;
    public ?string $address2 = null;

    public function __construct(
        string $address1,
        ?string $name = null,
        ?string $address2 = null,
    ) {
        $this->name = $name;
        $this->address1 = trim($address1, " ,");
        $this->address2 = $address2;
    }

    public static function make(array $data): self
    {
        if (count($data) === 4) {
            [$name, $address1, $address2, $cityStateZip] = $data;
        } elseif (count($data) === 3) {
            [$address1, $address2, $cityStateZip] = $data;
        } elseif (count($data) === 2) {
            [$address1, $cityStateZip] = $data;
        } else {
            [$address1] = $data;
            $cityStateZip = '';
        }

        $addressDto = new self(
            address1: $address1,
            address2: $address2 ?? null,
            name: $name ?? null,
        );

        $addressDto->sanitizeCityStateZip($cityStateZip);
        return $addressDto;
    }

    private function sanitizeCityStateZip(string $cityStateZip): void
    {
        $matches = explode(' ', $cityStateZip);

        $this->zip = trim(array_pop($matches), ' ,');
        $this->state = trim(array_pop($matches), ' ,');
        $this->city = trim(implode(' ', $matches), ' ,');
    }
}

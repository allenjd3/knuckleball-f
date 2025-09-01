<?php

namespace App\Dto;

class OgData
{
    public function __construct(
        public string $image = '',
        public string $title = '',
        public string $description = '',
        public string $url = '',
    ) {}

    public static function make(array $rawData)
    {
        return new self(
            image: data_get($rawData, 'ogImageUrl', ''),
            title: data_get($rawData, 'ogTitle', ''),
            description: data_get($rawData, 'ogDescription', ''),
            url: data_get($rawData, 'lastLinkUrl', '')
        );
    }

    public function hasOgData(): bool
    {
        return $this->image || $this->title || $this->description || $this->url;
    }
}

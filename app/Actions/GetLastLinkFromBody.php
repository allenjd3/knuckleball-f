<?php

namespace App\Actions;

use Closure;
use Exception;
use Symfony\Component\DomCrawler\Crawler;

class GetLastLinkFromBody
{
    public static function handle(string $body): string
    {
        $bodyCrawler = new Crawler($body);
        $lastUrl = '';

        try {
            $lastUrl = $bodyCrawler->filter('a[href]')
                ->last()
                ->link()
                ->getUri();
        } catch (Exception $e) {}

        return $lastUrl;
    }
}